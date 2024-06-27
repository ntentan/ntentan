<?php

namespace ntentan\controllers\model_binders;

use ntentan\utils\Input;
use ntentan\utils\Text;
use ntentan\Controller;
use ntentan\controllers\ModelBinderInterface;

/**
 * This class is responsible for binding request data with standard ntentan models or classes.
 *
 * @author ekow
 */
class DefaultModelBinder implements ModelBinderInterface
{
    /**
     * @param \ntentan\Model $object
     * @return array
     */
    private function getModelFields($object)
    {
        return array_keys($object->getDescription()->getFields());
    }
    
    private function getClassFields($object)
    {
        $reflection = new \ReflectionClass($object);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $fields = [];
        foreach ($properties as $property) {
            $fields[] = $property->name;
        }
        return $fields;
    }

    public function bind(array $data)
    {
        $instance = $data["instance"];
        $fields = $this->getClassFields($instance);
        $requestData = Input::post() + Input::get();
        foreach ($fields as $field) {
            if (isset($requestData[$field])) {
                $instance->$field = $requestData[$field];
            }
        }
        return $instance;
    }

    public function getRequirements(): array
    {
        return ['instance'];
    }
}
