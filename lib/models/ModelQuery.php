<?php
namespace ntentan\models;

/**
 * A class for generating friendlier queries for the ntentan models
 */
class ModelQuery
{
    private $model;
    private $parameters;
    private $mode;
    
    public function __construct($model, $initialMode = 'all', $initialParameters = array())
    {
        $this->model = $model;
        $this->parameters = $initialParameters;
        $this->mode = $initialMode;
    }
    
    public function go()
    {
        return $this->model->get($this->mode, $this->parameters);
    }
}