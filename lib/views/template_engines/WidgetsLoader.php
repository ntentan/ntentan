<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\views\template_engines;

use ntentan\Ntentan;
use \ReflectionClass;

class WidgetsLoader
{
    private $loadedWidgets = array();
    
    public function __call($widget, $arguments)
    {
        if(isset($this->loadedWidgets))
        $widgetFile = Ntentan::$modulesPath . "/widgets/$widget/" . Ntentan::camelize($widgetName) . "Widget.php";
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
            $path = Ntentan::getFilePath("lib/widgets/$widget");
        }
        else
        {
            Ntentan::error("Widget <code><b>$widget</b></code> not found");
        }

        $widgetClass = new ReflectionClass($widgetClass);
        $widgetInstance = $widgetClass->newInstance($constructorArg);
        $widgetInstance->setName($widgetName);
        $widgetInstance->setFilePath($path);
        if($alias == null) $alias = $widgetName;
        $widgetInstance->setAlias($alias);
        $widgetInstance->init();
        $this->loadedWidgets[$alias] = $widgetInstance;
    }
}
