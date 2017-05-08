<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;
use ntentan\Context;

class HttpBasicAuthMethod extends AbstractAuthMethod {

    public function login(Context $context, $route) {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Protected"');
            header('HTTP/1.0 401 Unauthorized');
            return "Failed to authenticate";
        } else {
            if($this->authLocalPassword(
                filter_var($_SERVER['PHP_AUTH_USER']), 
                filter_var($_SERVER['PHP_AUTH_PW'])
            )) {
                return true;
            } else {
                return false;
            }            
        }
    }

}
