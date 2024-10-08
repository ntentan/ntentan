<?php
namespace ntentan;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\middleware\MiddlewareQueue;


/**
 * Application bootstrapping class.
 */
class Application
{
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private MiddlewareQueue $registry;
    
    /**
     * Create an instance of the application.
     */
    public final function __construct(MiddlewareQueue $registry, ServerRequestInterface $request, ResponseInterface $response)
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
