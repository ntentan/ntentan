<?php
namespace ntentan\middleware\auth;

use ntentan\Middleware;

interface AuthMethod extends Middleware
{
    public function setup(array $config);
}
