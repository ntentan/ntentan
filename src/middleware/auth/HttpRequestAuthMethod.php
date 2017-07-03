<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;
use ntentan\Context;
use ntentan\View;

class HttpRequestAuthMethod extends AbstractAuthMethod
{
    public function login(Context $context, $route)
    {
        $parameters = $this->getParameters();
        $usernameField = $parameters->get('username_field', "username");
        $passwordField = $parameters->get('password_field', "password");
        if (Input::exists(Input::POST, $usernameField) && Input::exists(Input::POST, $passwordField)) {
            if ($this->authLocalPassword(
                Input::post($usernameField),
                Input::post($passwordField)
            )) {
                return $context->getRedirect($parameters->get('redirect_route', $context->getUrl('/')));
            } else {
                $view = $context->getContainer()->resolve(View::class);
                $view->set('auth_message', $this->message);
            }
        }
        
        if ($context->getUrl($route['route']) != $parameters->get("login_route", "login")) {
            return $context->getRedirect($parameters->get("login_route", "login"));
        }
        return true;
    }
}
