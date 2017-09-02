<?php

namespace ntentan;

use ntentan\panie\Container;
use ntentan\kaikai\Cache;

class ContainerBuilder
{
    public static function getContainer()
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
            Config::class => [function($container){
                $config = new Config();
                $config->readPath('config');
                return $config;
            }, 'singleton' => true],
            kaikai\CacheBackendInterface::class => [function($container){
                $backend = $container->resolve(Config::class)->get('cache.backend', 'volatile');
                $classname = '\ntentan\kaikai\backends\\' . Text::ucamelize($backend) . 'Cache';
            }, 'singleton' => true]//Cache::getBackendClassName($config-
        ]);
        return $container;
    }
}
