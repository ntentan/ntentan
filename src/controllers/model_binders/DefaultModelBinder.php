<?php

namespace ntentan\controllers\model_binders;

use ntentan\utils\Input;
use ntentan\utils\Text;
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
            $relationshipFields = $modelRelationship->getModelInstance()->getDescription()->getFields();
            foreach($relationshipFields as $field) {
                $relationshipField = "$model.{$field['name']}";
                $relationship['fields'][] = $field['name'];
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
        
        foreach ($requestData as $field => $value) {
            if (isset($fields[$field])) {
                if(isset($fields[$field]['fields'])) {
                    $instance = $fields[$field]['instance'];
                    foreach($fields[$field]['fields'] as $relatedField) {
                        $requestField = "{$fields[$field]['model']}.$relatedField";
                        if(isset($requestData[$requestField])) {
                            $instance[$relatedField] = $requestData[$requestField];
                            unset($requestData[$data]);
                        }
                    }
                    $object[$fields[$field]['model']] = $instance;
                } else {
                    $object[$field] = $requestData[$field] == '' ? null : $requestData[$field];
                    unset($requestData[$field]);
                }
            }
        }
        return $object;
    }

    public function getBound() {
        return $this->bound;
    }

}
