<?php
/**
 * 
 */
abstract class DataStore
{
    /**
     * The instance of the model utilizing this datastore.
     * @var Model
     */
    private $model;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function get($queryParameters)
    {
        $newModel = clone $this->model;
        $newModel->setData($this->_get($queryParameters));
        return $newModel;
    }

    protected abstract function _get($queryParameters);

    protected abstract function _put($queryParameters);

    protected abstract function _update($queryParameters);
    
    protected abstract function _delete($queryParameters);

    public abstract function getDataStoreInfo();
}