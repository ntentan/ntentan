<?php

namespace ntentan;

use ntentan\atiaa\DriverFactory;
use ntentan\controllers\model_binders\DefaultModelBinder;
use ntentan\controllers\model_binders\RedirectBinder;
use ntentan\controllers\model_binders\UploadedFileBinder;
use ntentan\controllers\model_binders\ViewBinder;
use ntentan\controllers\ModelBinderRegistry;
use ntentan\exceptions\NtentanException;
use ntentan\honam\factories\SmartyEngineFactory;
use ntentan\honam\TemplateFileResolver;
use ntentan\honam\TemplateRenderer;
use ntentan\honam\Templates;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\kaikai\Cache;
use ntentan\middleware\MvcMiddleware;
use ntentan\nibii\factories\DriverAdapterFactory;
use ntentan\nibii\interfaces\DriverAdapterFactoryInterface;
use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\panie\Container;
use ntentan\interfaces\ContainerBuilderInterface;
use ntentan\config\Config;
use ntentan\sessions\SessionContainerFactory;
use ntentan\utils\filesystem\UploadedFile;
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

    public function __construct($namespace)
    {
        $this->container =new Container();
        $this->container->setup([
            Context::class => [
                function($container) use ($namespace) {
                    return new Context($container->get(Config::class), $namespace);
                },
                'singleton' => true
            ],
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
            ModelBinderRegistry::class => [
                function () {
                    $modelBinderRegistry = new ModelBinderRegistry();
                    $modelBinderRegistry->setDefaultBinderClass(DefaultModelBinder::class);
                    $modelBinderRegistry->register(View::class, ViewBinder::class);
                    $modelBinderRegistry->register(UploadedFile::class, UploadedFileBinder::class);
                    $modelBinderRegistry->register(Redirect::class, RedirectBinder::class);
                    return $modelBinderRegistry;
                }
            ],

            // Wire up the application class
            Application::class => [ //Application::class,
                function ($container) use ($namespace) {
                    $config = $container->get(Config::class);
                    $application = new Application(
                        $container->get(Context::class),
                        $container->get(Router::class),
                        $config,
                        $container->get(PipelineRunner::class),
                        $container->get(Cache::class),
                        $container->get(SessionContainerFactory::class),
                        $namespace
                    );
                    if($config->isKeySet('db')) {
                        $application->setDatabaseDriverFactory($container->get(DriverFactory::class));
                        $application->setOrmFactories(
                            $container->get(ModelFactoryInterface::class),
                            $container->get(DriverAdapterFactoryInterface::class),
                            $container->get(ValidatorFactoryInterface::class)
                        );
                    }
                    return $application;
                },
                'calls' => ['setMiddlewareFactoryRegistry'] //, 'setDatabaseDriverFactory', 'setOrmFactories']
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