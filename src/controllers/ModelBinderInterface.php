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
    public function bind(array $data);
    
    public function getRequirements(): array;
}
