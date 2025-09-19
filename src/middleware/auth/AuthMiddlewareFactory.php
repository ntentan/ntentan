<?php

namespace ntentan\middleware\auth;

use ntentan\AbstractMiddleware;
use ntentan\interfaces\MiddlewareFactoryInterface;

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
    
    public function createMiddleware(array $parameters): AbstractMiddleware
    {
        $instance = new AuthMiddleware($this->authMethodFactory);
        $instance->setParameters($parameters);
        return $instance;
    }
}
