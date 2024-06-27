<?php
namespace ntentan\middleware;

use ntentan\controllers\ModelBinderRegistry;
use ntentan\View;
use ntentan\controllers\model_binders\ViewBinder;
use ntentan\controllers\model_binders\DefaultModelBinder;
use ntentan\panie\Container;
use ntentan\middleware\mvc\ServiceContainerBuilder;

/**
 */
class MvcWiring {
    
    public static function get() {
        return [
            ModelBinderRegistry::class => [
                function(Container $container) {
                    $registry = new ModelBinderRegistry();
                    $registry->setDefaultBinderClass(DefaultModelBinder::class);
                    $registry->register(View::class, ViewBinder::class);
                    return $registry;
                },
                'singleton' => true
            ],
            ServiceContainerBuilder::class => ['singleton' => true]
        ];
    }
}
