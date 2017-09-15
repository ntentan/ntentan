<?php

namespace ntentan\middleware;

use ntentan\utils\Input;
use ntentan\honam\TemplateEngine;
use ntentan\honam\Helper;
use ntentan\Context;
use ntentan\AbstractMiddleware;
use ntentan\interfaces\ControllerFactoryInterface;

/**
 * 
 */
class MvcMiddleware extends AbstractMiddleware
{
    /**
     *
     * @var ControllerFactoryInterface
     */
    private $controllerFactory;

    public function __construct(ControllerFactoryInterface $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
    }
    
    private function extractRouteParameters($route)
    {
        $parameters = $route['parameters'];
        $routeDescription = $route['description'];
        foreach ($routeDescription['parameters']['default'] as $parameter => $value) {
            // Only set the controller on default route, if no route is presented to the router.
            if ($routeDescription['name'] == 'default' && $route['route'] != '' && $parameter == 'controller') {
                continue;
            }
            if (!isset($parameters[$parameter])) {
                $parameters[$parameter] = $value;
            } elseif ($parameters[$parameter] === '') {
                $parameters[$parameter] = $value;
            }
        }        
        $parameters += Input::get() + Input::post();
        return $parameters;
    }

    public function run($route, $response)
    {
        TemplateEngine::prependPath('views/shared');
        TemplateEngine::prependPath('views/layouts');
        Helper::setBaseUrl(Context::getInstance()->getUrl(''));
        $parameters = $this->extractRouteParameters($route);
        $controller = $this->controllerFactory->createController($parameters);
        return $this->controllerFactory->executeController($controller, $parameters);        
    }
    
    /**
     *
     * @param array $invokeParameters
     * @param \ReflectionParameter $methodParameter
     * @param array $params
     */
    private function bindParameter(&$invokeParameters, $methodParameter, $params)
    {
        if (isset($params[$methodParameter->name])) {
            $invokeParameters[] = $params[$methodParameter->name];
            $this->boundParameters[$methodParameter->name] = true;
        } else {
            $type = $methodParameter->getClass();
            if ($type !== null) {
                $binder = Context::getInstance()->getModelBinderRegister()->get($type->getName());
                $invokeParameters[] = $binder->bind($this, $this->activeAction, $type->getName(), $methodParameter->name);
                $this->boundParameters[$methodParameter->name] = $binder->getBound();
            } else {
                $invokeParameters[] = $methodParameter->isDefaultValueAvailable() ?
                    $methodParameter->getDefaultValue() : null;
            }
        }
    }

    protected function isBound($parameter)
    {
        return $this->boundParameters[$parameter];
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

    private function getMethod($path)
    {
        $context = Context::getInstance();
        $className = (new ReflectionClass($this))->getShortName();
        $methods = $context->getCache()->read(
            "controller.{$className}.methods", function () use ($context) {
            $class = new ReflectionClass($this);
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            $results = [];
            foreach ($methods as $method) {
                $methodName = $method->getName();
                if (substr($methodName, 0, 2) == '__') {
                    continue;
                }
                if (array_search($methodName, ['getActiveControllerAction', 'executeControllerAction'])) {
                    continue;
                }
                $docComments = $this->parseDocComment($method->getDocComment());
                $keyName = isset($docComments['action']) ? $docComments['action'] . $docComments['method'] : $methodName;
                $results[$keyName] = [
                    'name' => $method->getName(),
                    'binder' => $docComments['binder'] ?? $context->getModelBinderRegister()->getDefaultBinderClass(),
                    'binder_params' => $docComments['binder.params'] ?? ''
                ];
            }
            return $results;
        }
        );

        if (isset($methods[$path . utils\Input::server('REQUEST_METHOD')])) {
            return $methods[$path . utils\Input::server('REQUEST_METHOD')];
        } elseif (isset($methods[$path])) {
            return $methods[$path];
        }

        return false;
    }

    public function executeControllerAction($action, $params)
    {
        $context = Context::getInstance();
        $action = $action ?? 'index';
        $methodName = Text::camelize($action);
        $return = null;
        $invokeParameters = [];

        if ($methodDetails = $this->getMethod($methodName)) {
            $this->activeAction = $action;
            /*$container = $context->getContainer();
            $container->bind(controllers\ModelBinderInterface::class)
                ->to($methodDetails['binder']);*/
            $method = new \ReflectionMethod($this, $methodDetails['name']);
            $methodParameters = $method->getParameters();
            foreach ($methodParameters as $methodParameter) {
                $this->bindParameter($invokeParameters, $methodParameter, $params);
            }

            return $method->invokeArgs($this, $invokeParameters);
        }
        throw new exceptions\ControllerActionNotFoundException($this, $methodName);
    }
    
}
