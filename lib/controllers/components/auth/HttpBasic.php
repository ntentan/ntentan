<?php
class HttpBasic extends AuthMethod
{
    public function login()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) 
        {
            header('WWW-Authenticate: Basic realm="Please enter a valid username or password"');
            header('HTTP/1.0 401 Unauthorized');
        } 
        else 
        {
            echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
            echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
        }        
    }
}