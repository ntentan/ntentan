<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\models\datastores;

use ntentan\Ntentan;
use ntentan\models\exceptions\DataStoreException;

class Postgresql extends SqlDatabase {
	private $db;
	
	public function connect($parameters) {
	    
		if(isset($parameters["schema"])) {
	    	$this->schema = $parameters["schema"];
	    } else {
	    	$this->schema = "public";
	    }
        $this->db = pg_connect(
            "host={$parameters["host"]} dbname={$parameters["database"]} user={$parameters["username"]} password={$parameters["password"]}"
        );
	}
	
    public function query($query)
    {
        $queryResult = pg_query($this->db, $query);
        
        if($queryResult === false)
        {
            throw new DataStoreException ("PostgreSQL Says : ".pg_last_error($this->db)." [$query]");
        }
        $result = array();
        while($row = pg_fetch_assoc($queryResult))
        {
            $result[] = $row;
        }
        
        return $result;
    }
    
    protected function escape($string) {
        return pg_escape_string($this->db, $string);    	
    }
    
    protected function quote($field) {
    	return "\"$field\"";
    }

    protected function resolveName($fieldPath, $reformat=false, $description = null)
    {
        if($reformat === true)
        {
            if(strpos($fieldPath, ".") === false)
            {
                if($description['fields'][$fieldPath]['type'] == 'boolean')
                {
                    $field = $this->quotedTable . "." . $this->quote($fieldPath);
                    return " CASE WHEN $field = true THEN 1 WHEN $field = false THEN 0 END AS $fieldPath";
                }
                else
                {
                    return $this->quotedTable . "." . $this->quote($fieldPath);
                }
            }
            else
            {
                $modelPathArray = explode(".", $fieldPath);
                $fieldName = array_pop($modelPathArray);
                $modelPath = implode(".", $modelPathArray);
                $model = Model::load($modelPath);
                $description = $model->describe();
                if($description[$fieldPath] == 'boolean')
                {
                    $fieldPath = $this->quote($model->getDataStore(true)->table) . '.' . $this->quote($fieldName);
                    return "CASE WHEN $fieldPath = true THEN 1 WHEN $fieldPath = false THEN 0 END ";
                }
                else
                {
                    return $this->quote($model->getDataStore(true)->table) . '.' . $this->quote($fieldName);
                }
                
            }
        }
        else
        {
            return parent::resolveName($fieldPath);
        }
    }

    public function describeTable($table, $schema)
    {
        $fields = array();
        $primaryKey = $this->query(
            "select column_name from
             information_schema.table_constraints pk
             join information_schema.key_column_usage c on
                c.table_name = pk.table_name and
                c.table_schema = pk.table_schema and
                c.constraint_name = pk.constraint_name
             where pk.table_name = '{$table}' and pk.table_schema='{$schema}'
             and constraint_type = 'PRIMARY KEY'"
        );

        $uniqueKeys = $this->query(
            "select column_name from
             information_schema.table_constraints pk
             join information_schema.key_column_usage c on
                c.table_name = pk.table_name and
                c.table_schema = pk.table_schema and
                c.constraint_name = pk.constraint_name
             where pk.table_name = '{$table}' and pk.table_schema='{$schema}'
             and constraint_type = 'UNIQUE'"
        );

        $pgFields = $this->query("select * from information_schema.columns where table_schema='{$schema}' and table_name='{$table}'");

        if(count($pgFields) == 0)
        {
            die("Database table [{$table}] not found.");
            throw new DataStoreException("Database table [{$ta}] not found.");
        }

        foreach($pgFields as $index => $pgField)
        {
            switch($pgField["data_type"])
            {
                case "boolean":
                case "integer":
                    $type = $pgField["data_type"];
                    break;

                case "numeric":
                    $type = "double";
                    break;

                case "date":
                    $type = "date";
                    break;

                case "timestamp":
                case "timestamp without time zone":
                    $type = "datetime";
                    break;

                case "character varying":
                    if($pgField["character_maximum_length"]<256)
                    {
                        $type = "string";
                    }
                    else
                    {
                        $type = "text";
                    }
                    break;

                case "text":
                    $type = "text";
                    break;

                default:
                    throw new Exception("Unknown postgresql data type [{$pgField["data_type"]}] for field[{$pgField["column_name"]}] in table [{$this->database}]");
            }

            $field = array(
                "name" => strtolower($pgField["column_name"]),
                "type" => $type,
                "required" => $pgField["is_nullable"] == "NO" ? true : false,
                "length" => $pgField["character_maximum_length"] > 0 ? (int)$pgField["character_maximum_length"] : null,
                "comment" => $pgField["column_comment"]

            );

            if($pgField["column_name"] == $primaryKey[0]["column_name"])
            {
                $field["primary_key"] = true;
            }

            foreach($uniqueKeys as $uniqueKey)
            {
                if($pgField["column_name"] == $uniqueKey["column_name"])
                {
                    $field["unique"] = true;
                }
            }

            $fields[$field["name"]] = $field;
        }
        return $fields;
    }
	
    public function describe()
    {
        $description = array();
        $description["name"] = $this->model->getName();
        $description["fields"] = $this->describeTable($this->table, $this->schema);
        return $description;
    }

    public function getLastInsertId()
    {
        $lastval = $this->query("SELECT LASTVAL() as last");
        return $lastval[0]["last"];
    }

    /**
     * (non-PHPdoc)
     * @see models/datastores/ntentan\models\datastores.SqlDatabase::describeSchema()
     */
    public function describeModel()
    {
        $description = array();
        $description["tables"] = array();
        $tables = $this->query(
            sprintf(
                "SELECT table_name
                 FROM information_schema.tables
                 WHERE table_schema = '%s'",
                $this->schema
            )
        );

        foreach($tables as $table)
        {
            $description["tables"][$table["table_name"]] = array();
            $description["tables"][$table["table_name"]]["belongs_to"] = array();
            $description["tables"][$table["table_name"]]["has_many"] = array();

            $tableDescription = $this->describeTable($table['table_name'], $this->schema);

            // Get the schemas which belong to this schema
            $belongsToTables = $this->query(
                sprintf(
                    "select constraint_column_usage.table_name, column_name
                    from information_schema.table_constraints
                    join information_schema.constraint_column_usage using(constraint_name)
                    where
                        table_constraints.table_schema = '%s' and
                        constraint_type = 'FOREIGN KEY' and
                        table_constraints.table_name = '%s' and
                        constraint_column_usage.column_name = 'id'",
                    $this->schema,
                    $table["table_name"]
                )
            );

            foreach($belongsToTables as $belongsToTable)
            {
                $singular = Ntentan::singular($belongsToTable["table_name"]);
                if(array_search($singular . '_id', \array_keys($tableDescription))!==false)
                {
                    $description["tables"][$table["table_name"]]["belongs_to"][] =
                        $singular;
                }
                else
                {
                    $description["tables"][$table["table_name"]]["belongs_to"][] =
                        array($singular, 'as' => $belongsToTable['column_name']);
                }
            }

            // Get the schemas which is owns.
            $hasManyTables = $this->query(
                sprintf(
                    "select table_constraints.table_name
                    from information_schema.table_constraints
                    join information_schema.constraint_column_usage using(constraint_name)
                    where
                        table_constraints.table_schema = '%s' and
                        constraint_type = 'FOREIGN KEY' and
                        constraint_column_usage.table_name = '%s' and
                        constraint_column_usage.column_name = 'id'",
                    $this->schema,
                    $table["table_name"]
                )
            );

            foreach($hasManyTables as $hasManyTable)
            {
                $description["tables"][$table["table_name"]]["has_many"][] =
                    $hasManyTable["table_name"];
            }
        }
        return $description;
    }

    public function begin()
    {
        $this->query("BEGIN");
    }

    public function end()
    {
        $this->query("COMMIT");
    }

}
