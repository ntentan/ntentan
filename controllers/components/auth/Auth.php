<?php
namespace ntentan\controllers\components\auth;

use \ntentan\Ntentan;
use \ntentan\controllers\components\Component;

/**
 * 
 */
class Auth extends Component
{
    public $loginPath = "users/login";
    public $logoutPath = "users/logout";
    public $redirectPath = "/";
    public $redirectOnSuccess = true;
    public $name = __CLASS__;
    public $authMethod = "http_request";
    public $usersModel = "asembisa.users";
    protected $authMethodInstance;
    
    public function preRender()
    {
        // Load the authenticator
        $authenticatorClass = __NAMESPACE__ . '\\' . Ntentan::camelize($this->authMethod);
        if(class_exists($authenticatorClass))
        {
            $this->authMethodInstance = new $authenticatorClass();
            $this->authMethodInstance->setAuthComponent($this);
        }
        else
        {
            print Ntentan::message("Authenticator class <code>$authenticatorClass</code> not found.");
            die();
        }       
        
        // Allow the roles component to activate the authentication if it is
        // available. If not just run the authenticator from this section.
        if($this->controller->hasComponent("roles")) return;
        if($_SESSION["logged_in"] === false || !isset($_SESSION["logged_in"]))
        {
            $this->login();
        }
    }
    
    public function login()
    {
        $this->authMethodInstance->login();
    }

    public function logout()
    {
        $_SESSION = array();
        Ntentan::redirect($this->redirectPath);
    }
}