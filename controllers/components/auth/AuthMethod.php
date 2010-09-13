<?php
namespace ntentan\controllers\components\auth;

use \ntentan\models\Model;

abstract class AuthMethod
{
    public $usersModel;
    public $redirectPath;
    public $message;
    
    abstract public function login();
    
    public function authLocalPassword($username, $password)
    {
        $usersModelClass = Model::getClassName($this->usersModel);
        $users = new $usersModelClass();
        $result = $users->getFirstWithUsername($username);
        if($result->password == md5($password))
        {
            $_SESSION["logged_in"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $result["id"];
            return true;
        }
        else
        {
            $this->message = "Invalid username or password!";
            return false;
        }
    }
}