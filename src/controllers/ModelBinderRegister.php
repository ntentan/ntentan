<?php

namespace ntentan\controllers;

use ntentan\panie\Container;

/**
 *
 *
 * @author ekow
 */
class ModelBinderRegister
{
    private $binders = [];
    private $customBinderInstances = [];
    private $defaultBinderClass;
    private $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

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
        if (isset($this->binders[$type])) {
            return $this->getCustomBinder($this->binders[$type]);
        } else {
            return $this->container->singleton(ModelBinderInterface::class);
        }
    }
}
