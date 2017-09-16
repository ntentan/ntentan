<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\middleware;

use ntentan\interfaces\MiddlewareFactoryInterface;
use ntentan\AbstractMiddleware;
use ntentan\middleware\auth\AuthMethodFactory;

/**
 * Description of AuthMiddlewareFactory
 *
 * @author ekow
 */
class AuthMiddlewareFactory implements MiddlewareFactoryInterface
{
    private $authMethodFactory;
    
    public function __construct(AuthMethodFactory $authMethodFactory)
    {
        $this->authMethodFactory = $authMethodFactory;
    }
    
    //put your code here
    public function createMiddleware(array $parameters): AbstractMiddleware
    {
        $instance = new AuthMiddleware($this->authMethodFactory);
        $instance->setParameters($parameters);
        return $instance;
    }
}
