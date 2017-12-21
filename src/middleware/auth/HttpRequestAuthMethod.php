<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;
use ntentan\Context;

/**
 * An authentication method that receives a username and password through an HTTP request.
 * The parameters which should be sent through a POST request are retrieved and validated against a local auth database.
 */
class HttpRequestAuthMethod extends AbstractAuthMethod
{
    private function isExcluded($route, $excludedRoutes, $context)
    {
        foreach($excludedRoutes as $excluded) {
            if($route === $excluded) {
                return true;
            }
        }
        return false;
    }
    
    public function login($route)
    {
        $context = Context::getInstance();
        $parameters = $this->getParameters();
        $usernameField = $parameters->get('username_field', "username");
        $passwordField = $parameters->get('password_field', "password");

        if (Input::exists(Input::POST, $usernameField) && Input::exists(Input::POST, $passwordField)) {
            $username = Input::post($usernameField);
            if ($this->authLocalPassword($username, Input::post($passwordField))) {
                return $context->getRedirect($parameters->get('success_redirect', $context->getUrl('/')));
            } else {
                //$view = $context->getContainer()->resolve(View::class);
                //$view->set(['auth_message' => $this->message, 'username' => $username]);
            }
        }
        
        $excluded = array_merge($parameters->get('excluded_routes', []), [$parameters->get('login_route', '/login')]);
        if(!$this->isExcluded($route['route'], $excluded, $context)) {
            return $context->getRedirect($parameters->get('login_route', '/login'));
        }
    }
}
