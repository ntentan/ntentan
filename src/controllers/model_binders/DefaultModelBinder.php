<?php

namespace ntentan\controllers\model_binders;

use ntentan\utils\Input;
use ntentan\Controller;
use ntentan\panie\Container;

/**
 * Description of DefaultModelBinder
 *
 * @author ekow
 */
class DefaultModelBinder implements \ntentan\controllers\ModelBinderInterface {

    private $bound;
    protected $container;
    
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * 
     * @param \ntentan\Model $object
     */
    private function getModelFields($object) {
        $description = $object->getDescription();
        $fields = $description->getFields();
        $modelRelationships = $description->getRelationships();
        
        foreach($modelRelationships as $model => $modelRelationship) {
            $relationship = [
                'fields' => [],
                'model' => $model,
                'instance' => $modelRelationship->getModelInstance()
            ];
            $relationshipFields = array_map(
                function($field) {
                    return $field['name'];
                },
                $modelRelationship->getModelInstance()->getDescription()->getFields()
            );
            foreach($relationshipFields as $field) {
                $relationshipField = "$model.$field";
                $relationship['fields'] = $relationshipFields;
                $fields[$relationshipField] = $relationship;
            }
        }
        return $fields; 
    }
    
    
  
    public function bind(Controller $controller, $action, $type, $name) {
        $this->bound = false;
        $object = $this->container->resolve($type);
        
        if (!is_a($object, '\ntentan\Model')) {
            return false;
        }
        
        $requestData = Input::post() + Input::get();
        $fields = $this->getModelFields($object);
        $requestFields = array_keys($requestData);
                
        //@todo Clean up this mess!
        while (!empty($requestFields)) {
            $field = array_pop($requestFields);
            // If the field in request data is also in model
            if (isset($fields[$field])) {
                // If the field has its own subfields
                if(isset($fields[$field]['fields'])) {
                    //$instance = $fields[$field]['instance'];
                    $relatedData = [];
                    foreach($fields[$field]['fields'] as $relatedField) {
                        $requestField = "{$fields[$field]['model']}.$relatedField";
                        if(isset($requestData[$requestField])) {
                            if(is_array($requestData[$requestField])) {
                                foreach($requestData[$requestField] as $fieldKey => $fieldValue) {
                                    if(!isset($relatedData[$fieldKey])) {
                                        $relatedData[$fieldKey] = [];
                                    }
                                    $relatedData[$fieldKey][$relatedField] = $fieldValue;
                                }
                            } else {
                                $relatedData[$relatedField] = $requestData[$requestField];
                            }
                            if($requestField != $field){
                                unset($requestFields[array_search($requestField, $requestFields)]);
                            }
                        }
                    }
                    $fields[$field]['instance']->setData($relatedData);
                    $object[$fields[$field]['model']] = $fields[$field]['instance'];
                } else {
                    $object[$field] = $requestData[$field] == '' ? null : $requestData[$field];
                }
            }
        }
       
        return $object;
    }

    public function getBound() {
        return $this->bound;
    }

}
