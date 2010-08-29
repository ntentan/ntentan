<?php
namespace ntentan\controllers\components\auth;

abstract class AuthMethod
{
    protected $auth;
    
    public function setAuthComponent($auth)
    {
        $this->auth = $auth;
    }
}