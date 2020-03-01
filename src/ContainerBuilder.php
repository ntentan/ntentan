<?php

namespace ntentan;

use ntentan\atiaa\DriverFactory;
use ntentan\exceptions\NtentanException;
use ntentan\honam\factories\SmartyEngineFactory;
use ntentan\honam\TemplateFileResolver;
use ntentan\honam\TemplateRenderer;
use ntentan\honam\Templates;
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
use ntentan\honam\EngineRegistry;
use ntentan\honam\factories\MustacheEngineFactory;
use ntentan\honam\factories\PhpEngineFactory;
use ntentan\honam\engines\php\HelperVariable;
use ntentan\honam\engines\php\Janitor;

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
    private $container;

    public function __construct()
    {
        $this->container =new Container();
        $this->container->setup([
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
                    /** @var Config $config */
                    $config = $container->resolve(Config::class);
                    if($config->isKeySet('db')) {
                        $driver = $config->get('db')['driver'];
                        if($driver === null) {
                            throw new NtentanException("Please provide a database configuration that specifies a driver.");
                        }
                        return new DriverAdapterFactory($config->get('db')['driver']);
                    }
                }
            ],
            Templates::class => [Templates::class, 'singleton' => true],
            TemplateFileResolver::class => [TemplateFileResolver::class, 'singleton' => true],
            TemplateRenderer::class => [
                function($container) {
                    /** @var EngineRegistry $engineRegistry */
                    $engineRegistry = $container->get(EngineRegistry::class);
                    $templateFileResolver = $container->get(TemplateFileResolver::class);
                    $templateRenderer = new TemplateRenderer($engineRegistry, $templateFileResolver);
                    $engineRegistry->registerEngine(['mustache'], $container->get(MustacheEngineFactory::class));
                    $engineRegistry->registerEngine(['smarty', 'tpl'], $container->get(SmartyEngineFactory::class));
                    $engineRegistry->registerEngine(['tpl.php'],
                        new PhpEngineFactory($templateRenderer,
                            new HelperVariable($templateRenderer, $container->get(TemplateFileResolver::class)),
                            $container->get(Janitor::class)
                        ));
                    return $templateRenderer;
                },
                'singleton' => true
            ],
            // Wire up the application class
            Application::class => [ Application::class,
                'calls' => ['setMiddlewareFactoryRegistry', 'setModelBinderRegistry'] //, 'setDatabaseDriverFactory', 'setOrmFactories']
            ],

//            MiddlewareFactoryRegistry::class => [
//                MiddlewareFactoryRegistry::class,
//                'calls' => [
//                    ['register' => ['middlewareFactory' => MvcMiddlewareFactory::class, 'name' => MvcMiddleware::class ]]
//                ]
//            ],

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
        ]);
        $this->registerMiddleWare(MvcMiddlewareFactory::class, MvcMiddleware::class);
    }

    public function addBindings(array $bindings)
    {
        $this->container->setup($bindings);
        return $this;
    }

    public function getContainer() 
    {
        return $this->container;
    }

    public function registerMiddleWare($factory, $middleware)
    {
        $this->container->setup([
            MiddlewareFactoryRegistry::class => [
            'calls' => ['register' => ['middlewareFactory' => $factory, 'name' => $middleware]]
            ]
        ]);
        return $this;
    }
}