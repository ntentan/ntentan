<?php

namespace ntentan\middleware\auth;

use ntentan\Model;
use ntentan\Session;

abstract class AbstractAuthMethod
{
    protected $message;
    private $parameters;

    abstract public function login($route);

    public function authLocalPassword($username, $password)
    {
        $users = Model::load($this->parameters->get('users_model', 'users'));
        $usernameField = $this->parameters->get('username_field', "username");
        $passwordField = $this->parameters->get('password_field', "password");        
        $result = $users->filter("$usernameField = ?", $username)->fetchFirst();
        $passwordCrypt = $this->parameters->get(
            'password_crypt_function',
            function ($password, $storedPassword) {
                return password_verify($password, $storedPassword);
            }
        );
        if ($passwordCrypt($password, $result->{$passwordField}) && $result->blocked != '1') {
            Session::set("logged_in", true);
            Session::set("username", $username);
            Session::set("user_id", $result["id"]);
            Session::set("user", $result->toArray());
            return true;
        } else {
            $this->message = $this->parameters->get('error_message', "Invalid username or password!");
            return false;
        }
    }
    
    protected function getParameters()
    {
        return $this->parameters;
    }
    
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
}
