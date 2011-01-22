<?php
/*
 * Copyright 2010 James Ekow Abaka Ainooson
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
 *
 */

namespace ntentan\views;

use ntentan\views\template_engines\Template;

use ntentan\Ntentan;

/**
 * An extension of the presentation class for the purposes of rendering views.
 * @author ekow
 */
class View extends Presentation
{
    private $_layout;
    public $template;
    public $widgets;
    public $defaultTemplatePath;

    public function __construct()
    {
        $this->_layout = new Layout();
        $this->setContentType("text/html");
    }
    
    public function __get($property)
    {
        switch($property)
        {
            case "layout":
                return $this->_layout;
                break;
            default:
                throw new \Exception("Parameter $property not found in view class");
                return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
            case "layout":
                $this->_layout->name = $value;
                break;
            case "layoutFile":
                $this->_layout->file = $value;
                break;
        }
    }
    
    public function setContentType($contentType, $encoding="ISO-8859-1")
    {
    	header("Content-type: $contentType;charset=$encoding");
    }

    public function out($viewData)
    {
        // Render all the blocks into string variables
        $widgets = array();
        foreach($this->widgets as $alias => $widget)
        {
            $widgetName = $alias."_widget";
            $viewData[$widgetName] = (string)$widget;
            $widgets[$blockName] = $viewData[$widgetName];
        }
        
        //ob_start();
        if(file_exists( $this->template ))
        {
            $data = Template::out($this->template, $viewData);
        }
        else if(file_exists($this->defaultTemplatePath . $this->template))
        {
            $data = Template::out($this->defaultTemplatePath . $this->template, $viewData);
        }
        else if($this->template === false)
        {
            // Do nothing
        }
        else
        {
            Ntentan::error("View template <b><code>{$this->template}</code></b> not Found!");
        }

        if(!Ntentan::isAjax())
        {
            $data = $this->_layout->out($data, $widgets, $viewData);
        }
        return $data;
    }
}
