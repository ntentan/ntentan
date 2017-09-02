<?php

namespace ntentan\middleware\auth;

class AuthMethodFactory
{
    private $authMethods = [
        'http_request' => HttpRequestAuthMethod::class,
        'http_basic' => HttpBasicAuthMethod::class
    ];
    
    public function createAuthMethod($parameters)
    {
        $authMethodType = $parameters->get('auth_method', 'http_request');
        if (!isset($this->authMethods[$authMethodType])) {
            throw new \Exception("Auth method $authMethodType not found");
        }        
        $class = $this->authMethods[$authMethodType];
        return new $class();
    }
}