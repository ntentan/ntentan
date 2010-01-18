<?php
/**
 * 
 */
class MysqlDataStore extends DataStore
{
    private static $db;
    
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
        // Get a list of fields
        if($params["fields"] == null)
        {
            $fields = "*";
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

        return $params["type"] == 'first' ? $result[0] : $result;
    }

    protected function _put($queryParameters)
    {
    
    }

    public function getDataStoreInfo()
    {
    
    }

    protected function _update($queryParameters)
    {
        
    }
    
    protected function _delete($queryParameters)
    {
        
    }
}