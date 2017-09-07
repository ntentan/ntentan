<?php
/**
 * Created by PhpStorm.
 * User: ekow
 * Date: 9/5/17
 * Time: 8:16 AM
 */

namespace ntentan;

use ntentan\nibii\ModelFactoryInterface;
use ntentan\utils\Text;

class ModelFactory implements ModelFactoryInterface
{
    private $namespace;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    public function createModel($name, $context)
    {
        if ($context == nibii\Relationship::BELONGS_TO) {
            $name = Text::pluralize($name);
        }
        $className = "\\{$this->namespace}\\models\\" . Text::ucamelize($name);
        return new $className;
    }

    public function getModelTable($instance)
    {
        $class = new \ReflectionClass($instance);
        $nameParts = explode("\\", $class->getName());
        return \ntentan\utils\Text::deCamelize(end($nameParts));
    }
}