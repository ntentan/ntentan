<?php

namespace ntentan\middleware;

use ntentan\Middleware;
use ntentan\exceptions\NtentanException;

class Registry
{
    private $register = [];
    
    public function register(string $class, callable $factory) : void
    {
        $this->register[$class] = $factory;
    }
    
    public function get(string $name, array $config) : Middleware
    {
        if (!isset($this->register[$name])) {
            throw new NtentanException("{$name} has not been registered as a middleware.");
        }
        return $this->register[$name]($config);
    }
}
