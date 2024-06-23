<?php
namespace ntentan\middleware;

use \ntentan\interfaces\ControllerFactoryInterface;
use ntentan\middleware\mvc\DefaultControllerFactory;

/**
 */
class MvcWiring {
    
    public static function get() {
        return [
            ControllerFactoryInterface::class => DefaultControllerFactory::class
        ];
    }
}
