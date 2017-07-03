<?php

namespace ntentan;

abstract class Middleware
{

    /**
     * The instance of the pipeline runner currently running.
     * @var PipelineRunner
     */
    private $runner;
    private $parameters;

    public abstract function run($route, $response);

    public function injectRunner(PipelineRunner $runner)
    {
        $this->runner = $runner;
    }

    public function setParameters($parameters)
    {
        $this->parameters = Parameters::wrap($parameters);
    }

    protected function next($response)
    {
        return $this->runner->runMiddleware($response);
    }

    protected function getParameters()
    {
        return $this->parameters;
    }

}
