<?php

namespace ntentan\middleware;

use ntentan\http\filters\Route;
use ntentan\kaikai\Cache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

class RequestsMiddleware implements Middleware
{
    private Cache $cache;
    private array $mapping;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $uri = $request->getUri();
    }

    private function getHandlerRoutes($handler): array
    {
        $reflection = new ReflectionClass($handler);
        $routes = [];

        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Route::class);
            if (!empty($attributes)) {
                $routes[] = $method->getName();
            }
        }

        return $routes;
    }

    public function configure(array $configuration)
    {
        $this->mapping = $this->cache->read('ntentan_requests_map',
            function () {
                $mapping = [];
                foreach($configuration['handler'] ?? [] as $handler) {
                    $routes = $this->getHandlerRoutes($handler);
                }
                return $mapping;
            }
        );
    }
}
