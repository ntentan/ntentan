<?php
/**
 * Source file for the view widgets loader class
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category Template Engines
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\views\template_engines;

use ntentan\Ntentan;
use ntentan\views\widgets\Widget;
use \ReflectionClass;

/**
 * A class for loading widgets in the views. The class is never directly invoked
 * in the view. The invokation is done automatically by the template engine.
 * This class loads the plugins in the following order of preference; first it
 * looks into the applications widget directory, then looks into the directories
 * of all the loaded plugins then it finally looks into the core framework library.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @todo cache the location of the widget so it is searched for just once
 */
class WidgetsLoader
{
    private $pluginMode = false;
    private $plugin;
    
    /**
     * Loads the widget.
     * 
     * @todo this method should store in the cache the location of the widget
     */
    public function loadWidget($widget)
    {
        $widgetFile = Ntentan::$modulesPath . "/widgets/$widget/" . Ntentan::camelize($widget) . "Widget.php";
        
        if(file_exists($widgetFile))
        {
            require_once $widgetFile;
            $widgetClass = "\\" . Ntentan::$namespace . "\\widgets\\$widget\\" . Ntentan::camelize($widget) . 'Widget';
            $path = Ntentan::$namespace . "/widgets/$widget";
        }
        else if($this->pluginMode)
        {
            $widgetClass = "\\ntentan\\plugins\\{$this->plugin}\\widgets\\$widget\\" . Ntentan::camelize($widget) . 'Widget';
            $path = "plugins/{$this->plugin}/widgets/$widget";
        }
        else if(file_exists(Ntentan::getFilePath("lib/views/widgets/$widget/" . Ntentan::camelize($widget) . "Widget.php")))
        {
            Ntentan::addIncludePath(Ntentan::getFilePath("lib/controllers/widgets/$widget"));
            $widgetClass = "\\ntentan\\views\\widgets\\$widget\\" . Ntentan::camelize($widget) . 'Widget';
            $path = Ntentan::getFilePath("lib/views/widgets/$widget");
        }
        else
        {
            return false;
        }
        
        $widgetClass = new ReflectionClass($widgetClass);
        $widgetInstance = $widgetClass->newInstance();
        $widgetInstance->filePath = $path;
        $widgetInstance->name = $widget;
        $widgetInstance->plugin = $this->plugin;
        
        return $widgetInstance;
    }

    /**
     * 
     */
    public function cached($alias)
    {
        return Widget::cached($alias);
    }
    
    public function __call($widget, $arguments)
    {
        $widgetInstance = $this->loadWidget($widget);
        if($widgetInstance === false)
        {
            Ntentan::error("Widget *$widget* not found");
        }
        else
        {
            $method = new \ReflectionMethod($widgetInstance, 'init');
            $method->invokeArgs($widgetInstance, $arguments);
            return $widgetInstance;
        }
    }

    public function __get($widget)
    {
        $widgetInstance = $this->loadWidget($widget);
        if($widgetInstance === false)
        {
            if($this->pluginMode === false)
            {
                $this->pluginMode = true;
                $this->plugin = $widget;
                return $this;
            }
        }
        else
        {
            return $widgetInstance;
        }
    }
}
