<?php

namespace ntentan;

use ntentan\atiaa\DbContext;
use ntentan\atiaa\DriverFactory;
use ntentan\controllers\model_binders\DefaultModelBinder;
use ntentan\kaikai\Cache;
use ntentan\middleware\auth\AbstractAuthMethod;
use ntentan\nibii\DriverAdapterFactoryInterface;
use ntentan\nibii\ModelFactoryInterface;
use ntentan\nibii\ORMContext;
use ntentan\Router;
use ntentan\config\Config;
use ntentan\utils\Input;
use ntentan\Context;
use ntentan\middleware\MiddlewareFactory;
use ntentan\controllers\ModelBinderRegister;
use ntentan\AbstractMiddleware;
use ntentan\atiaa\Driver;

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

    /**
     *
     * @param type $context
     */
    public final function __construct(Router $router, Config $config, PipelineRunner $runner, Cache $cache, string $namespace)
    {
        $this->context = Context::initialize($namespace);
        $this->context->setCache($cache);
        $this->router = $router;
        $this->config = $config;
        $this->runner = $runner;
        $this->cache = $cache;
        $this->prefix = $config->get('app.prefix');
    }

    protected function setup() : void
    {
    }

    public function setDriverFactory(DriverFactory $driverFactory) : void
    {
        DbContext::initialize($driverFactory);
    }

    public function setOrmFactories(ModelFactoryInterface $modelFactory, DriverAdapterFactoryInterface $driverAdapterFactory) : void
    {
        ORMContext::initialize($modelFactory, $driverAdapterFactory, DbContext::getInstance(), $this->cache);
    }
    
    public function setModelBinderRegister(ModelBinderRegister $modelBinderRegister) : void
    {
        $this->modelBinderRegister = $modelBinderRegister;
        $modelBinderRegister->setDefaultBinderClass(DefaultModelBinder::class);
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
    
    /*private function startSession()
    {
        // Replace with a factory oya!
        $sessionContainerType = $this->config->get('app.sessions.container', 'default');
        switch($sessionContainerType) {
            case 'none':
                return;
            case 'default':
                break;
            default:
                $this->container->resolve(SessionContainer::getClassName($sessionContainerType));
        }
        session_start();        
    }*/

    public function execute()
    {
        $this->setup();
        //$this->startSession();
        $route = $this->router->route(substr(parse_url(Input::server('REQUEST_URI'), PHP_URL_PATH), 1), $this->prefix);
        $pipeline = $route['description']['parameters']['pipeline'] ?? $this->pipeline;
        echo $this->runner->run($pipeline, $route);
    }    
}
