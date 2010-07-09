<?php
abstract class AuthMethod
{
    protected $auth;
    
    public function setAuthComponent($auth)
    {
        $this->auth = $auth;
    }
}