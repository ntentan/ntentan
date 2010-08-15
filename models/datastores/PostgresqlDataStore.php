<?php
class PostgresqlDataStore extends SqlDatabaseDataStore {
	private $db;
	
	public function connect($parameters) {
	    
		if(isset($parameters["schema"])) {
	    	$this->defaultSchema = $parameters["schema"];
	    } else {
	    	$this->defaultSchema = "public";
	    }
        $this->db = pg_connect(
            "host={$parameters["host"]} dbname={$parameters["database"]} user={$parameters["username"]} password={$parameters["password"]}"
        );
	}
	
    protected function setTable($table) {
        $this->_table = "{$this->defaultSchema}.{$table}";
    }	
	
    protected function query($query)
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
	
    public function describe()
    {
        $fields = array();
        $databaseInfo = explode(".", $this->table);
        
        $primaryKey = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
                c.table_name = pk.table_name and 
                c.constraint_name = pk.constraint_name
             where pk.table_name = '{$databaseInfo[1]}' and pk.table_schema='{$databaseInfo[0]}'
             and constraint_type = 'PRIMARY KEY'"
        );
        
        $uniqueKeys = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
                c.table_name = pk.table_name and 
                c.constraint_name = pk.constraint_name
             where pk.table_name = '{$databaseInfo[1]}' and pk.table_schema='{$databaseInfo[0]}'
             and constraint_type = 'UNIQUE'"
        );
                
        $pgFields = $this->query("select * from information_schema.columns where table_schema='{$databaseInfo[0]}' and table_name='{$databaseInfo[1]}'");
        
        if(count($pgFields) == 0)
        {
            throw new Exception("Database table [{$this->table}] not found.");
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
                "required" => $pgField["is_nullable"] == "NO" ? true : false
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

        $description = array();
        $description["name"] = $this->table;
        $description["fields"] = $fields;
        return $description;
    }
}
