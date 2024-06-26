<?php

namespace ntentan\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\Middleware;
use ntentan\Router;
use ntentan\panie\Inject;
use ntentan\middleware\mvc\ServiceContainerBuilder;
use ntentan\panie\Container;
use ntentan\Controller;
use ntentan\utils\Text;
use ntentan\exceptions\NtentanException;
use ntentan\controllers\ModelBinderRegistry;

/**
 * 
 */
class MvcMiddleware implements Middleware
{

    private Router $router;
    
    private Container $serviceContainer;
    
    private ModelBinderRegistry $modelBinders;

    #[Inject]
    private string $namespace = 'app';

    public function __construct(Router $router, ServiceContainerBuilder $containerBuilder, ModelBinderRegistry $modelBinders)
    {
        $this->router = $router;
        $this->serviceContainer = $containerBuilder->getContainer();
        $this->modelBinders = $modelBinders;
    }

    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $uri = $request->getUri();
        $response = $response->withStatus(200);
        $route = $this->router->route($uri->getPath(), $uri->getQuery());
        $controllerClassName = sprintf(
            '\%s\controllers\%sController', $this->namespace, Text::ucamelize($route['controller'])
        );
        $controller = $this->serviceContainer->get($controllerClassName);
        $methods = $this->getListOfMethods($controller, $controllerClassName);
        $methodKey = "{$route['action']}." . strtolower($request->getMethod());
        
        if (isset($methods[$methodKey])) {
            $method = $methods[$methodKey];
            $callable = new \ReflectionMethod($controller, $method['name']);
            $argumentDescription = $callable->getParameters();
            $arguments = [];
            
            foreach($argumentDescription as $argument) {
                $arguments[] = $this->bindParameter($argument, $route);
            }
            
            $output = $callable->invokeArgs($controller, $arguments);
            return $response->withBody($output->asStream());
        }
        
        throw new NtentanException("Could not resolve a controller for the current request [{$uri->getPath()}].");
    }
    
    private function bindParameter(\ReflectionParameter $parameter, array $route)
    {
        $type = $parameter->getType();
        
        // Let's support single named types for now
        if (!($type instanceof \ReflectionNamedType)) {
            return null;
        }
        
        $binder = $this->serviceContainer->get($this->modelBinders->get($type->getName()));
        $binderData = [];
        
        foreach($binder->getRequirements() as $required) {
            $binderData[$required] = match($required) {
                'instance' => $this->serviceContainer->get($type->getName()),
                'route' => $route,
                default => throw new NtentanException("Cannot satisfy data binding requirement: {$required}")
            };
        }
        
        return $binder->bind($binderData);
    }
    
    private function getListOfMethods(object $controller, string $className): array
    {
        $methods = (new \ReflectionClass($controller))->getMethods(\ReflectionMethod::IS_PUBLIC);
        $results = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();

            // Skip internal methods
            if (substr($methodName, 0, 2) == '__') {
                continue;
            }
            $action = $methodName;
            $requestMethod = ".get";

            foreach ($method->getAttributes() as $attribute) {
                match($attribute->getName()) {
                    Action::class => $action = $attribute->newInstance()->getPath(),
                    RequestMethod::class => $requestMethod = $attribute->newInstance()->getType()
                };
            }

            $methodKey = $action . $requestMethod;
            if (isset($results[$methodKey]) && $method->class != $className) {
                continue;
            }

            $results[$methodKey] = [
                'name' => $methodName,
//                'binder' => $binder 
//                    ?? $controller->getDefaultModelBinderClass() 
//                    ?? $this->modelBinderRegistry->getDefaultBinderClass()
            ];
        }
        return $results;
    }
    
    public function setup(array $config): MvcMiddleware
    {
        $this->router->setRoutes($config['controllers']['routes']);
        return $this;
    }
}
