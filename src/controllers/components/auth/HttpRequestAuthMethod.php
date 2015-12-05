<?php
namespace ntentan\controllers\components\auth;

use ntentan\honam\TemplateEngine;
use ntentan\utils\Input;

class HttpRequestAuthMethod extends AbstractAuthMethod
{
    public function login()
    {
        TemplateEngine::appendPath(__DIR__. '/../../../../views/auth');
        if(Input::exists(Input::POST, $this->usersFields['username']) && Input::exists(Input::POST, $this->usersFields['password']))
        {
            return $this->authLocalPassword(
                Input::post($this->usersFields['username']),
                Input::post($this->usersFields['password'])
            );
        }
        else
        {
            return false;
        }
    }
}
