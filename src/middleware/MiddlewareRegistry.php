<?php

namespace ntentan\middleware;

use ntentan\exceptions\NtentanException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MiddlewareRegistry
{
    private $register = [];
    private $pipeline = [];
    
    private function __construct($pipeline, $register) {
        $this->pipeline = $pipeline;
        $this->register = $register;
    }
    
    public function iterate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->register[$this->pipeline[0]]()->run($request, $response, $this->next(...));        
    }
    
    private function next(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $nextMiddleWare = next($this->pipeline);
        
        if ($nextMiddleWare !== false) {
            if (!isset($this->register[$nextMiddleWare])) {
                throw new NtentanException("{$nextMiddleWare} has not been registered as a middleware.");
            }
            $middleware = $this->register[$nextMiddleWare]();
            return $middleware->run($request, $response, $this->next(...));
        }
    }    
    
    public static function setup(string ...$queue) 
    {
        return [
            self::class => [
                function($container) use ($queue) {
                    $register = [];
                    foreach($queue as $middleware) {
                        $register[$middleware] = fn() => $container->get($middleware);
                    }
                    return new MiddlewareRegistry($queue, $register);
                },
                'singleton' => true
            ]
        ];      
    }
}
