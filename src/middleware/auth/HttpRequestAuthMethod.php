<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;

/**
 * An authentication method that receives a username and password through an HTTP request.
 * The parameters which should be sent through a POST request are retrieved and validated against a local auth database.
 */
class HttpRequestAuthMethod extends AbstractAuthMethod
{
    use Redirects;

    private function isExcluded($route, $excludedRoutes)
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
        $parameters = $this->getParameters();
        $usernameField = $parameters->get('username_field', "username");
        $passwordField = $parameters->get('password_field', "password");

        if (Input::exists(Input::POST, $usernameField) && Input::exists(Input::POST, $passwordField)) {
            $username = Input::post($usernameField);
            if ($this->authLocalPassword($username, Input::post($passwordField))) {
                return $this->getRedirect()->toUrl($parameters->get('success_redirect', $this->context->getUrl('/')));
            } else {
                return false;
            }
        }
        
        $excluded = array_merge($parameters->get('excluded_routes', []), [$parameters->get('login_route', 'login')]);
        if(!$this->isExcluded($route['route'], $excluded)) {
            return $this->getRedirect()->toUrl($parameters->get('login_route', '/login'));
        }
    }
}
