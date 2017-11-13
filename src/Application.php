<?php

namespace ntentan;

use ntentan\atiaa\DbContext;
use ntentan\atiaa\DriverFactory;
use ntentan\controllers\model_binders\DefaultModelBinder;
use ntentan\controllers\model_binders\UploadedFileBinder;
use ntentan\kaikai\Cache;
use ntentan\nibii\interfaces\DriverAdapterFactoryInterface;
use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\nibii\ORMContext;
use ntentan\config\Config;
use ntentan\sessions\SessionContainerFactory;
use ntentan\utils\filesystem\UploadedFile;
use ntentan\utils\Input;
use ntentan\controllers\ModelBinderRegistry;
use ntentan\nibii\interfaces\ValidatorFactoryInterface;
use ntentan\middleware\MiddlewareFactoryRegistry;

/**
 * Application bootstrapping class.
 * 
 * @package ntentan
 */
class Application
{
    private $pipeline = [];
    protected $router;
    protected $config;
    protected $prefix;
    private $runner;
    private $context;
    private $cache;
    private $sessionContainerFactory;
    protected $modelBinderRegistry;
    protected $middlewareFactoryRegistry;

    /**
     *
     * @param Router $router
     * @param Config $config
     * @param PipelineRunner $runner
     * @param Cache $cache
     * @param SessionContainerFactory $sessionContainerFactory
     * @param string $namespace
     */
    public final function __construct(Router $router, Config $config, PipelineRunner $runner, Cache $cache, SessionContainerFactory $sessionContainerFactory, string $namespace)
    {
        $this->context = Context::initialize($namespace, $config, $cache);
        $this->context->setCache($cache);
        $this->router = $router;
        $this->config = $config;
        $this->runner = $runner;
        $this->cache = $cache;
        $this->sessionContainerFactory = $sessionContainerFactory;
        $this->prefix = $config->get('app.prefix');
        $this->appendMiddleware(middleware\MvcMiddleware::class);
    }

    protected function setup() : void
    {
    }

    public function setDatabaseDriverFactory(DriverFactory $driverFactory) : void
    {
        DbContext::initialize($driverFactory);
    }

    public function setOrmFactories(ModelFactoryInterface $modelFactory, DriverAdapterFactoryInterface $driverAdapterFactory, ValidatorFactoryInterface $modelValidatorFactory) : void
    {
        ORMContext::initialize($modelFactory, $driverAdapterFactory, $modelValidatorFactory, $this->cache);
    }
    
    public function setMiddlewareFactoryRegistry(MiddlewareFactoryRegistry $middlewareFactoryRegistry)
    {
        $this->middlewareFactoryRegistry = $middlewareFactoryRegistry;
    }
    
    public function setModelBinderRegistry(ModelBinderRegistry $modelBinderRegistry) : void
    {
        $this->modelBinderRegistry = $modelBinderRegistry;
        $modelBinderRegistry->setDefaultBinderClass(DefaultModelBinder::class);
        $modelBinderRegistry->register(View::class, controllers\model_binders\ViewBinder::class);
        $modelBinderRegistry->register(UploadedFile::class, UploadedFileBinder::class);
        $this->context->setModelBinderRegistry($modelBinderRegistry);
    }

    public function appendMiddleware(string $middleware, array $parameters = [])
    {
        $this->pipeline[] = [$middleware, $parameters];
    }

    public function prependMiddleware(string $middleware, array $parameters = [])
    {
        array_unshift($this->pipeline, [$middleware, $parameters]);
    }
    
    private function buildPipeline($pipeline)
    {
        $instances = [];
        foreach($pipeline as $middleware) {
            $instance = $this->middlewareFactoryRegistry->getFactory($middleware[0])->createMiddleware($middleware[1] ?? []);
            $instances[] = $instance;
        }
        return $instances;
    }

    public function execute()
    {
        $this->setup();
        $this->sessionContainerFactory->createSessionContainer();
        $route = $this->router->route(substr(parse_url(Input::server('REQUEST_URI'), PHP_URL_PATH), 1), $this->prefix);
        $this->context->setParameter('route', $route['route']);
        $this->context->setParameter('route_parameters', $route['parameters']);
        $pipeline = $this->buildPipeline($route['description']['parameters']['pipeline'] ?? $this->pipeline);
        echo $this->runner->run($pipeline, $route);
    }    
}
