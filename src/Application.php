<?php

namespace ntentan;

use ntentan\atiaa\DbContext;
use ntentan\atiaa\DriverFactory;
use ntentan\kaikai\Cache;
use ntentan\nibii\interfaces\DriverAdapterFactoryInterface;
use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\nibii\ORMContext;
use ntentan\config\Config;
use ntentan\sessions\SessionContainerFactory;
use ntentan\nibii\interfaces\ValidatorFactoryInterface;
use ntentan\middleware\MiddlewareFactoryRegistry;

/**
 * Application bootstrapping class.
 */
class Application
{
    private $defaultPipeline = [];
    protected $router;
    protected $config;
    protected $prefix;
    private $runner;
    private $context;
    private $cache;
    private $sessionContainerFactory;

    /**
     * @var MiddlewareFactoryRegistry
     */
    protected $middlewareFactoryRegistry;

    /**
     *
     * @param Router $router
     * @param Config $config
     * @param PipelineRunner $runner
     * @param Cache $cache
     * @param SessionContainerFactory $sessionContainerFactory
     */
    public final function __construct(Context $context, Router $router, Config $config, PipelineRunner $runner, Cache $cache, SessionContainerFactory $sessionContainerFactory)
    {
        $this->context = $context;
        $this->router = $router;
        $this->config = $config;
        $this->runner = $runner;
        $this->cache = $cache;
        $this->sessionContainerFactory = $sessionContainerFactory;
        $this->prefix = $config->get('app.prefix') ?? "";
    }

    protected function setup() : void
    {
        $routes = require "bootstrap/routes.php";
        $this->router->setRoutes($routes['routes']);
        $this->defaultPipeline = $routes['default_pipeline'];
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
        $route = $this->router->route(substr(parse_url(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL), PHP_URL_PATH), 1), $this->prefix);
        $this->context->setParameter('route', $route['route']);
        $this->context->setParameter('route_parameters', $route['parameters']);
        $pipeline = $this->buildPipeline($route['description']['parameters']['pipeline'] ?? $this->defaultPipeline);
        echo $this->runner->run($pipeline, $route);
    }    
}
