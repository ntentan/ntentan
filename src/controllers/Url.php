<?php

namespace ntentan\controllers;
use ntentan\config\Config;
use ntentan\Router;

/**
 * Description of Url
 *
 * @author ekow
 */
class Url
{
    public static function action($action, $variables = [])
    {
        $controller = Router::getVar('controller_path');
        return Config::get('app.prefix') . "/$controller/$action";
    }
    
    public static function path($path)
    {
        return Config::get('app.prefix') . "/$path";
    }
}
