<?php
/*
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
 */
class AuthComponent extends Component
{
    /**
     *
     */
    const REDIRECT      = 'redirect';
    
    /**
     *
     */
    const CALL_FUNCTION = 'call_function';
    const DO_NOTHING    = 'do_nothing';

    /**
     * The route through which the login method of the auth component should be
     * invoked. This path should point to a controller which exists and implements 
     * the required method.
     * @var string
     */
    public $loginRoute;
    
    /**
     * The route through wich the logout method of the auth component should be
     * invoked. This path should point to a controller which exists and
     * implements the required method. 
     * @var string
     */
    public $logoutRoute;

    /**
     * Route for redirection when login is successful. Redirection is only
     * performed when the AuthComponent::onSuccess property is set for
     * redirection.
     * 
     * @var string
     */
    public $redirectRoute = "/";

    /**
     * Function to ca
     * @var <type>
     */
    public $successFunction = null;
    public $onSuccess = AuthComponent::REDIRECT;
    public $onFailure = AuthComponent::REDIRECT;
    public $failureFunction;
    public $name = __CLASS__;
    public $authMethod = "http_basic";
    private $_usersModel = "users";
    protected $authMethodInstance;
    public $excludedRoutes;
    public $authenticated;

    public function __construct($parameters = array())
    {
        $this->authMethod = isset($parameters['method']) ? $parameters['method'] : $this->authMethod;
        $this->loginRoute = isset($parameters['login_route']) ? $parameters['login_route'] : $this->loginRoute;
        $this->logoutRoute = isset($parameters['logout_route']) ? $parameters['logout_route'] : $this->logoutRoute;
        $this->onFailure = isset($parameters['on_failure']) ? $parameters['on_failure'] : $this->onFailure;
        $this->failureFunction = isset($parameters['failure_function']) ? $parameters['failure_function'] : $this->failureFunction;
        $this->excludedRoutes = is_array($parameters['excluded_routes']) ? $parameters['excluded_routes'] : array();
        $this->authenticated = $_SESSION['logged_in'];
    }

    public function __set($variable, $value)
    {
        switch($variable)
        {
            case "usersModel":
                $this->_usersModel = $value;
                $this->authMethodInstance->usersModel = $value;
                break;
        }
    }

    public function preExecute()
    {
        // Allow the roles component to activate the authentication if it is
        // available. If not just run the authenticator from this section.
        if($this->controller->hasComponent("roles"))
        {
            return;
        }
        else
        {
            foreach($this->excludedRoutes as $excludedRoute)
            {
                if(preg_match("/$excludedRoute/i", Ntentan::$route) > 0)
                {
                    return;
                }
            }
            
            if($_SESSION["logged_in"] === false || !isset($_SESSION["logged_in"]))
            {
                $this->login();
            }
        }
    }

    public function redirectToLogin()
    {
        $this->set("login_message", $this->authMethodInstance->message);
        $this->set("login_status", false);
        if(Ntentan::$route != $this->loginRoute)
        {
            Ntentan::redirect(
                Ntentan::getUrl(
                    $this->loginRoute .
                    (
                        Ntentan::$requestedRoute == ""
                        ? "" :
                        (Ntentan::$requestedRoute == $this->logoutRoute ? "" : "?redirect=" . urlencode(Ntentan::$requestedRoute))
                    )
                ),
                true
            );
        }
    }
    
    public function login()
    {
        Ntentan::addIncludePath(Ntentan::getFilePath('lib/controllers/components/auth/methods'));
        $authenticatorClass = __NAMESPACE__ . '\\methods\\' . Ntentan::camelize($this->authMethod);
        if(class_exists($authenticatorClass))
        {
            $this->authMethodInstance = new $authenticatorClass();
            $this->authMethodInstance->usersModel = $this->_usersModel;
        }
        else
        {
            print Ntentan::message("Authenticator class <code>$authenticatorClass</code> not found.");
        }
        
        if($this->authMethodInstance->login())
        {
            $this->authenticated = true;
            switch($this->onSuccess)
            {
                case AuthComponent::REDIRECT:
                    Ntentan::redirect($this->redirectRoute);
                    break;

                case AuthComponent::CALL_FUNCTION:
                    $decomposed = explode("::", $this->successFunction);
                    $className = $decomposed[0];
                    $methodName = $decomposed[1];
                    $method = new \ReflectionMethod($className, $methodName);
                    $method->invoke(null, $this->controller);
                    break;
                
                default:
                    $this->set('login_status', true);
            }
        }
        else
        {
            switch($this->onFailure)
            {
                case AuthComponent::CALL_FUNCTION:
                    $decomposed = explode("::", $this->failureFunction);
                    $className = $decomposed[0];
                    $methodName = $decomposed[1];
                    $method = new \ReflectionMethod($className, $methodName);
                    $method->invoke(null, $this->controller);
                    break;

                case AuthComponent::REDIRECT:
                    $this->loginRoute = $this->loginRoute == null ? $this->controller->route . "/login" : $this->loginRoute;
                    $this->logoutRoute = $this->logoutRoute == null ? $this->controller->route . "/logout" : $this->logoutRoute;
                    $this->redirectToLogin();
                    break;

                default:
                    $this->set('login_status', false);
                    break;
            }
        }
    }

    public function logout()
    {
        $_SESSION = array();
        Ntentan::redirect($this->loginRoute);
    }

    public static function userId()
    {
        return $_SESSION["user_id"];
    }
    
    public static function getProfile()
    {
        return $_SESSION["user"];
    }
}
