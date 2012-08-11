<?php

namespace ntentan\models;

use ntentan\Ntentan;

class ModelQuery
{
    private $model;
    private $getParams = array();
    private $getType = 'all';

    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function __call($method, $args)
    {
        if(preg_match("/(sort)(?<dir>Ascending|Descending)?(?<by>By)(?<field>[a-zA-Z]*)/", $method, $matches))
        {
            if($matches['dir'] == 'Ascending') $dir = 'ASC';
            elseif($matches['dir'] == 'Descending') $dir = 'DESC';
            
            $this->sortBy(Ntentan::deCamelize($matches['field']), $dir);
        } 
        elseif (preg_match("/(sort)(?<dir>Ascending|Descending)?(?<sub_module>[a-zA-Z]*)(?<sub_by>By)(?<field>[a-zA-Z]*)/", $method, $matches)) 
        {
            if($matches['dir'] == 'Ascending') $dir = 'ASC';
            elseif($matches['dir'] == 'Descending') $dir = 'DESC';
            
            $this->getParams[$matches['sub_module'] . "_sort"][] = Ntentan::deCamelize($matches['field']) . " $dir";
        }
        return $this;
    }

    public function sortBy($sortField, $direction = 'ASC')
    {
        $this->getParams['sort'][] = "$sortField $direction";
        return $this;
    }
    
    public function fetchRelated()
    {
        $this->getParams['fetch_related'] = true;
        return $this;
    }
    
    public function go()
    {
        return $this->model->get($this->getType, $this->getParams);
    }
    
    public function toArray()
    {
        return $this->go()->toArray();
    }
}
