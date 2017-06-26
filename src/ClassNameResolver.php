<?php

namespace ntentan;

use ntentan\utils\Text;
use ntentan\nibii\interfaces\ModelClassResolverInterface;
use ntentan\interfaces\ControllerClassResolverInterface;
use ntentan\nibii\interfaces\ModelJoinerInterface;

/**
 * Provides implementations of the various name resolver interfaces.
 * @author ekow
 */
class ClassNameResolver implements ModelClassResolverInterface, 
    ControllerClassResolverInterface, ModelJoinerInterface
{
    public function getModelClassName($model, $context)
    {
        if($context == nibii\Relationship::BELONGS_TO) {
            $model = Text::pluralize($model);
        }
        $namespace = Ntentan::getNamespace();
        return "\\$namespace\\models\\" . Text::ucamelize($model);        
    }

    public function getJunctionClassName($classA, $classB)
    {
        $classBParts = explode('\\', substr(nibii\Nibii::getClassName($classB), 1));
        $classAParts = explode('\\', $classA);
        $joinerParts = [];

        foreach($classAParts as $i => $part) {
            if($part == $classBParts[$i]) {
                $joinerParts[] = $part;
            } else {
                break;
            }
        }

        $class = [end($classAParts), end($classBParts)];
        sort($class);
        $joinerParts[] = implode('', $class);

        return implode('\\', $joinerParts);        
    }

    public function getControllerClassName($name)
    {
        return sprintf(
            '\%s\controllers\%sController', 
            Ntentan::getNamespace(), 
            utils\Text::ucamelize($name)
        );        
    }

}
