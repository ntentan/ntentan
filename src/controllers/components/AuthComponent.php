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

namespace ntentan\controllers\components;

use ntentan\Ntentan;
use ntentan\controllers\Component;
use ntentan\Session;
use ntentan\Router;
use ntentan\Parameters;
use ntentan\utils\Input;

/**
 * The class for the authentication component.
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
    private static $authMethods = [
        'http_request' => '\ntentan\controllers\components\auth\HttpRequestAuthMethod',
        'http_digest' => '\ntentan\controllers\components\auth\HttpDigestAuthMethod',
        'http_basic' => '\ntentan\controllers\components\auth\HttpBasicAuthMethod',
    ];

    public function __construct($parameters = array())
    {
        parent::__construct();
        $this->parameters = Parameters::wrap($parameters);
        $this->authenticated = Session::get('logged_in');
    }

    public function init()
    {
        foreach ($this->parameters->get('excluded_routes', array()) as $excludedRoute) {
            if (preg_match("/$excludedRoute/i", Ntentan::$route) > 0) {
                return;
            }
        }
        
        if ($this->authenticated !== true) {
            $this->set('app_name', \ntentan\Config::get('app.name'));
            $this->set('title', "Login");
            $this->login();
        }
    }

    public function redirectToLogin()
    {
        $this->set("login_message", $this->authMethodInstance->getMessage());
        $this->set("login_status", false);
        $route = Router::getRoute();

        if ($route !== $this->parameters['login_route']) {
            Ntentan::redirect($this->parameters['login_route']);
        }
    }

    private function performSuccessOperation()
    {
        $this->authenticated = true;
        switch ($this->parameters->get('on_success', self::REDIRECT)) {
            case self::REDIRECT:
                Ntentan::redirect($this->parameters->get('redirect_route', '/'));
                break;

            case self::CALL_FUNCTION:
                $function = $this->parameters->get('success_function');
                $function();
                break;

            default:
                $this->set('login_status', true);
        }
    }

    private function performFailureOperation()
    {
        switch ($this->parameters->get('on_failure', self::REDIRECT)) {
            case self::CALL_FUNCTION:
                $function = $this->parameters->get('failure_function');
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
    
    public static function registerAuthMethod($authMethod, $class) 
    {
        self::$authMethods[$authMethod] = $class;
    }
    
    private function getAuthMethod()
    {
        $authMethod = $this->parameters->get('auth_method', 'http_request');
        if(!isset(self::$authMethods[$authMethod])) {
            throw new \Exception("Auth method $authMethod not found");
        }
        $class = self::$authMethods[$authMethod];
        return new $class();
    }

    public function login()
    {
        $this->authMethodInstance = $this->getAuthMethod();
        $this->authMethodInstance->setPasswordCryptFunction(
            $this->parameters->get(
                'password_crypt', 
                function($password, $storedPassword) {
                    return md5($password) == $storedPassword;
                }
            )
        );
        $this->authMethodInstance->setUsersModel($this->parameters->get('users_model'));
        $userModelFields = $this->parameters->get('users_model_fields');
        $this->authMethodInstance->setUsersModelFields($userModelFields);
        $this->set('login_data',
            [
                $userModelFields['username'] => Input::post($userModelFields['username']), 
                $userModelFields['password'] => Input::post($userModelFields['password'])
            ]
        );

        if ($this->loggedIn()) {
            $this->performSuccessOperation();
        } else if ($this->authMethodInstance->login()) {  
            Session::set('logged_in', true);
            $this->performSuccessOperation();
        } else {            
            $this->performFailureOperation();
        }
    }

    public function logout()
    {
        Session::reset();
        Ntentan::redirect($this->parameters->get('login_route', "/login"));
    }

    public static function getUserId()
    {
        return Session::get("user_id");
    }

    public static function loggedIn()
    {
        return isset(Session::get('user')['id']);
    }

    public function getProfile()
    {
        if (Session::get('logged_in')) {
            return Session::get('user');
        } else {
            $this->redirectToLogin();
        }
    }
}
