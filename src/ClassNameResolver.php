<?php

namespace ntentan;

use ntentan\utils\Text;
use ntentan\nibii\interfaces\ClassResolverInterface as ModelClassNameResolver;
use ntentan\controllers\interfaces\ClassResolverInterface as ControllerClassNameResolver;
use ntentan\nibii\interfaces\ModelJoinerInterface;
use ntentan\nibii\interfaces\TableNameResolverInterface;
use ntentan\panie\ComponentResolverInterface;

/**
 * Description of ModelClassResolver
 * @author ekow
 */
class ClassNameResolver implements ModelClassNameResolver, ControllerClassNameResolver, ModelJoinerInterface, ComponentResolverInterface
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

    public function getComponentClassName($component, $parameters)
    {
        // Attempt to load an application component
        $type = $parameters['type'];
        $camelType = Text::ucamelize($type);
        $namespaces = $parameters['namespaces'];
        
        $className = Text::ucamelize($component) . $camelType;
        $class = "\\{$namespaces[0]}\\{$type}s\\$component\\$className";
        if(class_exists($class)) {
            return $class;
        }

        // Attempt to load a core dependency
        $class = "\\ntentan\\{$namespaces[1]}\\$className";
        if(class_exists($class)) {
            return $class;
        }

        // Attempt to load plugin dependency
        $componentPaths = explode(".", $component);
        $className = Text::ucamelize(array_pop($componentPaths));
        $class= "\\ntentan\\extensions\\" . implode("\\", $componentPaths) . "\\{$type}s\\$className$camelType";
        if(class_exists($class)) {
            return $class;
        }

        throw new exceptions\ComponentNotFoundException("[$component] $type not found");        
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
