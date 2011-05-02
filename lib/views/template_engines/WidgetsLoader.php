<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\views\template_engines;

use ntentan\Ntentan;
use ntentan\views\widgets\Widget;
use \ReflectionClass;

class WidgetsLoader
{
    //private $loadedWidgets = array();

    public function loadWidget($widget)
    {
        //if(!isset($this->loadedWidgets[$widget]))
        //{
            $widgetFile = Ntentan::$modulesPath . "/widgets/$widget/" . Ntentan::camelize($widget) . "Widget.php";
            if(file_exists($widgetFile))
            {
                require_once $widgetFile;
                $widgetClass = "\\" . Ntentan::$modulesPath . "\\widgets\\$widget\\" . Ntentan::camelize($widget) . 'Widget';
                $path = Ntentan::$modulesPath . "/widgets/$widget";
            }
            else if(file_exists(Ntentan::getFilePath("lib/views/widgets/$widget/" . Ntentan::camelize($widget) . "Widget.php")))
            {
                Ntentan::addIncludePath(Ntentan::getFilePath("lib/controllers/widgets/$widget"));
                $widgetClass = "\\ntentan\\views\\widgets\\$widget\\" . Ntentan::camelize($widget) . 'Widget';
                $path = Ntentan::getFilePath("lib/views/widgets/$widget");
            }
            else
            {
                Ntentan::error("Widget <code><b>$widget</b></code> not found");
            }
            $widgetClass = new ReflectionClass($widgetClass);
            $widgetInstance = $widgetClass->newInstance();
            $widgetInstance->filePath = $path;
            $widgetInstance->name = $widget;
            return $widgetInstance;
        //}
        //return $this->loadedWidgets[$widget];
    }

    public function cached($alias)
    {
        return Widget::cached($alias);
    }
    
    public function __call($widget, $arguments)
    {
        $widgetInstance = $this->loadWidget($widget);
        $method = new \ReflectionMethod($widgetInstance, 'init');
        $method->invokeArgs($widgetInstance, $arguments);
        return $widgetInstance;
    }

    public function __get($widget)
    {
        $widgetInstance = $this->loadWidget($widget);
        return $widgetInstance;
    }
}
