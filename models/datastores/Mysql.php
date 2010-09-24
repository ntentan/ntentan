<?php

namespace ntentan\models\datastores;

use \mysqli;
use \Exception;
use \DataStoreException;

class Mysql extends SqlDatabase
{
	private static $db;
	
    protected function connect($parameters) {
        $this->defaultSchema = $parameters["database"];
        Mysql::$db = new mysqli(
            $parameters["hostname"],
            $parameters["username"],
            $parameters["password"],
            $parameters["database"]
        );
    }
    
    protected function query($query) {
        $queryResult = Mysql::$db->query($query);
        
        if($queryResult === false)
        {
            throw new DataStoreException ("MySQL Says : ". Mysql::$db->error . "[$query]");
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

    public function describe()
    {
        $fields = array();
        $databaseInfo = $this->table;
        
        $primaryKey = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
                c.table_name = pk.table_name and 
                c.constraint_name = pk.constraint_name and
                c.table_schema = pk.table_schema
             where pk.table_name = '{$this->table}' and pk.table_schema='{$this->defaultSchema}'
             and constraint_type = 'PRIMARY KEY'"
        );
        
        $uniqueKeys = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
                c.table_name = pk.table_name and 
                c.constraint_name = pk.constraint_name and
                c.table_schema = pk.table_schema
             where pk.table_name = '{$this->table}' and pk.table_schema='{$this->defaultSchema}'
             and constraint_type = 'UNIQUE'"
        );
        
        $mysqlFields = $this->query("select * from information_schema.columns where table_schema='{$this->defaultSchema}' and table_name='{$this->table}'");

        
        if(count($mysqlFields) == 0)
        {
            throw new Exception("Database table [{$this->table}] not found.");
        }
        
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
                    $type = "integer";
                    break;

                case "double":
                    $type = "double";
                    break;

                case "date":
                    $type = "date";
                    break;

                case "timestamp":
                case "timestamp without time zone":
                    $type = "datetime";
                    break;
            
                case "varchar":
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
                    $type = "text";
                    break;
                    
                default:
                    throw new Exception("Unknown MySQL data type [{$mysqlField["DATA_TYPE"]}] for field[{$mysqlField["COLUMN_NAME"]}] in table [{$this->table}]");
            }

            $field = array(
                "name" => strtolower($mysqlField["COLUMN_NAME"]),
                "type" => $type,
                "required" => $mysqlField["IS_NULLABLE"] == "NO" ? true : false,
                "lenght" => $mysqlField["CHARACTER_MAXIMUM_LENGHT"],
                "comment" => $mysqlField["COLUMN_COMMENT"]
            );

            if($mysqlField["COLUMN_NAME"] == $primaryKey[0]["column_name"])
            {
                $field["primary_key"] = true;
            }
            
            if($mysqlField["COLUMN_DEFAULT"] !== null)
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

        $description = array();
        $description["name"] = $this->table;
        $description["fields"] = $fields;
        return $description;
    }
    
    protected function quote($field)
    {
        return "`$field`";
    }
    
    protected function escape($string)
    {
        return mysql_escape_string($string);
    }
    
    protected function getLastInsertId()
    {
        return Mysql::$db->insert_id;
    }
    
    public function begin()
    {
        //Mysql::$db->autocommit(false);
    }
    
    public function end()
    {
        //Mysql::$db->commit();
    }
}
