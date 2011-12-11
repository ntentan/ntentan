<?php
namespace ntentan\views\helpers\forms\api;

use ntentan\Ntentan;

use ntentan\models\Model;

class ModelField extends SelectionList
{
    public function __construct($label, $model, $value = null)
    {
        parent::__construct();
        $this->label =$label;
        $modelInstance = Model::load($model);
        if($value === null)
        {
            $data = $modelInstance->get('all');
        }
        else
        {
            $data = $modelInstance->get('all', array('fields'=>array('id', $value)));
        }
        $this->setName(Ntentan::singular($model) . "_id");

        for($i = 0; $i < $data->count(); $i++)
        {
            $this->addOption($value == null ? $data[$i] : $data[$i][$value], $data[$i]["id"]);
        }
    }
}
