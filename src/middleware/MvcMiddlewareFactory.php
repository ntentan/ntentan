<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\middleware;

use ntentan\interfaces\MiddlewareFactoryInterface;
use ntentan\AbstractMiddleware;
use ntentan\interfaces\ControllerFactoryInterface;
use ntentan\honam\Templates;

/**
 * Description of DefaultMiddlewareFactory
 *
 * @author ekow
 */
class MvcMiddlewareFactory implements MiddlewareFactoryInterface
{
    /**
     *
     * @var string
     */
    private $controllerFactory;
    private $templates;
    
    public function __construct(ControllerFactoryInterface $controllerFactory, Templates $templates)
    {
        $this->controllerFactory = $controllerFactory;
        $this->templates = $templates;
    }
    
    public function createMiddleware(array $parameters): AbstractMiddleware
    {
        $instance = new MvcMiddleware($this->controllerFactory, $this->templates);
        $instance->setParameters($parameters);
        return $instance;
    }
}
