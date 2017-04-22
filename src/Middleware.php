<?php

namespace ntentan;

abstract class Middleware {
    
    /**
     * The instance of the pipeline runner currently running.
     * @var PipelineRunner
     */
    private $runner;
    private static $parameters;
    
    public abstract function run($route, $response);
    
    public function injectRunner(PipelineRunner $runner) {
        $this->runner = $runner;
    }
    
    protected function next($response) {
        return $this->runner->runMiddleware($response);
    }
    
    public static function with($parameters) {
        self::$parameters = Parameters::wrap($parameters);
        return get_called_class();
    }
    
    protected function getParameters() {
        return self::$parameters ?? Parameters::wrap([]);
    }

}
