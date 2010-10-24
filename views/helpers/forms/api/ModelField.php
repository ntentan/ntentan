<?php
namespace ntentan\views\helpers\forms\api;

use ntentan\models\Model;

class ModelField extends SelectionList
{
	public function __construct($label, $model, $value = null)
	{
        parent::__construct();
        $this->label =$label;
        $modelInstance = Model::load($model);
        $data = $modelInstance->get('all');
        
        for($i = 0; $i < $data->count(); $i++)
        {
            $this->addOption($data[$i], $data[$i]["id"]);
        }
	}
}
