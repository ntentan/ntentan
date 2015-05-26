<?php
namespace ntentan\controllers\components\auth\methods;

use \ntentan\Ntentan;
use ntentan\honam\TemplateEngine;

class HttpRequest extends AuthMethod
{
    public function login()
    {
        TemplateEngine::appendPath(Ntentan::getFilePath('lib/controllers/components/auth/views'));
        if(isset($_REQUEST[$this->usersFields['username']]) && isset($_REQUEST[$this->usersFields['password']]))
        {
            return $this->authLocalPassword(
                $_REQUEST[$this->usersFields['username']],
                $_REQUEST[$this->usersFields['password']]
            );
        }
        else
        {
            return false;
        }
    }
}
