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
    
    public function execute(): void
    {
        $response = $this->registry->iterate($this->request, $this->response);
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
