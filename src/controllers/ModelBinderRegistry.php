<?php

namespace ntentan\controllers;

use ntentan\panie\Container;

/**
 *
 *
 * @author ekow
 */
class ModelBinderRegistry
{
    private $binders = [];
    private $customBinderInstances = [];
    private $defaultBinderClass;

    private function getCustomBinder($binder)
    {
        if (!isset($this->customBinderInstances[$binder])) {
            $this->customBinderInstances[$binder] = $this->container->resolve($binder);
        }
        return $this->customBinderInstances[$binder];
    }

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
