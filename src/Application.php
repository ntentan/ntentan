<?php

namespace ntentan;

use ntentan\Router;
use ntentan\config\Config;
use ntentan\utils\Input;
use ntentan\Context;
use ntentan\panie\Container;
use ntentan\middleware\MiddlewareFactory;

class Application
{
    private $pipeline = [];
    protected $router;
    protected $config;
    protected $prefix;
    private $runner;
    private $middlewareFactory;
    protected $container;
    protected $namespace;

    /**
     *
     * @param type $context
     */
    public final function __construct(Router $router, Config $config, PipelineRunner $runner, MiddlewareFactory $middlewareFactory)
    {
        Context::initialize();
        $this->router = $router;
        $this->config = $config;
        $this->runner = $runner;
        $this->middlewareFactory = $middlewareFactory;
        $this->prefix = $config->get('app.prefix');
        $this->prependMiddleware(middleware\MVCMiddleware::class);
    }

    protected function setup()
    {
    }
    

    public function appendMiddleware($class, $options = [])
    {
        $this->pipeline[] = $this->middlewareFactory->createMiddleware($class, $options);
    }

    public function prependMiddleware($class, $options = [])
    {
        array_unshift($this->pipeline, $this->middlewareFactory->createMiddleware($class, $options));
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
