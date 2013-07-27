<?php
/**
 * Source file for the auth component
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category Components
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
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
    
    /**
     * 
     * @var string
     */
    const DO_NOTHING    = 'do_nothing';

    /**
     * The route through which the login method of the auth component should be
     * invoked. This path should point to a controller which exists and implements 
     * the required method.
     * 
     * @var string
     */
    public $loginRoute;
    
    /**
     * The route through wich the logout method of the auth component should be
     * invoked. This path should point to a controller which exists and
     * implements the required method. 
     * 
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
     * Function to call when login is successful. Function would only be called
     * when the AuthComponent::onSuccess property is set to call functions.
     * 
     * @var string
     */
    public $successFunction = null;
    
    /**
     * Tells the component what to do when authentication is successful.
     * 
     * @var string
     */
    public $onSuccess = AuthComponent::REDIRECT;
    public $onFailure = AuthComponent::REDIRECT;
    public $failureFunction;
    public $name = __CLASS__;
    public $authMethod = "http_request";
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
        $this->onSuccess = isset($parameters['on_success']) ? $parameters['on_success'] : $this->onFailure;
        $this->redirectRoute = isset($parameters['redirect_route']) ? $parameters['redirect_route'] : $this->redirectRoute;
        $this->failureFunction = isset($parameters['failure_function']) ? $parameters['failure_function'] : $this->failureFunction;
        $this->successFunction = isset($parameters['success_function']) ? $parameters['success_function'] : $this->successFunction;
        $this->excludedRoutes = is_array($parameters['excluded_routes']) ? $parameters['excluded_routes'] : array();
        $this->usersModel = isset($parameters['users_model']) ? $parameters['users_model'] : $this->_usersModel;
        $this->authenticated = $_SESSION['logged_in'];
    }

    public function __set($variable, $value)
    {
        switch($variable)
        {
            case "usersModel":
                $this->_usersModel = $value;
                if(is_object($this->authMethodInstance))
                {
                    $this->authMethodInstance->usersModel = $value;
                }
                break;
        }
    }

    public function init()
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
                $this->set('app_name', Ntentan::$config['application']['name']);
                $this->set('title', "Login");
                $this->login();
            }
        }
    }

    public function redirectToLogin()
    {
        $this->set("login_message", $this->authMethodInstance->message);
        $this->set("login_status", false);
        if(Ntentan::$route != $this->loginRoute && Ntentan::$requestedRoute != $this->loginRoute)
        {
            Ntentan::redirect(
                $this->loginRoute .
                (
                    Ntentan::$requestedRoute == ""
                    ? "" :
                    (Ntentan::$requestedRoute == $this->logoutRoute ? "" : "?redirect=" . urlencode(Ntentan::$requestedRoute))
                )
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
    
    public static function loggedIn()
    {
        return isset($_SESSION['user']);
    }
    
    public function getProfile()
    {
        if($_SESSION['logged_in'])
        {
            return $_SESSION["user"];
        }
        else
        {
            $this->redirectToLogin();
        }
    }
}
