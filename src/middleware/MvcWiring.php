<?php
namespace ntentan\middleware;

use \ntentan\interfaces\ControllerFactoryInterface;
use ntentan\middleware\mvc\DefaultControllerFactory;
use ntentan\controllers\ModelBinderRegistry;
use ntentan\View;
use ntentan\controllers\model_binders\ViewBinder;

/**
 */
class MvcWiring {
    
    public static function get() {
        return [
            ControllerFactoryInterface::class => DefaultControllerFactory::class,
            ModelBinderRegistry::class => [
                function() {
                    $binder = new ModelBinderRegistry();
                    $binder->register(View::class, ViewBinder::class);
                    return $binder;
                }
            ]
        ];
    }
}
