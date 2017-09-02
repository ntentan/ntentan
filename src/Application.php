<?php

namespace ntentan;

use ntentan\Router;
use ntentan\config\Config;
use ntentan\utils\Input;
use ntentan\Context;

class Application
{
    private $pipeline = [];
    protected $router;
    protected $config;
    protected $prefix;
    private $runner;
    protected $container;
    protected $namespace;

    /**
     *
     * @param type $context
     */
    private function __construct($namespace)
    {
        $this->container = ContainerBuilder::getContainer();
        Context::initialize();
        $this->router = $this->container->resolve(Router::class);
        $this->config = $this->container->resolve(Config::class);
        $this->runner = $this->container->resolve(PipelineRunner::class);
        $this->prefix = $this->config->get('app.prefix');
        $this->prependMiddleware(middleware\MVCMiddleware::class);
    }
    
    public static function initialize($namespace)
    {
        $class = get_called_class();
        return new $class($namespace);
    }

    protected function setup()
    {
    }
    
    private function setupMiddleware($class, $options)
    {
        $instance = $this->container->resolve($class);
        $instance->setParameters($options);
        return $instance;
    }

    public function appendMiddleware($class, $options = [])
    {
        $this->pipeline[] = $this->setupMiddleware($class, $options);
    }

    public function prependMiddleware($class, $options = [])
    {
        array_unshift($this->pipeline, $this->setupMiddleware($class, $options));
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
