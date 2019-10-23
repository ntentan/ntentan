<?php

namespace ntentan\middleware\mvc;

use ntentan\controllers\ModelBinderRegistry;
use ntentan\exceptions\NtentanException;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\panie\Container;
use ntentan\Context;
use ntentan\utils\Text;
use ntentan\Controller;
use ntentan\utils\Input;
use ntentan\exceptions\ControllerActionNotFoundException;
use ntentan\View;
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

    /**
     * DefaultControllerFactory constructor.
     */
    public function __construct()
    {
        $this->serviceContainer = new Container();
        $this->serviceContainer->setup([
            Context::class => function() { return Context::getInstance(); },
            Config::class => function() { return Context::getInstance()->getConfig(); }
        ]);
        $this->setupBindings($this->serviceContainer);
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

    private function getListOfMethods($controller, $className, $methods)
    {
        $context = Context::getInstance();
        $results = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (substr($methodName, 0, 2) == '__') {
                continue;
            }
            $docComments = $this->parseDocComment($method->getDocComment());
            $keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['method'] : $methodName;
            if(isset($results[$keyName]) && $method->class != $className) {
                continue;
            }
            $results[$keyName] = [
                'name' => $method->getName(),
                'binder' => $docComments['binder']
                    ?? $controller->getDefaultModelBinderClass()
                    ?? $context->getModelBinderRegistry()->getDefaultBinderClass(),
                'binder_params' => $docComments['binder.params'] ?? ''
            ];
        }
        return $results;
    }

    private function getMethod(Controller $controller, $path)
    {
        $context = Context::getInstance();
        $reflectionClass = new \ReflectionClass($controller);
        $className = $reflectionClass->getName();

        /* @var $methods array */
        $methods = $context->getCache()->read("controller.{$className}.methods",
            function() use ($controller, $reflectionClass, $className){
                $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
                return $this->getListOfMethods($controller, $className, $methods);
            }
        );

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
        if($controller == null) {
            throw new NtentanException("There is no controller specified for this request");
        }
        $context = Context::getInstance();
        $controllerClassName = sprintf('\%s\controllers\%sController', $context->getNamespace(), Text::ucamelize($controller));
        $context->setParameter('controller_path', $context->getUrl($controller));
        $controllerInstance = $this->serviceContainer->resolve($controllerClassName);
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
