<?php

namespace ntentan;

use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\utils\Text;

class MvcModelFactory implements ModelFactoryInterface
{
    public function createModel($name, $context)
    {
        $namespace = Context::getInstance()->getNamespace();
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
        return \ntentan\utils\Text::deCamelize(end($nameParts));
    }
}