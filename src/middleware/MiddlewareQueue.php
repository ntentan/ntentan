<?php

namespace ntentan\middleware;

use ntentan\exceptions\NtentanException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class MiddlewareQueue
{
    private array $register = [];
    private array $pipeline = [];
    
    private function __construct(array $pipeline, array $register) {
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
    
    public static function setup(array $queue) //string ...$queue) 
    {
        return [
            self::class => [
                function($container) use ($queue) {
                    $selectedQueue = [];
                    if (count($queue) == 1) {
                        $selectedQueue = reset($queue);
                    }
                    $register = [];
                    $finalQueue = [];

                    foreach($selectedQueue as $middlewareEntry) {
                        list($middlewareClass, $config) = $middlewareEntry;
                        $finalQueue[]=$middlewareClass;
                        $register[$middlewareClass] = function() use ($container, $config, $middlewareClass) {
                            $middleware = $container->get($middlewareClass);
                            $middleware->configure($config);
                            return $middleware;
                        };
                    }
                    return new MiddlewareQueue($finalQueue, $register);
                },
                'singleton' => true
            ]
        ];      
    }
}
