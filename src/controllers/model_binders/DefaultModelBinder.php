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
     *
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

    public function bind(Controller $controller, string $type, string $name, array $parameters, $instance = null)
    {
        $fields = $this->getClassFields($instance);
        if (is_a($instance, '\ntentan\Model')) {
            $fields = array_merge($fields, $this->getModelFields($instance));
        }
        $requestData = Input::post() + Input::get();
        foreach ($fields as $field) {
            $decamelized = Text::deCamelize($field);
            if (isset($requestData[$decamelized])) {
                $instance->$field = $requestData[$decamelized] == '' ? null : $requestData[$decamelized];
            }
        }
        return $instance;
    }

    public function requiresInstance() : bool
    {
        return true;
    }
}
