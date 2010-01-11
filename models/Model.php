<?php
class Model
{
    private $_dataStoreInstance;
    
    protected $dataStore;

    public static function load($model)
    {
        $pathComponents = explode(".", $model);
        $modelClass = ucfirst($pathComponents[0]) . "Model";

        require_once
        (
            Ntentan::$packagesPath
            . implode("/", $pathComponents)
            . "/$modelClass.php"
        );

        $model = new $modelClass();

        if($model->datastore == null)
        {
            $dataStoreParams = Ntentan::getDefaultDataStore();
            $dataStoreClass = ucfirst($dataStoreParams["datastore"]) . "DataStore";
            $dataStore = new $dataStoreClass($dataStoreParams);
            $model->setDataStore($dataStore);
        }

        return $model;
    }

    public function setDataStore($dataStore)
    {
        $this->_dataStoreInstance = $dataStore;
        $this->_dataStoreInstance->setModel($this);
    }

    public function get($type = 'all', $params = null)
    {
        $params["type"] = $type;
        $result = $this->_dataStoreInstance->get($params);
        return $result;
    }

    public function __call($method, $arguments)
    {
        if(substr($method, 0, 7) == "getWith")
        {
            $field = strtolower(substr($method, 7));
            $type = 'first';
            foreach($arguments as $argument)
            {
                $params["conditions"][$field] = $argument;
            }
        }
        return $this->get($type, $params);
    }
}