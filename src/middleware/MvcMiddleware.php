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

/**
 * 
 */
class MvcMiddleware implements Middleware
{

    private Router $router;
    
    private Container $serviceContainer;

    #[Inject]
    private string $namespace = 'app';

    public function __construct(Router $router, ServiceContainerBuilder $containerBuilder) //ControllerFactoryInterface $controllerFactory)
    {
        $this->router = $router;
        $this->serviceContainer = $containerBuilder->getContainer();
    }

    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $uri = $request->getUri();
        $parameters = $this->router->route($uri->getPath(), $uri->getQuery());
        $controllerClassName = sprintf(
            '\%s\controllers\%sController', $this->namespace, Text::ucamelize($parameters['controller'])
        );
        $controller = $this->serviceContainer->get($controllerClassName);
        $methods = $this->getListOfMethods($controller, $controllerClassName);
        $methodKey = "{$parameters['action']}." . strtolower($request->getMethod());
        var_dump($methods[$methodKey]);
    }    
    
    private function getListOfMethods(Controller $controller, string $className): array
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
