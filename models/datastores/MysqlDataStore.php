<?php
/**
 * 
 */
class MysqlDataStore extends DataStore
{
    private static $db;
    private $table;
    
    private static function connect($parameters)
    {
        return new mysqli(
            $parameters["hostname"],
            $parameters["username"],
            $parameters["password"],
            $parameters["database"]
        );
    }

    public function __construct($parameters)
    {
        if(MysqlDataStore::$db == null)
        {
            MysqlDataStore::$db = MysqlDataStore::connect($parameters);
        }
    }

    public function setModel($model)
    {
        parent::setModel($model);
        $modelInformation = new ReflectionObject($model);
        $modelName = $modelInformation->getName();
        $modelName = strtolower($modelName);
        $this->table = substr($modelName, 0, strlen($modelName)-5);
    }

    protected function _get($params)
    {
        // Get a list of fields convert it to a count if that is what is needed
        if($params["type"] == "count")
        {
            $fields = "COUNT(*)";
        }
        else
        {
            if($params["fields"] == null)
            {
                $fields = "*";
            }
        }

        // Generate the base query
        $query = "select $fields from {$this->table} ";

        // Generate conditions
        if($params["conditions"] !== null)
        {
            // Go through the array of conditions and generate a condition
            // statement for MySQL
            foreach($params["conditions"] as $field => $condition)
            {
                if(is_array($condition))
                {
                    foreach($condition as $clause)
                    {
                        $conditions[] = "$field = '$clause'";
                    }
                }
                else
                {
                    $conditions[] = "$field = '$condition'";
                }
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add the limiting clauses
        $query .= ($params["type"] == 'first' ? " LIMIT 1" : "" );

        // 
        $queryResult = MysqlDataStore::$db->query($query) or die(MysqlDataStore::$db->error);
        while($row = $queryResult->fetch_assoc())
        {
            $result[] = $row;
        }

        // Generate the data to be returned
        if($params["type"] == 'first')
        {
            $return = $result[0];
        }
        else if($params["type"] == 'count')
        {
            $return = reset($result[0]);
        }
        else
        {
            $return = $result;
        }
        return $return;
    }

    protected function _put($data)
    {
        $fields = array_keys($data);
        foreach($data as $value)
        {
            $values[] = MysqlDataStore::$db->escape_string($value);
        }
        $query = "INSERT INTO {$this->table} (`" . implode("`, `", $fields) . "`) VALUES ('" . implode("', '", $values) . "')";
        MysqlDataStore::$db->query($query);
    }

    public function getDataStoreInfo()
    {
    
    }

    protected function _update($data)
    {
        $fields = array_keys($data);
        foreach($data as $field => $value)
        {
            if($field == "id") continue;
            $values[] = "`$field` = '".MysqlDataStore::$db->escape_string($value)."'";
        }
        $query = "UPDATE {$this->table} SET " . implode(", ", $values) . " WHERE id = '{$data["id"]}'";
        MysqlDataStore::$db->query($query);
    }
    
    protected function _delete($key)
    {
        $query = "DELETE FROM {$this->table} WHERE id = '{$key}'";
        MysqlDataStore::$db->query($query);
    }

    private function describeType($type)
    {
        preg_match('/(?<type>int|varchar|text)(\((?<lenght>[0-9]*)\))?[ ]?(?<signed>unsigned)?/', $type, $matches);
        switch($matches["type"])
        {
            case "int":
                $return["type"] = "integer";
                $return["signed"] = $matches["signed"] == "unsigned" ? false : true;
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
            $fields[] = $field;
            unset($field);
        }
        $description["name"] = $this->table;
        $description["fields"] = $fields;
        return $description;
    }
}