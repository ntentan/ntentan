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
        $queries = [];
        foreach($variables as $key => $value) {
            $queries[] = sprintf("%s=%s", urldecode($key), urlencode($value));
        }
        $path = Config::get('app.prefix') . ($controller == "" ? "" : "/$controller");
        return "$path/$action" . (count($queries) ? "?" . implode('&', $queries) : "");
    }
    
    public static function path($path)
    {
        return Config::get('ntentan:app.prefix') . ($path[0] == '/' ? '' : '/') .  "$path";
    }
}
