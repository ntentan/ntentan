<?php
namespace ntentan\middleware;

use ntentan\exceptions\NtentanException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The middleware queue holds an ordered list of middleware objects.
 */
class MiddlewareQueue
{
    private array $registry = [];
    private array $pipeline = [];

    private ResponseInterface $pendingResponse;

    /**
     * Create a new middleware queue.
     * @param array $pipeline A list of middleware along with their configurations. Middle ware are executed in the order presented.
     * @param array $factories An associative array of functions for creating the middleware classes.
     */
    public function __construct(array $pipeline, array $factories) {
        $this->pipeline = $pipeline;
        $this->registry = $factories;
    }
    
    public function iterate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->registry[$this->pipeline[0]]()->run($request, $response, $this->next(...));
    }

    /**
     * Runs the next item on the middleware queue.
     * @throws NtentanException
     */
    private function next(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $nextMiddleWare = next($this->pipeline);
        
        if ($nextMiddleWare !== false) {
            if (!isset($this->registry[$nextMiddleWare])) {
                throw new NtentanException("{$nextMiddleWare} has not been registered as a middleware.");
            }
            $middleware = $this->registry[$nextMiddleWare]();
            return $middleware->run($request, $response, $this->next(...));
        }
        return $response;
    }
}
