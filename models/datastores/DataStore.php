<?php
abstract class DataStore
{
    private $model;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public abstract function get($queryParameters);
    public abstract function put($queryParameters);
    public abstract function update($queryParameters);
    public abstract function delete($queryParameters);
    public abstract function getDataStoreInfo();
}
