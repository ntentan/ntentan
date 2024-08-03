<?php
namespace ntentan\middleware;

use ntentan\exceptions\NtentanException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\http\Uri;

class MiddlewareQueue
{
    private array $register = [];
    private array $pipeline = [];
    
    private function __construct(array $pipeline, array $register) {
        $this->pipeline = $pipeline;
        $this->register = $register;
    }
    
    public static function prefix(string $prefix): callable
    {
        return function(ServerRequestInterface $request) use ($prefix) {
            $uri = $request->getUri();
            $path = $uri->getPath();
            if (substr($path, 0, strlen($prefix)) == $prefix) {
                $path = substr($_SERVER['REQUEST_URI'], strlen($prefix));
                $path = $path == "" ? '/' : $path;
                if ($uri instanceof Uri) {
                    $uri->withPrefix($prefix);
                }
                $request->withUri($uri->withPath($path), true);
                return true;
            }
        };
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
    
    public static function setup(array $queue)
    {
            return [
            self::class => [
                function($container) use ($queue) {
                    
                    $selectedQueue = [];
                    if (count($queue) == 1) {
                        $selectedQueue = reset($queue)['pipeline'];
                    } else {
                        $request = $container->get(ServerRequestInterface::class);
                        foreach($queue as $name => $pipeline) {
                            if ($name == 'default') {
                                $selectedQueue = $pipeline['pipeline'];
                                continue;
                            }
                            if (isset($pipeline['filter']) && $pipeline['filter']($request)) {
                                $selectedQueue = $pipeline['pipeline'];
                                break;
                            }
                        }
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
