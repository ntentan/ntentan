<?php

namespace ntentan\middleware\mvc;

use ntentan\controllers\ModelBinderRegistry;
use ntentan\exceptions\NtentanException;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\kaikai\Cache;
use ntentan\Context;
use ntentan\utils\Text;
use ntentan\Controller;
use ntentan\exceptions\ControllerActionNotFoundException;
use ntentan\attributes\Action;
use ntentan\attributes\RequestMethod;
use ntentan\Router;
use ntentan\panie\Inject;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DefaultControllerFactory
 * @package ntentan\middleware\mvc
 */
class DefaultControllerFactory implements ControllerFactoryInterface
{
    // /**
    //  * A container used as a service container for the controller execution phase.
    //  * @var Container
    //  */
    // private $serviceContainer;

    // /**
    //  * An instance of the ModelBinderRegistry that holds model binders for all types.
    //  * @var ModelBinderRegistry
    //  */
    // private $modelBinderRegistry;

    // private Context $context;

    // private $cache;
    
    private Router $router;

    #[Inject]
    private string $namespace = 'app';

    /**
     * DefaultControllerFactory constructor.
     * @param Context $context
     * @param Cache $cache
     * @param ModelBinderRegistry $modelBinderRegistry
     * @param ServiceContainerBuilder $serviceContainerBuilder
     */
    public function __construct(Router $router) //ModelBinderRegistry $modelBinderRegistry, ServiceContainerBuilder $serviceContainerBuilder, Router $router)
    {
        $this->router = $router;
        // $this->namespace = $context->getNamespace();
//        $this->serviceContainer = $serviceContainerBuilder->getContainer();
//        $this->modelBinderRegistry = $modelBinderRegistry;
//        $closure = Closure::bind(Closure::fromCallable(function () { return (require "bootstrap/services.php")['mvc']; }), null);
//        $this->serviceContainer->setup($closure());
    }
    
    private function bindParameter(Controller $controller, &$invokeParameters, $methodParameter, $params)
    {
        $decamelizedParameter = Text::deCamelize($methodParameter->name);
        if (isset($params[$methodParameter->name]) || isset($params[$decamelizedParameter])) {
            $invokeParameters[] = $params[$methodParameter->name] ?? $params[$decamelizedParameter];
        } else {
            $type = $methodParameter->getType();
            if ($type !== null && ! $type->isBuiltin()) {
                $binder = $this->serviceContainer->resolve($this->modelBinderRegistry->get($type->getName()));
                $instance = null;
                $typeName = $type->getName();
                if($binder->requiresInstance()) {
                    $instance = $this->serviceContainer->resolve($typeName);
                }
                $invokeParameters[] = $binder->bind($controller, $typeName, $methodParameter->name, $params, $instance);
            } else {
                $invokeParameters[] = $methodParameter->isDefaultValueAvailable() ? $methodParameter->getDefaultValue() : null;
            }
        }
    }

    private function getListOfMethods($controller, $className, $methods)
    {
        $results = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();

            // Skip internal methods
            if (substr($methodName, 0, 2) == '__') {
                continue;
            }
            $action = $methodName;
            $requestMethod = "";

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
                'binder' => $binder 
                    ?? $controller->getDefaultModelBinderClass() 
                    ?? $this->modelBinderRegistry->getDefaultBinderClass()
            ];
        }
        return $results;
    }

    private function getMethod(Controller $controller, $path)
    {
        $reflectionClass = new \ReflectionClass($controller);
        $className = $reflectionClass->getName();

        /* @var $methods array */
        $methods = $this->cache->read("controller.{$className}.methods",
            function() use ($controller, $reflectionClass, $className){
                $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
                return $this->getListOfMethods($controller, $className, $methods);
            }
        );

        $specialMethod = $path . filter_var($_SERVER['REQUEST_METHOD']);
        if (isset($methods[$specialMethod])) {
            return $methods[$specialMethod];
        } elseif (isset($methods[$path])) {
            return $methods[$path];
        }

        return false;
    }
    
    #[\Override]
    public function create(ServerRequestInterface $request): Controller
    {
        $uri = $request->getUri();
        $parameters = $this->router->route($uri->getPath(), $uri->getQuery());
        $controllerClassName = sprintf(
            '\%s\controllers\%sController', $this->namespace, Text::ucamelize($parameters['controller'])
        );
        return new $controllerClassName();
//        $controller = $parameters['controller'];
//        if($controller == null) {
//            throw new NtentanException("There is no controller specified for this request");
//        }
//        $controllerClassName = sprintf('\%s\controllers\%sController', $this->context->getNamespace(), Text::ucamelize($controller));
//        $this->context->setParameter('controller_path', $this->context->getUrl($controller));
//        return $this->serviceContainer->resolve($controllerClassName);
    }

    #[\Override]
    public function run(): ResponseInterface //(Controller $controller, array $parameters): string
    {
        $action = $parameters['action'];
        $methodName = Text::camelize($action);
        $invokeParameters = [];
        $methodDetails = $this->getMethod($controller, $methodName);

        if ($methodDetails !== false) {
            $controller->setActionMethod($action ?? 'index');
            $method = new \ReflectionMethod($controller, $methodDetails['name']);
            $methodParameters = $method->getParameters();
            $this->modelBinderRegistry->setDefaultBinderClass($methodDetails['binder']);
            foreach ($methodParameters as $methodParameter) {
                $this->bindParameter($controller, $invokeParameters, $methodParameter, $parameters);
            }
            $output = $method->invokeArgs($controller, $invokeParameters);
            if($output === null) {
                throw new NtentanException("Output from the $action action cannot be null");
            }
            return (string) $output;
        }
        throw new ControllerActionNotFoundException($this, $methodName);
    }

    #[\Override]
    public function setup(array $config): void {
        $this->router->setRoutes($config['routes']);
    }
}
