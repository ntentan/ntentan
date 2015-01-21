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
        
        $data = $modelInstance->get(
            'all', 
            array( 
                'conditions' => count($extraConditions) > 0 ? $extraConditions : null,
                'sort' => $value
            )
        );
         
        $this->setName(Ntentan::singular($model) . "_id");
        
        if($value === null)
        {
            $description = $modelInstance->describe();
            $value = $description['primary_key'][0];
        }

        for($i = 0; $i < $data->count(); $i++)
        {
            $this->addOption($data[$i], $data[$i][$value]);
        }
    }
}
