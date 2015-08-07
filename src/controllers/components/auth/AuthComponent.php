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
use ntentan\controllers\components\Component;
use ntentan\utils\Text;
use ntentan\Session;

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
    const REDIRECT = 'redirect';
    
    /**
     * 
     */
    const CALL_FUNCTION = 'call_function';
    
    /**
     * 
     * @var string
     */
    const DO_NOTHING = 'do_nothing';

    protected $parameters;
    private $authMethodInstance;
    private $authenticated;

    public function __construct($parameters = array())
    {
        parent::__construct();
        $this->parameters = $parameters;
        $this->authenticated = Session::get('logged_in');
    }
    
    private function getParameter($parameter, $default = null)
    {
        if(isset($this->parameters[$parameter]))
        {
            return $this->parameters[$parameter];
        }
        else
        {
            return $default;
        }
    }

    public function init()
    {
        foreach($this->getParameter('excluded_routes', array()) as $excludedRoute)
        {
            if(preg_match("/$excludedRoute/i", Ntentan::$route) > 0)
            {
                return;
            }
        }

        if($this->authenticated !== true)
        {
            $this->set('app_name', \ntentan\Config::get('app.name'));
            $this->set('title', "Login");
            $this->login();
        }
    }

    public function redirectToLogin()
    {
        $this->set("login_message", $this->authMethodInstance->getMessage());
        $this->set("login_status", false);
        $loginRoute = $this->getParameter('login_route', $this->controller->route . "/login");
        $route = \ntentan\Router::getRoute();
        \ntentan\logger\Logger::info("$route : $loginRoute");
        if($route !== $loginRoute)
        {
            Ntentan::redirect($loginRoute);
        }
    }
    
    private function performSuccessOperation()
    {
        $this->authenticated = true;
        switch($this->getParameter('on_success', self::REDIRECT))
        {
            case self::REDIRECT:
                Ntentan::redirect($this->getParameter('redirect_route', '/'));
                break;

            case self::CALL_FUNCTION:
                $function = $this->getParameter('success_function');
                $function();
                break;

            default:
                $this->set('login_status', true);
        }        
    }
    
    private function performFailureOperation()
    {
        switch($this->getParameter('on_failure', self::REDIRECT))
        {
            case self::CALL_FUNCTION:
                $function = $this->getParameter('failure_function');
                $function();
                break;

            case self::REDIRECT:
                $this->redirectToLogin();
                break;

            default:
                $this->set('login_status', false);
                break;
        }        
    }
    
    public function login()
    {
        $authenticatorClass = __NAMESPACE__ . '\\methods\\' . Text::ucamelize($this->getParameter('auth_method', 'http_request'));
        $this->authMethodInstance = new $authenticatorClass();
        $this->authMethodInstance->setPasswordCryptFunction(
            $this->getParameter(
                'password_crypt', 
                function($password, $storedPassword){ 
                    return md5($password) == $storedPassword; 
                }
            )
        );
        $this->authMethodInstance->setUsersModel($this->getParameter('users_model'));
        $this->authMethodInstance->setUsersModelFields($this->getParameter('users_model_fields'));
        
        if($this->loggedIn())
        {
            $this->performSuccessOperation();
        }
        else if($this->authMethodInstance->login())
        {
            $this->performSuccessOperation();
        }
        else
        {
            $this->performFailureOperation();
        }
    }

    public function logout()
    {
        Session::reset();
        Ntentan::redirect($this->getParameter('login_route', $this->controller->route . "/login"));
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
