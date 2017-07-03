<?php

namespace ntentan\middleware\mvc;

use ntentan\interfaces\ControllerClassResolverInterface;
use ntentan\exceptions\ControllerNotFoundException;
use ntentan\interfaces\ResourceLoaderInterface;
use ntentan\Context;

class ControllerLoader implements ResourceLoaderInterface
{

    private $container;
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->container = $context->getContainer();
    }

    public function load($params)
    {
        $controller = $params['controller'];
        $action = isset($params['action']) ? $params['action'] : null;

        // Try to get the classname based on router parameters
        $controllerClassName = $this->container->resolve(ControllerClassResolverInterface::class)
                ->getControllerClassName($controller);

        // Try to resolve the classname 
        $resolvedControllerClass = $this->container->getResolvedClassName($controllerClassName);

        if ($resolvedControllerClass) {
            // use resolved class name
            $params['controller_path'] = $controller;
            $this->context->setParameter('controller_path', $controller);
            $controllerInstance = $this->container->resolve($controllerClassName);
        } else if (class_exists($controller)) {
            // use controller class
            $controllerInstance = $this->container->resolve($controller);
        } else {
            throw new ControllerNotFoundException("Failed to find $controllerClassName]");
        }
        return $controllerInstance->executeControllerAction($action, $params, $this->context);
    }

}
