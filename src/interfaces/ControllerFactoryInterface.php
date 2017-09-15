<?php

namespace ntentan\interfaces;

use ntentan\Controller;

/**
 * 
 * @author ekow
 */
interface ControllerFactoryInterface
{
    public function createController(array &$parameters): Controller;
    public function executeController(Controller $controller, array $parameters): string;
}
