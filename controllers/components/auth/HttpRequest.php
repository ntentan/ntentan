<?php
namespace ntentan\controllers\components\auth;

use \ntentan\Ntentan;
use \ntentan\models\Model;

class HttpRequest extends AuthMethod
{
    public function login()
    {
        if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]))
        {
            $usersModelClass = Model::getClassName($this->auth->usersModel);
            $users = new $usersModelClass();
            $result = $users->getFirstWithUsername($_REQUEST["username"]);
            if($result->password == md5($_REQUEST["password"]))
            {
                $_SESSION["logged_in"] = true;
                $_SESSION["username"] = $_REQUEST["username"];
                $_SESSION["user_id"] = $result["id"];
                $this->auth->set("login_message", "Successful login" );
                $this->auth->set("login_status", true);
                if($this->auth->redirectOnSuccess === true) 
                {
                    Ntentan::redirect( 
                        Ntentan::getUrl($this->auth->redirectPath), 
                        true
                    );
                }
            }
            else
            {
                $this->auth->set("login_message", "Invalid username or password" );
                $this->auth->set("login_status", false);
            }
        }
        else
        {
            if(Ntentan::$route != $this->auth->loginPath && Ntentan::$route != $this->auth->logoutPath) 
            {
                Ntentan::redirect($this->auth->loginPath);
            }
        }       
    }
}
