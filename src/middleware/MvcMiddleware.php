<?php

namespace ntentan\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use ntentan\Middleware;
use ntentan\Router;
use ntentan\panie\Inject;
use ntentan\middleware\mvc\ServiceContainerBuilder;
use ntentan\panie\Container;
use ntentan\utils\Text;
use ntentan\exceptions\NtentanException;
use ntentan\controllers\ModelBinderRegistry;
use ntentan\attributes\Action;
use ntentan\attributes\Method;
use ntentan\View;
use ntentan\http\StringStream;

/**
 * Responds to requests by initializing classes according to an MVC pattern.
 */
class MvcMiddleware implements Middleware
{

    private Router $router;
    
    private Container $serviceContainer;
    
    private ServiceContainerBuilder $containerBuilder;
    
    private ModelBinderRegistry $modelBinders;

    #[Inject]
    private string $namespace = 'app';

    public function __construct(Router $router, ServiceContainerBuilder $containerBuilder, ModelBinderRegistry $modelBinders, array $mvcConfig)
    {
        $this->router = $router;
        $this->containerBuilder = $containerBuilder;
        $this->modelBinders = $modelBinders;
        $this->router->setRoutes($mvcConfig['routes']);
    }

    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->serviceContainer = $this->containerBuilder->getContainer($request, $response);
        $uri = $request->getUri();
        $response = $response->withStatus(200);
        $route = $this->router->route($uri->getPath(), $uri->getQuery());
        $controllerClassName = sprintf(
            '\%s\controllers\%sController', $this->namespace, Text::ucamelize($route['controller'])
        );
        $controller = $this->serviceContainer->get($controllerClassName);
        $methods = $this->getListOfMethods($controller, $controllerClassName);
        $methodKey = "{$route['action']}." . strtolower($request->getMethod());
        $routeParameters = array_keys($route);
        
        if (isset($methods[$methodKey])) {
            $method = $methods[$methodKey];
            $callable = new \ReflectionMethod($controller, $method['name']);
            $argumentDescription = $callable->getParameters();
            $arguments = [];
            
            foreach($argumentDescription as $argument) {
                if ($argument->getType()->isBuiltIn() && in_array($argument->getName(), $routeParameters)) {
                    $arguments[] = $route[$argument->getName()];
                } else {
                    $arguments[] = $this->bindParameter($argument, $route);
                }
            }
            
            $output = $callable->invokeArgs($controller, $arguments);
            
            return match(true) {
                $output instanceof View => $response->withBody($output->asStream()),
                $output instanceof ResponseInterface => $output,
                gettype($output) === 'string' => $response->withBody(new StringStream($output)),
                default => throw new NtentanException("Controller returned an unexpected " 
                        . ($output === null ? "null output" : "object of type " .get_class($output)))
            };
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
                    Method::class => $requestMethod = "." . strtolower($attribute->newInstance()->getType())
                };
            }

            $methodKey = $action . $requestMethod;
            if (isset($results[$methodKey]) && $method->class != $className) {
                continue;
            }

            $results[$methodKey] = [
                'name' => $methodName
            ];
        }
        return $results;
    }
}
