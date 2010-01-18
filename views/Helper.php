<?php

class Helper
{
    public function __call ($method, $arguments)
    {
        if(substr($method, 0, 6) == "create")
        {
            $class = substr($method, 6);
            $reflectionClass = new ReflectionClass($class);
            return $reflectionClass->newInstanceArgs($arguments);
        }
    }
}
