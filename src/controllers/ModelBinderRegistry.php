<?php

namespace ntentan\controllers;

/**
 * This registry tracks data types and their associated model binders. 
 * For types without specific model binder specified, a default model binder is used. Ntentan ships with a 
 * DefaultModelBinder class that can be replaced with with a user specified default binder if needed.
 *
 * @author ekow
 */
class ModelBinderRegistry
{
    private $binders = [];
    private $defaultBinderClass;

    public function setDefaultBinderClass($defaultBinderClass)
    {
        $this->defaultBinderClass = $defaultBinderClass;
    }

    public function getDefaultBinderClass()
    {
        return $this->defaultBinderClass;
    }

    public function register($type, $binder)
    {
        $this->binders[$type] = $binder;
    }

    public function get($type)
    {
        $binderClass = $this->binders[$type] ?? $this->defaultBinderClass;
        return new $binderClass();
    }
}
