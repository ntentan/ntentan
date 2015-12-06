<?php
namespace ntentan\controllers\components\auth;

class HttpBasicAuthMethod extends AbstractAuthMethod
{
    public function login()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) 
        {
            header('WWW-Authenticate: Basic realm="Login"');
            header('HTTP/1.0 401 Unauthorized');
        } 
        else 
        {
            if($this->authLocalPassword(
                $_SERVER['PHP_AUTH_USER'],
                $_SERVER['PHP_AUTH_PW']
            ))
            {
                return true;
            }
            else
            {
                header('WWW-Authenticate: Basic realm="Invalid username or password please provide valid credentials"');
                header('HTTP/1.0 401 Unauthorized');
                return false;
            }
        }        
    }
}