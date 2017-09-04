<?php

namespace ntentan;

use ntentan\controllers\model_binders\DefaultModelBinder;
use ntentan\kaikai\Cache;
use ntentan\middleware\auth\AbstractAuthMethod;
use ntentan\Router;
use ntentan\config\Config;
use ntentan\utils\Input;
use ntentan\Context;
use ntentan\middleware\MiddlewareFactory;
use ntentan\controllers\ModelBinderRegister;
use ntentan\AbstractMiddleware;

class Application
{
    private $pipeline = [];
    protected $router;
    protected $config;
    protected $prefix;
    private $runner;
    private $context;

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
        $this->prefix = $config->get('app.prefix');
    }

    protected function setup()
    {
    }
    
    public function setModelBinderRegister(ModelBinderRegister $modelBinderRegister)
    {
        $this->modelBinderRegister = $modelBinderRegister;
        $modelBinderRegister->setDefaultBinderClass(DefaultModelBinder::class);
        $this->context->setModelBinderRegister($modelBinderRegister);
    }

    public function appendMiddleware(AbstractMiddleware $middleware, $options)
    {
        $middleware->setParameters($options);
        $this->pipeline[] = $middleware;
    }

    public function prependMiddleware(AbstractMiddleware $middleware, $options = [])
    {
        $middleware->setParameters($options);
        array_unshift($this->pipeline, $middleware);
    }
    
    private function startSession()
    {
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
    }

    public function execute()
    {
        $this->setup();
        $this->startSession();
        $route = $this->router->route(substr(parse_url(Input::server('REQUEST_URI'), PHP_URL_PATH), 1), $this->prefix);
        $pipeline = $route['description']['parameters']['pipeline'] ?? $this->pipeline;
        echo $this->runner->run($pipeline, $route);
    }    
}
