<?php

namespace ntentan;

abstract class AbstractMiddleware
{

    /**
     * The instance of the pipeline runner currently running.
     * @var PipelineRunner
     */
    private $runner;
    private $parameters;

    abstract public function run($route, $response);

    public function setRunner(PipelineRunner $runner)
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
