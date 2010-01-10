<?php

class authentication extends AbstractComponent
{
    public $authPath = "users/login";
    public $successUrl = "";
    public $name = __CLASS__;

    public function preRender()
    {
        if(!isset($_SESSION["ntentan_logged_in"]) && Ntentan::$route != $this->authPath)
        {
            Ntentan::redirect($this->authPath);
        }
    }

    public function login()
    {
        if(isset($_POST["username"]) && isset($_POST["password"]))
        {
            $users = Model::load("users");
            $users->get();
        }
    }
}