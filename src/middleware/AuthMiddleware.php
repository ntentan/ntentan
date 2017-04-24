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

namespace ntentan\middleware;

use ntentan\Ntentan;
use ntentan\Session;
use ntentan\Parameters;
use ntentan\utils\Input;
use ntentan\View;
use ntentan\config\Config;
use ntentan\Context;
use ntentan\middleware\auth\HttpRequestAuthMethod;

/**
 * AuthComponent provides a simplified authentication scheme
 *
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class AuthMiddleware extends \ntentan\Middleware {

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

    private $authMethodInstance;
    private $authenticated;
    private static $authMethods = [
        'http_request' => HttpRequestAuthMethod::class
    ];
    
    private $context;

    public function __construct(Context $context) {
        $this->context = $context;
        $this->authenticated = Session::get('logged_in');

        /*foreach ($this->parameters->get('excluded_routes', array()) as $excludedRoute) {
            if (preg_match("/$excludedRoute/i", Ntentan::$route) > 0) {
                return;
            }
        }*/
    }

    public function redirectToLogin() {
        View::set("login_message", $this->authMethodInstance->getMessage());
        View::set("login_status", false);
        $route = Ntentan::getRouter()->getRoute();
        $loginRoute = $this->parameters->get('login_route', 'login');

        if ($route !== $loginRoute) {
            return Redirect::path($loginRoute);
        }
    }

    private function performSuccessOperation() {
        $this->authenticated = true;
        switch ($this->parameters->get('on_success', self::REDIRECT)) {
            case self::REDIRECT:
                return Redirect::path($this->parameters->get('redirect_route', '/'));
                break;

            case self::CALL_FUNCTION:
                call_user_func($this->parameters->get('success_function'));
                break;

            default:
                View::set('login_status', true);
        }
    }

    private function performFailureOperation() {
        switch ($this->parameters->get('on_failure', self::REDIRECT)) {
            case self::CALL_FUNCTION:
                $function = $this->parameters->get('failure_function');
                $function();
                break;

            case self::REDIRECT:
                $this->redirectToLogin();
                break;

            default:
                View::set('login_status', false);
                break;
        }
    }

    public static function registerAuthMethod($authMethod, $class) {
        self::$authMethods[$authMethod] = $class;
    }

    private function getAuthMethod() {
        $authMethod = $this->getParameters()->get('auth_method', 'http_request');
        if (!isset(self::$authMethods[$authMethod])) {
            throw new \Exception("Auth method $authMethod not found");
        }
        $class = self::$authMethods[$authMethod];
        $authMethod = $this->context->getContainer()->resolve($class);
        $authMethod->setParameters($this->getParameters());
        return $authMethod;
    }

    public function login() {
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
        View::setLayout('auth_main');
        View::setTemplate('auth_login');
        View::set('login_data', [
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

    public function logout() {
        Session::reset();
        Redirect::path($this->parameters->get('login_route', "/login"));
    }

    public static function getUserId() {
        return Session::get("user_id");
    }

    public function run($route, $response) {
        if(Session::get('logged_in')) {
            return $this->next($route, $response);
        } 
        $response = $this->getAuthMethod()->login($this->context, $route);
        if($response === true) {
            return $this->next($route, $response);
        } else {
            return $response;
        }
    }

    public function getProfile() {
        if (Session::get('logged_in')) {
            return Session::get('user');
        } else {
            $this->redirectToLogin();
        }
    }

}
