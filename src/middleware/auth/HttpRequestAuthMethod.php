<?php

namespace ntentan\middleware\auth;

use ntentan\honam\TemplateEngine;
use ntentan\utils\Input;
use ntentan\Context;

class HttpRequestAuthMethod extends AbstractAuthMethod {

    public function login(Context $context, $route) {
        TemplateEngine::appendPath(__DIR__ . '/../../../../views/auth');
        if (Input::exists(Input::POST, $this->usersFields['username']) && Input::exists(Input::POST, $this->usersFields['password'])) {
            if($this->authLocalPassword(
                Input::post($this->usersFields['username']), 
                Input::post($this->usersFields['password'])
            )) {
                return true;
            };
        } 
        return $context->getRedirect($this->getParameters()->get("login_dir", "login"));
    }

}
