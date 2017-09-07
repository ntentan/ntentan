<?php

namespace ntentan;

use ntentan\atiaa\DriverFactory;
use ntentan\middleware\mvc\ControllerLoader;
use ntentan\middleware\mvc\ResourceLoaderFactory;
use ntentan\middleware\MVCMiddleware;
use ntentan\nibii\DriverAdapterFactory;
use ntentan\nibii\DriverAdapterFactoryInterface;
use ntentan\nibii\ModelFactoryInterface;
use ntentan\panie\Container;
use ntentan\interfaces\ContainerBuilderInterface;
use ntentan\Application;
use ntentan\config\Config;
use ntentan\utils\Text;
use ntentan\View;

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
    public function getContainer() 
    {
        $container = new Container();
        $container->setup([
            ModelClassResolverInterface::class => ClassNameResolver::class,
            ModelJoinerInterface::class => ClassNameResolver::class,
            TableNameResolverInterface::class => nibii\Resolver::class,

            DriverFactory::class => [
                function($container) {
                    $config = $container->resolve(Config::class);
                    return new DriverFactory($config->get('db'));
                }
            ],

            ModelFactoryInterface::class => [
                function() {
                    return new ModelFactory(Context::getInstance()->getNamespace());
                }
            ],

            DriverAdapterFactoryInterface::class => [
                function($container) {
                    $config = $container->resolve(Config::class);
                    return new DriverAdapterFactory($config->get('db'));
                }
            ],

            // Wire up the application class
            Application::class => [
                Application::class,
                'calls' => [
                    'prependMiddleware' => ['middleware' => MVCMiddleware::class],
                    'setModelBinderRegister', 'setDriverFactory', 'setOrmFactories'
                ]
            ],

            // Wire up the resource loader to setup initial loader types
            ResourceLoaderFactory::class => [
                ResourceLoaderFactory::class,
                'calls' => ['registerLoader' => ['key' => 'controller', 'class' => ControllerLoader::class]]
            ],
            
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
            kaikai\CacheBackendInterface::class => [
                function($container){
                    $backend = $container->resolve(Config::class)->get('cache.backend', 'volatile');
                    $classname = '\ntentan\kaikai\backends\\' . Text::ucamelize($backend) . 'Cache';
                    return $container->resolve($classname);
                }, 
                'singleton' => true
            ],
            
            // Factory for session containers
            sessions\SessionContainer::class => [
                function($container){
                    $sessionContainerType = $container->resolve(Config::class)->get('app.sessions.container', 'default');
                    $className = '\ntentan\sessions\containers\\' . Text::ucamelize($sessionContainerType) . 'Container';
                    return $container->resolve($className);
                },
                'singleton' => true
            ]
        ]);
        return $container;        
    }
}