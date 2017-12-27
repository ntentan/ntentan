<?php

namespace ntentan\controllers;

use ntentan\Controller;

/**
 * Describes the interface for model binders.
 * Model binders allow the framework to assign values from HTTP requests to object instances that are passed as
 * arguments of action methods.
 *
 * @author ekow
 */
interface ModelBinderInterface
{
    /**
     * Creates or sets up an object to be used as an argument for an action method.
     *
     * @param Controller $controller An instance of the controller.
     * @param string $type The type to be bound.
     * @param string $name The name of the argument on the action method.
     * @param array $parameters An array containing all parameters available to the action.
     * @param mixed $instance A pre created instance of type if required.
     * @return mixed
     */
    public function bind(Controller $controller, string $type, string $name, array $parameters, $instance = null);
    
    /**
     * Specifies whether this binder creates its own instance or requires an external instance.
     * If this method returns true, the controller factory will supply an object of the bound type. This is useful if
     * the controller factory uses some sort of service location scheme.
     * 
     * @return bool Returns true if this binder requires objects to be injected.
     */
    public function requiresInstance() : bool;
}
