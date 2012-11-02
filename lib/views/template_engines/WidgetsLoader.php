<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2012 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
