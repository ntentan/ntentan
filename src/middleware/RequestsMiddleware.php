<?php

namespace ntentan\middleware;

use ntentan\http\filters\RequestFilter;
use ntentan\kaikai\Cache;
use ntentan\ServiceContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ntentan\ServiceContainerBuilder;
use ReflectionClass;

class RequestsMiddleware implements Middleware
{
    use ServiceContainer;

    private Cache $cache;
    private array $mapping;


    public function __construct(Cache $cache, ServiceContainerBuilder $containerBuilder)
    {
        $this->cache = $cache;
        $this->containerBuilder = $containerBuilder;
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $serviceContainer = $this->getServiceContainer($request, $response);
        foreach($this->mapping as $route) {
            $success = true;
            $variables = [];
            foreach($route['attributes'] as $attribute) {
                /** @var RequestFilter $attributeInstance */
                $attributeInstance = $attribute;

                if (!$attributeInstance->match($request)) {
                    $success = false;
                    break;
                };

                $variables = [...$variables, ...$attributeInstance->getValues()];
            }

            if ($success) {
                $handler = $serviceContainer->get($route['class']);
                $arguments = $serviceContainer->getMethodArguments($route['method']);
                return $route['method']->invokeArgs($handler, $arguments);
            }
        }
        return $response->withStatus(404);
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
            $parameters = [];
            foreach($method->getParameters() as $parameter) {
                $parameters[$parameter->name] = ['type' => $parameter->getType()];
            }
            if (!empty($attributes)) {
                $routes[] = [
                    'class' => $class->name, 'method' => $method,
                    'attributes' => array_map(
                        fn($attribute) => $attribute->newInstance(),
                        $attributes
                    ),
                    'parameters' => $parameters
                ];
            }
        }

        return $routes;
    }

    public function configure(array $configuration): void
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
