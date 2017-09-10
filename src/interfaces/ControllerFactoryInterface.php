<?php

namespace ntentan\interfaces;

use ntentan\Controller;

/**
 * 
 * @author ekow
 */
interface ControllerFactoryInterface
{
    public function createController(string $controller): Controller;
    public function executeController(Controller $controller, string $action, array $parameters): string;
}
