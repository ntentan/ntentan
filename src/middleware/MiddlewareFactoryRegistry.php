<?php

namespace ntentan\middleware;

use ntentan\interfaces\MiddlewareFactoryInterface;

class MiddlewareFactoryRegistry
{
    private $register = [];
    
    public function register(MiddlewareFactoryInterface $middlewareFactory, string $name) : void
    {
        $this->register[$name] = $middlewareFactory;
    }
    
    public function getFactory(string $name) : MiddlewareFactoryInterface
    {
        return $this->register[$name];
    }
}
