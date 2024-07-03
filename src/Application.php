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
    private middleware\MiddlewareRegistry $registry;
    private array $pipeline;
    
    /**
     * Create an instance of the application.
     */
    public final function __construct(ServerRequestInterface $request, ResponseInterface $response, middleware\MiddlewareRegistry $registry)
    {
        $this->request = $request;
        $this->response = $response;
        $this->registry = $registry;
    }
    
    
    public function runPipeline(array $pipeline, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->pipeline = $pipeline;
        return $this->registry
            ->get($this->pipeline[0]) //, $this->pipeline[0][1] ?? [])
            ->run($request, $response, $this);
    }
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $nextMiddleWare = next($this->pipeline);
        if ($nextMiddleWare !== false) {
            $middleware = $this->registry->get($nextMiddleWare); 
            return $middleware->run($request, $response, $this);
        }
    }
    
    public function execute(string ...$pipeline): void
    {
        $response = $this->runPipeline($pipeline, $this->request, $this->response);
        http_response_code($response->getStatusCode());
        foreach($response->getHeaders() as $header => $values) {
            foreach($values as $value) {
                header("$header: $value");
            }
        }
        $body = $response->getBody();
        if ($body->isReadable()) {
            echo $body->getContents();   
        }
    }
}
