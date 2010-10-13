<?php
namespace ntentan\controllers\components\auth;

use \ntentan\Ntentan;
use \ntentan\models\Model;

class HttpRequest extends AuthMethod
{
    public function login()
    {
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
