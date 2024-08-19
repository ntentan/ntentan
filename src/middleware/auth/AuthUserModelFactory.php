<?php

namespace ntentan\middleware\auth;

use ntentan\exceptions\NtentanException;
use Psr\Container\ContainerInterface;

class AuthUserModelFactory
{
    private ContainerInterface $container;
    private string $modelClass;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    public function create(): AuthUserModel
    {
        if (!isset($this->modelClass)) {
            throw new NtentanException("A user model class name must be set");
        }
        return $this->container->get($this->modelClass);
    }
}