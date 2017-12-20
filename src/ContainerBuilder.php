<?php

namespace ntentan;

use ntentan\atiaa\DriverFactory;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\middleware\MvcMiddleware;
use ntentan\nibii\factories\DriverAdapterFactory;
use ntentan\nibii\interfaces\DriverAdapterFactoryInterface;
use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\panie\Container;
use ntentan\interfaces\ContainerBuilderInterface;
use ntentan\config\Config;
use ntentan\utils\Text;
use ntentan\nibii\interfaces\ValidatorFactoryInterface;
use ntentan\nibii\factories\DefaultValidatorFactory;
use ntentan\kaikai\CacheBackendInterface;
use ntentan\middleware\mvc\DefaultControllerFactory;
use ntentan\middleware\MiddlewareFactoryRegistry;
use ntentan\middleware\MvcMiddlewareFactory;

/**
 * Wires up the panie IoC container for ntentan.
 * This class contains the default wiring of the IoC container for ntentan. This wiring is primarily used during the
 * initial setup of the application. Any bindings created here are not passed on to the container used for initializing
 * controllers. To add your own custom bindings, you can extend this class and pass your new builder's class name to the
 * application initialization class of your app.
 * @package ntentan
 */
class ContainerBuilder implements ContainerBuilderInterface
{
    private $defaultSetup;

    public function __construct()
    {
        $this->defaultSetup = [
            ModelClassResolverInterface::class => ClassNameResolver::class,
            ModelJoinerInterface::class => ClassNameResolver::class,
            TableNameResolverInterface::class => nibii\Resolver::class,
            DriverFactory::class => [
                function($container) {
                    $config = $container->resolve(Config::class);
                    return new DriverFactory($config->get('db'));
                }
            ],
            ModelFactoryInterface::class => MvcModelFactory::class,
            ValidatorFactoryInterface::class => DefaultValidatorFactory::class,
            DriverAdapterFactoryInterface::class => [
                function($container) {
                    $config = $container->resolve(Config::class);
                    return new DriverAdapterFactory($config->get('db')['driver']);
                }
            ],
            // Wire up the application class
            Application::class => [
                Application::class,
                'calls' => ['setMiddlewareFactoryRegistry', 'setModelBinderRegistry', 'setDatabaseDriverFactory', 'setOrmFactories']
            ],

            MiddlewareFactoryRegistry::class => [
                MiddlewareFactoryRegistry::class,
                'calls' => [
                    ['register' => ['middlewareFactory' => MvcMiddlewareFactory::class, 'name' => MvcMiddleware::class ]]
                ]
            ],

            //
            ControllerFactoryInterface::class => DefaultControllerFactory::class,

            // Factory for configuration class
            Config::class => [
                function(){
                    $config = new Config();
                    $config->readPath('config');
                    return $config;
                },
                'singleton' => true
            ],

            // Factory for cache backends
            CacheBackendInterface::class => [
                function($container){
                    $backend = $container->resolve(Config::class)->get('cache.backend', 'volatile');
                    $classname = '\ntentan\kaikai\backends\\' . Text::ucamelize($backend) . 'Cache';
                    return $container->resolve($classname);
                },
                'singleton' => true
            ]
        ];
    }

    public function getContainer() 
    {
        $container = new Container();
        $container->setup($this->defaultSetup);
        return $container;        
    }

    public function registerMiddleWare($factory, $middleware)
    {
        $this->defaultSetup[MiddlewareFactoryRegistry::class]['calls'][]= [
            'register' => ['middlewareFactory' => $factory, 'name' => $middleware]
        ];
        return $this;
    }
}