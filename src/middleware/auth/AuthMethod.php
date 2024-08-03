<?php
namespace ntentan\middleware\auth;

use ntentan\Middleware;

interface AuthMethod extends Middleware
{
    function isAuthenticated(): bool;
}
