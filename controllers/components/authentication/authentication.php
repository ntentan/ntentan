<?php

class authentication extends AbstractComponent
{
    public $authPath = "users/login";
    public $name = __CLASS__;

    public function preRender()
    {
        if(!isset($_SESSION["logged_in"]) && Ntentan::$route != $this->authPath)
        {
            Ntentan::redirect($this->authPath);
        }
    }

    public function login()
    {
        
    }
}