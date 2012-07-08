<?php
namespace ntentan\controllers\components\auth\methods;

use \ntentan\Ntentan;
use \ntentan\models\Model;
use ntentan\views\template_engines\TemplateEngine;

class HttpRequest extends AuthMethod
{
    public function login()
    {
        TemplateEngine::appendPath(Ntentan::getFilePath('lib/controllers/components/auth/views'));
        if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]))
        {
            return $this->authLocalPassword(
                $_REQUEST["username"],
                $_REQUEST["password"]
            );
        }
        else
        {
            return false;
        }
    }
}
