<?php
namespace ntentan\middleware\auth;

use ntentan\Context;

/**
 * A factory for creating authentication methods.
 * 
 * @author ekow
 */
class AuthMethodFactory
{
    private array $factories = [];

    public function __construct(Context $context, AuthUserModelFactory $userModelFactory) {
        $this->factories = [
            'http_request' => function() use ($context, $userModelFactory) {
                return new HttpRequestAuthMethod($context, $userModelFactory);
            },
        ];
    }

    public function create(array $config): AuthMethod
    {
        $authMethodType = $config['method'] ?? 'http_request';
        if (!isset($this->factories[$authMethodType])) {
            throw new \Exception("Auth method $authMethodType not found");
        }
        $instance = $this->factories[$authMethodType]();
        $instance->configure($config);
        return $instance;
    }

    public function registerAuthMethod(string $name, callable $class) : void
    {
        $this->factories[$name] = $class;
    }
}
