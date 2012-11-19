<?php
namespace ntentan\controllers\components\auth\methods;

use \ntentan\models\Model;

abstract class AuthMethod
{
    public $usersModel = 'users';
    public $redirectPath;
    public $message;
    
    abstract public function login();
    
    public function authLocalPassword($username, $password)
    {
        $usersModelClass = Model::getClassName($this->usersModel);
        $users = new $usersModelClass();
        $result = $users->getJustFirstWithUsername($username);

        if($result->password == md5($password))
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
}