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
    
    public function __construct(ControllerFactoryInterface $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
    }
    
    public function createMiddleware(array $parameters): AbstractMiddleware
    {
        $instance = new MvcMiddleware($this->controllerFactory);
        $instance->setParameters($parameters);
        return $instance;
    }
}
