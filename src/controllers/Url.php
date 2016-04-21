<?php

namespace ntentan\controllers;
use ntentan\Config;
use ntentan\Router;

/**
 * Description of Url
 *
 * @author ekow
 */
class Url
{
    public static function action($action, $controller = null, $variables = [])
    {
        return Config::get('app.prefix') . "/$controller/$action";
    }
    
    public static function path($path)
    {
        return Config::get('app.prefix') . "/$path";
    }
}
