<?php
class MysqlDataStore extends SqlDatabaseDataStore
{
	private $db;
	
    protected function connect($parameters) {
        $this->defaultSchema = $parameters["database"];
        $this->db = mysqli(
            $parameters["hostname"],
            $parameters["username"],
            $parameters["password"],
            $parameters["database"]
        );
    }
    
    protected function query($query) {
        $queryResult = MysqlDataStore::$db->query($query);
        
        if($queryResult === false)
        {
            throw new DataStoreException ("MySQL Says : ".MysqlDataStore::$db->error);
        }
        $result = array();
        while($row = $queryResult->fetch_assoc())
        {
            $result[] = $row;
        }
        
        return $result;
    }

    public function describe()
    {
        $fields = array();
        $result = MysqlDataStore::$db->query("DESCRIBE {$this->table}");
        if($result === false) throw new Exception("Mysql Query Error");
        while($row = $result->fetch_assoc())
        {
            $field["name"] = $row["Field"];
            $type = $this->describeType($row["Type"]);
            $field["type"] = $type["type"];

            if($type["lenght"] != null) $field["lenght"] = $type["lenght"];
            if($type["signed"]) $field["signed"] = true;
            if($row["Null"] == "NO") $field["required"] = true;
            if($row["Key"] == "PRI") $field["primary_key"] = true;
            
            $fields[$row["Field"]] = $field;
            unset($field);
        }
        
        $uniqueFields = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
             c.table_name = pk.table_name and 
             c.constraint_name = pk.constraint_name
             where pk.table_name = '{$this->table}' 
                and pk.table_schema='" . MysqlDataStore::$defaultSchema . "'
             and constraint_type = 'UNIQUE'"
        );
        
        foreach($uniqueFields as $uniqueField)
        {
            $fields[$uniqueField["column_name"]]["unique"] = true;
        }
        
        $description["name"] = $this->table;
        $description["fields"] = $fields;
        
        return $description;
    }
    
    private function describeType($type) {
        preg_match('/(?<type>int|varchar|text|double)(\((?<lenght>[0-9]*)\))?[ ]?(?<signed>unsigned)?/', $type, $matches);
        switch($matches["type"]) {
            case "int":
                $return["type"] = "integer";
                $return["signed"] = $matches["signed"] == "unsigned" ? false : true;
                break;

            case "double":
                $return["type"] = "double";
                break;
            
            case "varchar":
                $return["type"] = "string";
                $return["lenght"] = $matches["lenght"];
                break;

            case "text":
                $return["type"] = "string";
                break;
        }
        return $return;
    }    
}