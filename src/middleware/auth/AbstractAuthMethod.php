<?php

namespace ntentan\middleware\auth;

use ntentan\Model;
use ntentan\Context;

abstract class AbstractAuthMethod {

    private $usersModel = 'users';
    protected $usersFields = array(
        'username' => 'username',
        'password' => 'password'
    );
    private $message;
    private $passwordCrypt;
    private $parameters;

    abstract public function login(Context $context, $route);

    public function authLocalPassword($username, $password) {
        $users = Model::load($this->usersModel);
        $result = $users->filter('username = ?', $username)->fetchFirst();
        $passwordCrypt = $this->passwordCrypt;
        if ($passwordCrypt($password, $result->password) && $result->blocked != '1') {
            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $result["id"];
            $_SESSION["user"] = $result->toArray();
            return true;
        } else {
            $this->message = "Invalid username or password!";
            return false;
        }
    }

    public function setUsersModel($usersModel) {
        if (!$usersModel == null) {
            $this->usersModel = $usersModel;
        }
    }

    public function setUsersModelFields($fields) {
        if (!$fields == null) {
            $this->usersFields = $fields;
        }
    }

    public function setPasswordCryptFunction($passwordCrypt) {
        $this->passwordCrypt = $passwordCrypt;
    }

    protected function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }
    
    protected function getParameters() {
        
    }

}
