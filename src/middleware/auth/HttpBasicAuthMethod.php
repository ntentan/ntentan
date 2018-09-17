<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;
use ntentan\Context;

class HttpBasicAuthMethod extends AbstractAuthMethod
{
    public function login($route)
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Protected"');
            header('HTTP/1.0 401 Unauthorized');
            return "Failed to authenticate";
        } else {
            if ($this->authLocalPassword(filter_var($_SERVER['PHP_AUTH_USER']), filter_var($_SERVER['PHP_AUTH_PW']))) {
                return $context->getRedirect($parameters->get('success_redirect', $context->getUrl('/')));
            } else {
                return "Failed to authenticate";
            }
        }
    }
}
