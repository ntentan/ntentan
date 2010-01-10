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
    }

    public function setDataStore($dataStore)
    {
        $this->_dataStoreInstance = $dataStore;
        $this->_dataStoreInstance->setModel($this);
    }

    public function get()
    {
        
    }
}