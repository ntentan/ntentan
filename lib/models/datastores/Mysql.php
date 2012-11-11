<?php
/**
 * Source file for the mysql orm driver
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

use \mysqli;
use \Exception;
use ntentan\models\exceptions\DataStoreException;

class Mysql extends SqlDatabase
{
    private static $db = false;
	
    protected function connect($parameters)
    {
        $this->schema = $parameters["database_name"];
        if(self::$db === false)
        {
            self::$db = new mysqli(
                $parameters["database_host"],
                $parameters["database_user"],
                $parameters["database_password"]
            );
            //self::$db->set_charset('utf8');
            if(!self::$db->select_db($parameters["database_name"]))
            {
                throw new DataStoreException("Could not select database <code><b>{$parameters['database_name']}</b></code>");
            }
        }
    }
    
    protected function _query($query)
    {
        $queryResult = self::$db->query($query);
        $this->numRows = $queryResult->num_rows;
        
        if($queryResult === false)
        {
            throw new DataStoreException ("MySQL Says : ". self::$db->error . "[$query]");
        }
        else if($queryResult === true)
        {
            $result = true;
        }
        else
        {
            $result = array();
            while($row = $queryResult->fetch_assoc())
            {
                $result[] = $row;
            }
        }
        return $result;
    }
    
    protected function _doesTableExist($table, $schema)
    {
        $exists = $this->query("select count(*) as `exists` from information_schema.tables where table_name = '$table' and table_schema = '$schema'");
        return $exists[0]['exists'];
    }

    public function describeTable($table, $schema)
    {
        $fields = array();
        $primaryKey = $this->query(
            "select column_name from
             information_schema.table_constraints pk
             join information_schema.key_column_usage c on
                c.table_name = pk.table_name and
                c.constraint_name = pk.constraint_name and
                c.table_schema = pk.table_schema
             where pk.table_name = '{$table}' and pk.table_schema='{$schema}'
             and constraint_type = 'PRIMARY KEY'"
        );

        $uniqueKeys = $this->query(
            "select column_name from
             information_schema.table_constraints pk
             join information_schema.key_column_usage c on
                c.table_name = pk.table_name and
                c.constraint_name = pk.constraint_name and
                c.table_schema = pk.table_schema
             where pk.table_name = '{$table}' and pk.table_schema='{$schema}'
             and constraint_type = 'UNIQUE'"
        );
        
        $mysqlFields = $this->query("select * from information_schema.columns where table_schema='{$schema}' and table_name='{$table}'");

        foreach($mysqlFields as $index => $mysqlField)
        {
            switch($mysqlField["DATA_TYPE"])
            {
                case "boolean":
                case "tinyint":
                    $type = "boolean";
                    break;
                case "integer":
                    $type = $mysqlField["DATA_TYPE"];
                    break;

                case "int":
				case "bigint":
                    $type = "integer";
                    break;

                case "double":
                case "float":
                    $type = "double";
                    break;

                case "date":
                    $type = "date";
                    break;

                case "timestamp":
                case "datetime":
                    $type = "datetime";
                    break;

                case "varchar":
                case "enum":
                    if($mysqlField["CHARACTER_MAXIMUM_LENGTH"]<256)
                    {
                        $type = "string";
                    }
                    else
                    {
                        $type = "text";
                    }
                    break;

                case "text":
                case "mediumtext":
                    $type = "text";
                    break;

                default:
                    throw new Exception("Unknown MySQL data type [{$mysqlField["DATA_TYPE"]}] for field[{$mysqlField["COLUMN_NAME"]}] in table [{$table}]");
            }

            $field = array(
                "name" => strtolower($mysqlField["COLUMN_NAME"]),
                "type" => $type,
                "required" => $mysqlField["IS_NULLABLE"] == "NO" ? true : false,
                "length" => $mysqlField["CHARACTER_MAXIMUM_LENGTH"] > 0 ? (int)$mysqlField["CHARACTER_MAXIMUM_LENGTH"] : null,
                "comment" => $mysqlField["COLUMN_COMMENT"]
            );

            if($mysqlField["COLUMN_NAME"] == $primaryKey[0]["column_name"])
            {
                $field["primary_key"] = true;
            }

            if($mysqlField["COLUMN_DEFAULT"] != null)
            {
                $field["default"] = $mysqlField["COLUMN_DEFAULT"];
            }

            foreach($uniqueKeys as $uniqueKey)
            {
                if($mysqlField["COLUMN_NAME"] == $uniqueKey["column_name"])
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
    
    /**
     * (non-PHPdoc)
     * @see models/datastores/ntentan\models\datastores.SqlDatabase::describeSchema()
     */
    public function describeModel()
    {
        $description = array();
        $description["tables"] = array();
        $schema = $this->schema;

        $tables = $this->query(
            sprintf(
                "SELECT table_name 
                 FROM information_schema.tables 
                 WHERE table_schema = '%s'",
                $schema
            )
        );
        
        foreach($tables as $table)
        {
            $description["tables"][$table["table_name"]] = array();
            $description["tables"][$table["table_name"]]["belongs_to"] = array();
            $description["tables"][$table["table_name"]]["has_many"] = array();
            $description["tables"][$table["table_name"]]["has_a"] = array();
            
            $tableDescription = $this->describeTable($table['table_name'], $this->schema);
        
            // Get the schemas which belong to
            $belongsToTables = $this->query(
                sprintf(
                    "select referenced_table_name, column_name
                    from information_schema.table_constraints 
                    join information_schema.key_column_usage using(constraint_name) 
                    where 
                        table_constraints.table_schema = '%s' and 
                        constraint_type = 'FOREIGN KEY' and 
                        table_constraints.table_name = '%s' and
                        referenced_column_name = 'id'",
                    $schema,
                    $table["table_name"]
                )
            );
            
            foreach($belongsToTables as $belongsToTable)
            {
                $singular = Ntentan::singular($belongsToTable["referenced_table_name"]);
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
                    join information_schema.key_column_usage using(constraint_name) 
                    where 
                        table_constraints.table_schema = '%s' and 
                        constraint_type = 'FOREIGN KEY' and 
                        referenced_table_name = '%s' and
                        referenced_column_name = 'id'",
                    $schema,
                    $table["table_name"]
                )
            );
            
            //@todo Change nomenulature to support has_a both here and in pgsql
            
            foreach($hasManyTables as $hasManyTable)
            {
                /*$unique = $this->query("select column_name from
                     information_schema.table_constraints pk
                     join information_schema.key_column_usage c on
                        c.table_name = pk.table_name and
                        c.constraint_name = pk.constraint_name and
                        c.table_schema = pk.table_schema
                     where pk.table_name = '{$table['table_name']}' and pk.table_schema='{$schema}' and column_name = '{$singular}_id'
                     and constraint_type = 'UNIQUE'"
                );
                if(count($unique) > 0)
                {
                    $description["tables"][$table["table_name"]]["has_a"][] = 
                        $hasManyTable["table_name"];
                }
                else
                {*/
                    $description["tables"][$table["table_name"]]["has_many"][] = 
                        $hasManyTable["table_name"];
                //}
            }            
        }
        return $description;
    }
    
    public function quote($field)
    {
        return "`$field`";
    }
    
    public function escape($string)
    {
        return mysql_real_escape_string($string);
    }
    
    protected function getLastInsertId()
    {
        return self::$db->insert_id;
    }
    
    public function begin()
    {
        //self::$db->autocommit(false);
    }
    
    public function end()
    {
        //self::$db->commit();
    }

    protected function limit($limitParams)
    {
        return " LIMIT " . (isset($limitParams['offset']) ? $limitParams['offset'] . ", {$limitParams['limit']}" : $limitParams['limit']);
    }

    public function __toString()
    {
        return 'mysql';
    }
}
