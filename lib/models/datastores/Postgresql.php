<?php
/**
 * Source file for the postgresql orm driver
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category ORM
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\models\datastores;

use ntentan\Ntentan;
use ntentan\models\exceptions\DataStoreException;
use ntentan\models\Model;

class Postgresql extends SqlDatabase
{
    private static $connections = array();
    private $db = false;
    private $connectionKey;

    public function connect($parameters)
    {
        $this->connectionKey = "{$parameters['host']}{$parameters['port']}{$parameters['name']}";
        
        if(!isset(self::$connections[$this->connectionKey]))
        {
            $connection = pg_connect(
                "host={$parameters["host"]} dbname={$parameters["name"]} user={$parameters["user"]} password={$parameters["password"]}"
            );  
            self::$connections[$this->connectionKey]['connection'] = $connection;
            self::$connections[$this->connectionKey]['references'] = 0;

            if($connection === false)
            {
                throw new DataStoreException("Could connect to database.");
            }
        }
        
        if(isset($parameters["schema"]))
        {
            $this->schema = $parameters["schema"];
        }
        else
        {
            $this->schema = "public";
        }
        $this->db = self::$connections[$this->connectionKey]['connection'];
        self::$connections[$this->connectionKey]['references']++;
    }

    protected function _query($query)
    {
        $query = mb_convert_encoding($query, 'UTF-8', \mb_detect_encoding($query));
        $queryResult = pg_query($this->db, $query);
        $this->numRows = pg_num_rows($queryResult);

        if($queryResult === false)
        {
            throw new DataStoreException ("PostgreSQL Says : ".\pg_last_error($this->db)." [$query]");
        }
        $result = array();
        while($row = pg_fetch_assoc($queryResult))
        {
            $result[] = $row;
        }

        return $result;
    }

    public function escape($string) 
    {
        return pg_escape_string($this->db, $string);
    }

    public function quote($field) 
    {
    	return "\"$field\"";
    }

    protected function limit($limitParams)
    {
        return (isset($limitParams['limit']) ? " LIMIT {$limitParams['limit']}":'') .
               (isset($limitParams['offset']) ? " OFFSET {$limitParams['offset']}":'');
    }

    protected function resolveName($fieldPath, $reformat=false, $description = null, $alias = true)
    {
        if($reformat === true)
        {
            if(strpos($fieldPath, ".") === false)
            {
                if($description['fields'][$fieldPath]['type'] == 'boolean')
                {
                    $field = $this->quotedTable . "." . $this->quote($fieldPath);
                    return " CASE WHEN $field = true THEN 1 WHEN $field = false THEN 0 END " . ($alias ? "AS $fieldPath" : '');
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
                    $fieldPath = $this->quote($model->datasStore->table) . '.' . $this->quote($fieldName);
                    return "CASE WHEN $fieldPath = true THEN 1 WHEN $fieldPath = false THEN 0 END ";
                }
                else
                {
                    return $this->quote($model->dataStore->table) . '.' . $this->quote($fieldName);
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
            throw new DataStoreException("Database table [{$table}] not found.");
        }

        foreach($pgFields as $index => $pgField)
        {
            switch($pgField["data_type"])
            {
                case "boolean":
                case "integer":
                case "bigint":
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
                case "timestamp with time zone":
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

                case '"char"':
                    $type = "string";
                    break;

                default:
                    throw new \Exception("Unknown postgresql data type [{$pgField["data_type"]}] for field[{$pgField["column_name"]}] in table [{$this->database}]");
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

    public function _doesTableExist($table, $schema)
    {
        $exists = $this->query("select count(*) as exists from information_schema.tables where table_name = '$table' and table_schema = '$schema'");
        return $exists[0]['exists'];
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
            $description["tables"][$table["table_name"]]["has_a"] = array();

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

            // Get the schemas which this one owns.
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

    public function __toString()
    {
        return 'postgresql';
    }
    
    public function __destruct() 
    {
        self::$connections[$this->connectionKey]['references']--;    
        if(self::$connections[$this->connectionKey]['references'] == 0) 
        {
            pg_close($this->db);
        }
    }
}
