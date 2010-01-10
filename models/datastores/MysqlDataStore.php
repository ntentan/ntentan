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
        $modelInformation = new ReflectionObject($model);
        $modelName = $modelInformation->getName();
        var_dump($modelName);
    }

    public function get($queryParameters)
    {
        
    }

    public function put($queryParameters)
    {
    
    }
    
    public function update($queryParameters)
    {
    
    }

    public function getDataStoreInfo()
    {
    
    }
    
    public function delete($queryParameters)
    {

    }
}