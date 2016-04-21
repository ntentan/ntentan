<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\controllers;

/**
 * Description of Redirect
 *
 * @author ekow
 */
class Redirect
{
    public static function action($action, $controller = null, $variables = [])
    {
        header("Location: " + Url::action($action, $controller, $variables));
    }
    
    public static function path($path)
    {
        header("Location: " + Url::path($path));
    }
}
