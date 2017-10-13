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
     * @param Controller $controller An instance of the controller
     * @param string $type The type to be bound
     * @param string $name The name of the argument on the action method
     * @param mixed $instance An instance pre created
     * @return void
     */
    public function bind(Controller $controller, $type, $name, $instance = null);
    //public function getBound();
    public function requiresInstance();
}
