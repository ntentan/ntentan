<?php
namespace ntentan\controllers\components\auth\methods;

use \ntentan\Model;

abstract class AuthMethod
{
    private $usersModel = 'users';
    protected $usersFields = array(
        'username' => 'username',
        'password' => 'password'
    );
    private $message;
    private $passwordCrypt;
    
    abstract public function login();
    
    public function authLocalPassword($username, $password)
    {
        $users = Model::load($this->usersModel);
        $result = $users->filter('username = ?', $username)->fetchFirst();
        $passwordCrypt = $this->passwordCrypt;
        if($passwordCrypt($password, $result->password) && $result->blocked != '1')
        {
            $_SESSION["logged_in"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $result["id"];
            $_SESSION["user"] = $result->toArray();
            return true;
        }
        else
        {
            $this->message = "Invalid username or password!";
            return false;
        }
    }
    
    public function setUsersModel($usersModel)
    {
        if(!$usersModel == null)
        {
            $this->usersModel = $usersModel;
        }
    }
    
    public function setUsersModelFields($fields)
    {
        if(!$fields == null)
        {
            $this->usersFields = $fields;
        }
    }
    
    public function setPasswordCryptFunction($passwordCrypt)
    {
        $this->passwordCrypt = $passwordCrypt;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
}
