<?php

namespace ntentan;

use ntentan\panie\Container;
use ntentan\interfaces\ContainerBuilderInterface;
use ntentan\Application;

class ContainerBuilder implements ContainerBuilderInterface
{
    public function getContainer() 
    {
        $container = new Container();
        $container->setup([
            ModelClassResolverInterface::class => ClassNameResolver::class,
            ModelJoinerInterface::class => ClassNameResolver::class,
            TableNameResolverInterface::class => nibii\Resolver::class,
            ComponentResolverInterface::class => ClassNameResolver::class,
            ControllerClassResolverInterface::class => ClassNameResolver::class,
            View::class => [View::class, "singleton" => true],
            nibii\ORMContext::class => [nibii\ORMContext::class, "singleton" => true],

            Application::class => [Application::class, 'calls' => ['setModelBinderRegister']],
            
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