<?php
class HttpRequest extends AuthMethod
{
    public function login()
    {
        if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]))
        {
            $users = Model::load("users");
            $result = $users->getFirstWithUsername($_REQUEST["username"]);
            if($result->password == md5($_REQUEST["password"]))
            {
                $_SESSION["logged_in"] = true;
                $_SESSION["username"] = $_REQUEST["username"];
                $_SESSION["user_id"] = $result["id"];
                $this->auth->set("login_message", "Successful login" );
                $this->auth->set("login_status", true);
                if($this->auth->redirectOnSuccess === true) Ntentan::redirect( $this->auth->redirectPath, true);
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