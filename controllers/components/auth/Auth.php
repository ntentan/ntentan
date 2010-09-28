<?php
/**
 * The authentication component base class's script file.
 *
 * LICENSE:
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    ntentan.controllers.components
 * @author     James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @copyright  2010 James Ekow Abaka Ainooson
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */

namespace ntentan\controllers\components\auth;

use ntentan\Ntentan;
use \ntentan\controllers\components\Component;

/**
 * The class for the authentication component. This class provides an entry point
 * through which the various authentication methods could be used. It also acts
 * as a watch dog of some sort which prevents un authenticated users from 
 * acessing sections of the site they are not entitled to. Finally this class
 * provides a role mechanism so that the various users of the system can be limited
 * to what they have access to based on thei attached role.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @package ntentan.controllers.components.auth
 */
class Auth extends Component
{
    /**
     * The URL route through wich the login component should be invoked. This 
     * path should point to a controller which exists and implements the required
     * method.
     * @var string
     */
    public $loginPath = "users/login";
    
    
    public $logoutPath = "users/logout";
    public $redirectPath = "/";
    public $redirectOnSuccess = true;
    public $name = __CLASS__;
    public $authMethod = "http_request";
    public $usersModel = "users";
    protected $authMethodInstance;
    
    public function preRender()
    {
        // Load the authenticator
        $authenticatorClass = __NAMESPACE__ . '\\' . Ntentan::camelize($this->authMethod);
        if(class_exists($authenticatorClass))
        {
            $this->authMethodInstance = new $authenticatorClass();
            $this->authMethodInstance->usersModel = $this->usersModel;
        }
        else
        {
            print Ntentan::message("Authenticator class <code>$authenticatorClass</code> not found.");
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
        if($this->authMethodInstance->login())
        {
            if($this->redirectOnSuccess)
            {
                Ntentan::redirect($this->redirectPath);
            }
            else
            {
                $this->set("login_status", true);
            }
        }
        else
        {
            $this->set("login_message", $this->authMethodInstance->message);
            $this->set("login_status", false);
            if(Ntentan::$route != $this->loginPath)
            {
                Ntentan::redirect(Ntentan::getUrl($this->loginPath));
            }
        }
    }

    public function logout()
    {
        $_SESSION = array();
        Ntentan::redirect($this->loginPath);
    }
    
    public static function userId()
    {
        return $_SESSION["user_id"];
    }
}