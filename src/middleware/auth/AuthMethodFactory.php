<?php

namespace ntentan\middleware\auth;

use ntentan\Parameters;

class AuthMethodFactory
{
    private $authMethods = [
        'http_request' => HttpRequestAuthMethod::class,
        'http_basic' => HttpBasicAuthMethod::class
    ];
    
    public function createAuthMethod(Parameters $parameters) : AbstractAuthMethod
    {
        $authMethodType = $parameters->get('auth_method', 'http_request');
        if (!isset($this->authMethods[$authMethodType])) {
            throw new \Exception("Auth method $authMethodType not found");
        }        
        $class = $this->authMethods[$authMethodType];
        return new $class();
    }

    public function registerAuthMethod(string $name, string $class) : void
    {
        $this->authMethods[$name] = $class;
    }
}