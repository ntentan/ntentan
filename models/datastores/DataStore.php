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
    protected $model;

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

    public function put()
    {
        $data = $this->model->getData();
        $this->_put($data);
    }

    public function update()
    {
        $data = $this->model->getData();
        $this->_update($data);
    }

    public function delete()
    {
        $data = $this->model->getData();
        $this->_delete($data["id"]);
    }

    protected abstract function _get($queryParameters);

    protected abstract function _put($queryParameters);

    protected abstract function _update($queryParameters);
    
    protected abstract function _delete($queryParameters);

    public abstract function describe();
}