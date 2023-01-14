<?php

namespace ntentan\middleware\mvc;

use Closure;
use ntentan\controllers\ModelBinderRegistry;
use ntentan\exceptions\NtentanException;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\kaikai\Cache;
use ntentan\panie\Container;
use ntentan\Context;
use ntentan\utils\Text;
use ntentan\Controller;
use ntentan\exceptions\ControllerActionNotFoundException;
use ntentan\attributes\Action;
use ntentan\attributes\RequestMethod;
use ntentan\config\Config;

/**
 * Class DefaultControllerFactory
 * @package ntentan\middleware\mvc
 */
class DefaultControllerFactory implements ControllerFactoryInterface
{
    /**
     * A container used as a service container for the controller execution phase.
     * @var Container
     */
    private $serviceContainer;

    /**
     * An instance of the ModelBinderRegistry that holds model binders for all types.
     * @var ModelBinderRegistry
     */
    private $modelBinderRegistry;

    protected $context;

    private $cache;

    /**
     * DefaultControllerFactory constructor.
     * @param Context $context
     * @param Cache $cache
     * @param ModelBinderRegistry $modelBinderRegistry
     * @param ServiceContainerBuilder $serviceContainerBuilder
     */
    public function __construct(Context $context, Cache $cache, ModelBinderRegistry $modelBinderRegistry, ServiceContainerBuilder $serviceContainerBuilder)
    {
        $this->serviceContainer = $serviceContainerBuilder->getContainer();
        $this->context = $context;
        $this->cache = $cache;
        $this->modelBinderRegistry = $modelBinderRegistry;
        $closure = Closure::bind(Closure::fromCallable(function () { return (require "bootstrap/services.php")['mvc']; }), null);
        $this->serviceContainer->setup($closure());
    }
    
    private function bindParameter(Controller $controller, &$invokeParameters, $methodParameter, $params)
    {
        $decamelizedParameter = Text::deCamelize($methodParameter->name);
        if (isset($params[$methodParameter->name]) || isset($params[$decamelizedParameter])) {
            $invokeParameters[] = $params[$methodParameter->name] ?? $params[$decamelizedParameter];
        } else {
            //$type = $methodParameter->getClass();
            $type = $methodParameter->getType();
            if ($type !== null) {
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
            //$docComments = $this->parseDocComment($method->getDocComment());
            //$keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['method'] : $methodName;

            // if(isset($results[$keyName]) && $method->class != $className) {
            //     continue;
            // }
            // $results[$keyName] = [
            //     'name' => $method->getName(),
            //     'binder' => $docComments['binder']
            //         ?? $controller->getDefaultModelBinderClass()
            //         ?? $this->modelBinderRegistry->getDefaultBinderClass(),
            //     'binder_params' => $docComments['binder.params'] ?? ''
            // ];
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

    public function createController(array &$parameters): Controller
    {
        $controller = $parameters['controller'];
        if($controller == null) {
            throw new NtentanException("There is no controller specified for this request");
        }
        $controllerClassName = sprintf('\%s\controllers\%sController', $this->context->getNamespace(), Text::ucamelize($controller));
        $this->context->setParameter('controller_path', $this->context->getUrl($controller));
        return $this->serviceContainer->resolve($controllerClassName);
    }

    public function executeController(Controller $controller, array $parameters): string
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
            return $output;
        }
        throw new ControllerActionNotFoundException($this, $methodName);
    }
}
