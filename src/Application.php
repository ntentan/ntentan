<?php
namespace ntentan;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * Application bootstrapping class.
 */
class Application
{
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private PipelineRunner $runner;
    
    /**
     * Create an instance of the application.
     */
    public final function __construct(ServerRequestInterface $request, ResponseInterface $response, PipelineRunner $runner)
    {
        $this->request = $request;
        $this->response = $response;
        $this->runner = $runner;
    }

    public function execute(array $pipeline): void
    {
        $this->runner->run($pipeline, $this->request, $this->response);
    }
}
