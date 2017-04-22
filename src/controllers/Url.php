<?php

namespace ntentan\controllers;

use ntentan\config\Config;
use ntentan\Context;

/**
 * Description of Url
 *
 * @author ekow
 */
class Url
{
    private $context;
    
    private function __construct(Context $context) {
        $this->context = $context;
    }
    
    private function getActionUrl($action, $variables) {
        $url = 
        $controller = $this->context->getRouter()->getVar('controller_path');
        $queries = [];
        foreach($variables as $key => $value) {
            $queries[] = sprintf("%s=%s", urldecode($key), urlencode($value));
        }
        $path = Config::get('ntentan:app.prefix') . ($controller == "" ? "" : "/$controller");
        return preg_replace('~/+~', '/', "$path/$action" . (count($queries) ? "?" . implode('&', $queries) : ""));        
    }


    public static function action($action, $variables = []) {
        return 
    }
    
    public static function path($path)
    {
        return preg_replace('~/+~', '/', Config::get('ntentan:app.prefix') . "/$path");
    }
}
