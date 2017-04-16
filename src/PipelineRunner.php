<?php
namespace ntentan;

class PipelineRunner {
    
    private $pipeline;
    
    private $container;
    
    private $route;
    
    public function __construct(Context $context) {
        $this->container = $context->getContainer();
    }
    
    public function run($pipeline, $route) {
        $this->pipeline = $pipeline;
        $this->route = $route;
        return $this->runMiddleware();
    }
    
    public function runMiddleware($response = null) {
        $middleware = array_pop($this->pipeline);
        $instance = $this->container->resolve($middleware);
        $instance->injectRunner($this);
        return $instance->run($this->route, $response);
    }
}