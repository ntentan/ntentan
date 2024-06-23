<?php

namespace ntentan;

use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\nibii\ORMContext;
use ntentan\utils\Text;

class MvcModelFactory implements ModelFactoryInterface
{
    private $context;

//    public function __construct(Context $context)
//    {
//        $this->context = $context;
//    }

    public function createModel($name, $context)
    {
        $namespace = $this->context->getNamespace();
        if ($context == nibii\Relationship::BELONGS_TO) {
            $name = Text::pluralize($name);
        }
        $className = "\\{$namespace}\\models\\" . Text::ucamelize($name);
        return new $className;
    }

    public function getModelTable($instance)
    {
        $class = new \ReflectionClass($instance);
        $nameParts = explode("\\", $class->getName());
        return Text::deCamelize(end($nameParts));
    }
    
    public function getJunctionClassName($classA, $classB)
    {
        $classBParts = explode('\\', substr($this->getClassName($classB), 1));
        $classAParts = explode('\\', $classA);
        $joinerParts = [];

        foreach ($classAParts as $i => $part) {
            if ($part == $classBParts[$i]) {
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

    public function getClassName($model)
    {
        $namespace = $this->context->getNamespace();
        return "\\{$namespace}\\models\\" . Text::ucamelize($model);
    }
}
