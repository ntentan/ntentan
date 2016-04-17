<?php

namespace ntentan;

use ntentan\utils\Text;
use ntentan\nibii\interfaces\ClassResolverInterface;
use ntentan\nibii\interfaces\ModelJoinerInterface;
use ntentan\nibii\interfaces\TableNameResolverInterface;

/**
 * Description of ModelClassResolver
 * @author ekow
 */
class ModelResolvers implements ClassResolverInterface, ModelJoinerInterface
{
    public function getClassName($model, $context)
    {
        if($context == nibii\Relationship::BELONGS_TO) {
            $model = Text::pluralize($model);
        }
        $namespace = Ntentan::getNamespace();
        return "\\$namespace\\models\\" . Text::ucamelize(explode('.', $model)[0]);        
    }

    public function getJunctionClass($classA, $classB)
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

}
