<?php

namespace ntentan;

abstract class Middleware {
    
    /**
     * The instance of the pipeline runner currently running.
     * @var PipelineRunner
     */
    private $runner;
    
    public abstract function run($route, $response);
    
    public function injectRunner(PipelineRunner $runner) {
        $this->runner = $runner;
    }
    
    protected function next($response) {
        return $this->runner->runMiddleware($response);
    }

}
