<?php

namespace ntentan\middleware;

use ntentan\http\filters\RequestFilter;
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
        return $response;
    }

    private function getHandlerRoutes($handler): array
    {
        $class = new ReflectionClass($handler);
        $routes = [];

        foreach ($class->getMethods() as $method) {
            $attributes = array_filter(
                $method->getAttributes(),
                fn($attribute) => is_subclass_of($attribute->getName(), RequestFilter::class)
            );
            if (!empty($attributes)) {
                $routes[] = [
                    'class' => $class->name, 'method' => $method->name,
                    'attributes' => array_map(
                        fn($attribute) => serialize($attribute->newInstance()),
                        $attributes
                    )
                ];
            }
        }

        return $routes;
    }

    public function configure(array $configuration)
    {
        $this->mapping = $this->cache->read('ntentan_requests_map',
            function () use ($configuration) {
                $mapping = [];
                foreach($configuration['handlers'] ?? [] as $handler) {
                    $mapping = [...$mapping, ...$this->getHandlerRoutes($handler)];
                }
                return $mapping;
            }
        );
    }
}
