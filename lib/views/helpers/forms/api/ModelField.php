<?php
namespace ntentan\views\helpers\forms\api;

use ntentan\Ntentan;

use ntentan\models\Model;

class ModelField extends SelectionList
{
    public function __construct($label, $model, $value = null, $extraConditions = array())
    {
        parent::__construct();
        $this->label =$label;
        $modelInstance = Model::load($model);
        if($value === null)
        {
            $data = $modelInstance->get(
                'all', 
                count($extraConditions) > 0 ? array('conditions'=>$extraConditions) : null
            );
        }
        else
        {
            $description = $modelInstance->describe();
            $data = $modelInstance->get(
                'all', 
                array(
                    'fields'=>array($description['primary_key'][0], $value), 
                    'conditions' => count($extraConditions) > 0 ? $extraConditions : null,
                    'sort' => $value
                )
            );
        }
         
        $this->setName(Ntentan::singular($model) . "_id");

        for($i = 0; $i < $data->count(); $i++)
        {
            $this->addOption($value === null ? $data[$i] : $data[$i][$value], $data[$i][$description['primary_key'][0]]);
        }
    }
}
