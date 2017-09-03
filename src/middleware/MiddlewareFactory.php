<?php

namespace ntentan\middleware;

use ntentan\PipelineRunner;

class MiddlewareFactory
{
    private $runner;
    
    public function __construct(PipelineRunner $runner)
    {
        $this->runner = $runner;
    }
    
    public function createMiddleware($class, $options = [])
    {
        $instance = new $class();
        $instance->setParameters($options);
        return $instance;
    }
}

