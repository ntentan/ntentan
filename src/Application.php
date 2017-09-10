<?php

namespace ntentan;

use ntentan\atiaa\DbContext;
use ntentan\atiaa\DriverFactory;
use ntentan\controllers\model_binders\DefaultModelBinder;
use ntentan\kaikai\Cache;
use ntentan\nibii\interfaces\DriverAdapterFactoryInterface;
use ntentan\nibii\interfaces\ModelFactoryInterface;
use ntentan\nibii\ORMContext;
use ntentan\Router;
use ntentan\config\Config;
use ntentan\sessions\SessionContainerFactory;
use ntentan\utils\Input;
use ntentan\Context;
use ntentan\controllers\ModelBinderRegister;
use ntentan\AbstractMiddleware;
use ntentan\nibii\interfaces\ValidatorFactoryInterface;

/**
 * Application bootstrapping class.
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
    protected $modelBinderRegister;

    /**
     *
     * @param type $context
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
    }

    protected function setup() : void
    {
    }

    public function setDriverFactory(DriverFactory $driverFactory) : void
    {
        DbContext::initialize($driverFactory);
    }

    public function setOrmFactories(ModelFactoryInterface $modelFactory, DriverAdapterFactoryInterface $driverAdapterFactory, ValidatorFactoryInterface $modelValidatorFactory) : void
    {
        ORMContext::initialize($modelFactory, $driverAdapterFactory, $modelValidatorFactory, DbContext::getInstance(), $this->cache);
    }
    
    public function setModelBinderRegister(ModelBinderRegister $modelBinderRegister) : void
    {
        $this->modelBinderRegister = $modelBinderRegister;
        $modelBinderRegister->setDefaultBinderClass(DefaultModelBinder::class);
        $modelBinderRegister->register(View::class, controllers\model_binders\ViewBinder::class);
        $this->context->setModelBinderRegister($modelBinderRegister);
    }

    public function appendMiddleware(AbstractMiddleware $middleware)
    {
        $this->pipeline[] = $middleware;
    }

    public function prependMiddleware(AbstractMiddleware $middleware)
    {
        array_unshift($this->pipeline, $middleware);
    }

    public function execute()
    {
        $this->setup();
        $this->sessionContainerFactory->createSessionContainer();
        $route = $this->router->route(substr(parse_url(Input::server('REQUEST_URI'), PHP_URL_PATH), 1), $this->prefix);
        $pipeline = $route['description']['parameters']['pipeline'] ?? $this->pipeline;
        echo $this->runner->run($pipeline, $route);
    }    
}
