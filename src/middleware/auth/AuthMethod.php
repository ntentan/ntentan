<?php
namespace ntentan\middleware\auth;

use ntentan\middleware\Middleware;

interface AuthMethod extends Middleware
{
    function isAuthenticated(): bool;
}
