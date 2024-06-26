<?php
namespace ntentan\middleware\auth;

class AuthMethodFactory
{
    private $authMethods = [
        'http_request' => HttpRequestAuthMethod::class,
        'http_basic' => HttpBasicAuthMethod::class
    ];

    public function createAuthMethod(array $config) : AuthMethod
    {
        $authMethodType = $config['method'] ?? 'http_request';
        if (!isset($this->authMethods[$authMethodType])) {
            throw new \Exception("Auth method $authMethodType not found");
        }
        $instance = new ($this->authMethods[$authMethodType])();
        $instance->setup($config);
        return $instance;
    }

    public function registerAuthMethod(string $name, string $class) : void
    {
        $this->authMethods[$name] = $class;
    }
}
