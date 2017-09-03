<?php

namespace ntentan\middleware\mvc;

use ntentan\interfaces\ControllerClassResolverInterface;
use ntentan\exceptions\ControllerNotFoundException;
use ntentan\interfaces\ResourceLoaderInterface;
use ntentan\Context;
use ntentan\panie\Container;
use ntentan\utils\Text;

class ControllerLoader implements ResourceLoaderInterface
{
    private $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function load($params)
    {
        $controller = $params['controller'];
        $action = isset($params['action']) ? $params['action'] : null;
        $context = Context::getInstance();

        if(class_exists($controller)) {
            $controllerInstance = $this->container->resolve($controller);
        } else {
            $controllerClassName = sprintf('\%s\controllers\%sController', $context->getNamespace(), Text::ucamelize($controller));
            $context->setParameter('controller_path', $context->getUrl($controller));
            $controllerInstance = $this->container->resolve($controllerClassName);
        }
        return $controllerInstance->executeControllerAction($action, $params);
    }
}
