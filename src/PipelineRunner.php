<?php

namespace ntentan;

/**
 * Runs a pipeline of application middleware.
 */
class PipelineRunner
{
    /**
     * An array of middleware that exist in the pipeline.
     * @var array<Middleware>
     */
    private $pipeline;

    /**
     * A description of the current rount being executed.
     * @var array<mixed>
     */
    private $route;

    public function run($pipeline, $route)
    {
        $this->pipeline = $pipeline;
        $this->route = $route;
        return $this->runMiddleware();
    }

    public function runMiddleware($response = null)
    {
        $middleware = array_shift($this->pipeline);
        $instance = $middleware;
        $instance->setRunner($this);
        return $instance->run($this->route, $response);
    }
}
