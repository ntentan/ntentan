<?php

namespace ntentan\middleware\auth;

use ntentan\Context;
use ntentan\Parameters;
use ntentan\Redirect;

class AuthMethodFactory
{
    private $authMethods = [
        'http_request' => HttpRequestAuthMethod::class,
        'http_basic' => HttpBasicAuthMethod::class
    ];
    private $redirect;
    private $context;

    public function __construct(Redirect $redirect, Context $context)
    {
        $this->redirect = $redirect;
        $this->context = $context;
    }

    public function createAuthMethod(Parameters $parameters) : AbstractAuthMethod
    {
        $authMethodType = $parameters->get('auth_method', 'http_request');
        if (!isset($this->authMethods[$authMethodType])) {
            throw new \Exception("Auth method $authMethodType not found");
        }
        $class = $this->authMethods[$authMethodType];
        $instance = new $class();

        $instance->setContext($this->context);
        if(array_search(Redirects::class, class_uses($instance))) {
            $instance->setRedirect($this->redirect);
        }

        return $instance;
    }

    public function registerAuthMethod(string $name, string $class) : void
    {
        $this->authMethods[$name] = $class;
    }
}