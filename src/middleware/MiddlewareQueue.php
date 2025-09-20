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
    
    public function __construct(array $pipeline, array $registry) {
        $this->pipeline = $pipeline;
        $this->registry = $registry;
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
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json')
            ->write(json_encode(
                "It appears we reached the end of the middleware queue without a proper response prepared"
            ));
    }
}
