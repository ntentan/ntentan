<?php

namespace ntentan\middleware\auth;

use ntentan\Model;
use ntentan\Context;
use ntentan\Session;

abstract class AbstractAuthMethod {

    protected $message;
    private $parameters;

    abstract public function login(Context $context, $route);

    public function authLocalPassword($username, $password) {
        $users = Model::load($this->parameters->get('users_model', 'users'));
        $result = $users->filter('username = ?', $username)->fetchFirst();
        $passwordCrypt = $this->parameters->get(
            'password_crypt_function',
            function($password, $storedPassword) {
                return md5($password) == $storedPassword;
            }                
        );
        if ($passwordCrypt($password, $result->password) && $result->blocked != '1') {
            Session::set("logged_in", true);
            Session::set("username", $username);
            Session::set("user_id", $result["id"]);
            Session::set("user", $result->toArray());
            return true;
        } else {
            $this->message = "Invalid username or password!";
            return false;
        }
    }
    
    protected function getParameters() {
        return $this->parameters;
    }
    
    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

}
