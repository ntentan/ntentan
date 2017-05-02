<?php
namespace ntentan;

/**
 * Runs a pipeline of application middleware.
 */
class PipelineRunner {
    
    /**
     * An array of middleware that exist in the pipeline.
     * @var array<Middleware>
     */
    private $pipeline;
    
    /**
     * Dependency injection container extracted from ntentan context.
     * @var \ntentan\panie\Container
     */
    private $container;
    
    /**
     * A description of the current rount being executed.
     * @var array<mixed>
     */
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
        $middleware = array_shift($this->pipeline);
        $instance = $this->container->resolve($middleware);
        $instance->injectRunner($this);
        return $instance->run($this->route, $response);
    }
}