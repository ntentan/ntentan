<?php

class authentication extends AbstractComponent
{
    public $authPath = "users/login";
    public $successUrl = "";
    public $name = __CLASS__;

    public function preRender()
    {
        if($_SESSION["ntentan_logged_in"] == false && Ntentan::$route != $this->authPath)
        {
            var_dump($_SESSION["ntentan_logged_in"], Ntentan::$route);
            Ntentan::redirect($this->authPath);
        }
    }

    public function login()
    {
        if(isset($_POST["username"]) && isset($_POST["password"]))
        {
            $users = Model::load("users");
            $result = $users->getWithUsername($_POST["username"]);
            if($result["password"] == md5($_POST["password"]))
            {
                $_SESSION["ntentan_logged_in"] = true;
                Ntentan::redirect("");
            }
        }
    }
}