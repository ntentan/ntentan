<?php

namespace ntentan\middleware\mvc;

use ntentan\controllers\ModelBinderRegistry;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\panie\Container;
use ntentan\Context;
use ntentan\utils\Text;
use ntentan\Controller;
use ntentan\utils\Input;
use ntentan\exceptions\ControllerActionNotFoundException;

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
    private $container;

    /**
     * An instance of the ModelBinderRegistry that holds model binders for all types.
     * @var ModelBinderRegistry
     */
    private $modelBinderRegistry;

    /**
     * DefaultControllerFactory constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->setupBindings($this->container);
    }

    /**
     * This method is overridden by sub classes to add custom bindings to the service locator.
     * @param Container $serviceLocator
     */
    protected function setupBindings(Container $serviceLocator)
    {
        
    }
    
    private function bindParameter(Controller $controller, &$invokeParameters, $methodParameter, $params)
    {
        if (isset($params[$methodParameter->name])) {
            $invokeParameters[] = $params[$methodParameter->name];
            $this->boundParameters[$methodParameter->name] = true;
        } else {
            $type = $methodParameter->getClass();
            if ($type !== null) {
                $binder = $this->modelBinderRegistry->get($type->getName());
                $instance = null;
                $typeName = $type->getName();
                if($binder->requiresInstance()) {
                    $instance = $this->container->resolve($typeName);
                }
                $invokeParameters[] = $binder->bind($controller, $typeName, $methodParameter->name, $instance);
            } else {
                $invokeParameters[] = $methodParameter->isDefaultValueAvailable() ? $methodParameter->getDefaultValue() : null;
            }
        }
    }

    private function parseDocComment($comment)
    {
        $lines = explode("\n", $comment);
        $attributes = [];
        foreach ($lines as $line) {
            if (preg_match("/@ntentan\.(?<attribute>[a-z_.]+)\s+(?<value>.+)/", $line, $matches)) {
                $attributes[$matches['attribute']] = $matches['value'];
            }
        }
        return $attributes;
    }

    private function getMethod(Controller $controller, $path)
    {
        $context = Context::getInstance();
        $reflectionClass = new \ReflectionClass($controller);
        $className = $reflectionClass->getShortName();
        
        $getMethods = function () use ($context, $reflectionClass, $controller) {
            $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            $results = [];
            foreach ($methods as $method) {
                $methodName = $method->getName();
                if (substr($methodName, 0, 2) == '__') {
                    continue;
                }
                $docComments = $this->parseDocComment($method->getDocComment());
                $keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['method'] : $methodName;
                $results[$keyName] = [
                    'name' => $method->getName(),
                    'binder' => $docComments['binder']
                        ?? $controller->getDefaultModelBinderClass()
                        ?? $context->getModelBinderRegistry()->getDefaultBinderClass(),
                    'binder_params' => $docComments['binder.params'] ?? ''
                ];
            }
            return $results;
        };
        
        /* @var $methods array */
        $methods = $context->getCache()->read("controller.{$className}.methods", $getMethods);

        if (isset($methods[$path . Input::server('REQUEST_METHOD')])) {
            return $methods[$path . Input::server('REQUEST_METHOD')];
        } elseif (isset($methods[$path])) {
            return $methods[$path];
        }

        return false;
    }

    public function createController(array &$parameters): Controller
    {
        $controller = $parameters['controller'];
        $context = Context::getInstance();

        if (class_exists($controller)) {
            $controllerInstance = $this->container->resolve($controller);
        } else {
            $controllerClassName = sprintf('\%s\controllers\%sController', $context->getNamespace(), Text::ucamelize($controller));
            $context->setParameter('controller_path', $context->getUrl($controller));
            $controllerInstance = $this->container->resolve($controllerClassName);
        }
        return $controllerInstance;
    }

    public function executeController(Controller $controller, array $parameters): string
    {
        $action = $parameters['action'];
        $methodName = Text::camelize($action);
        $invokeParameters = [];
        $methodDetails = $this->getMethod($controller, $methodName);
        $this->modelBinderRegistry = Context::getInstance()->getModelBinderRegistry();
        
        if ($methodDetails !== false) {
            $controller->setActionMethod($action ?? 'index');
            $method = new \ReflectionMethod($controller, $methodDetails['name']);
            $methodParameters = $method->getParameters();
            $this->modelBinderRegistry->setDefaultBinderClass($methodDetails['binder']);
            foreach ($methodParameters as $methodParameter) {
                $this->bindParameter($controller, $invokeParameters, $methodParameter, $parameters);
            }
            return $method->invokeArgs($controller, $invokeParameters);
        }
        throw new ControllerActionNotFoundException($this, $methodName);
    }

}
