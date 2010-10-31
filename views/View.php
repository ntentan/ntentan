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

use ntentan\Ntentan;

/**
 * An extension of the presentation class for the purposes of rendering views.
 * @author ekow
 */
class View extends Presentation
{
    private $_layout;
    public $template;
    public $blocks;
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
                return parent::__get($property);
        }
    }

    public function __set($property, $value)
    {
        switch($property)
        {
            case "layout":
                $this->layout->name = $value;
                break;
            case "layoutFile":
                $this->layout->file = $value;
                break;
        }
    }
    
    public function setContentType($contentType, $encoding="utf-8")
    {
    	header("Content-type: $contentType; charset=$encoding");
    }

    public function out($viewData)
    {
        // Convert all keys of the data array into variables
        if(is_array($viewData))
        {
            foreach($viewData as $key => $value)
            {
                $$key = $value;
            }
        }
        
        // Render all the blocks into string variables
        $blocks = array();
        foreach($this->blocks as $alias => $block)
        {
            $blockName = $alias."_block";
            $$blockName = (string)$block;
            $blocks[$blockName] = $$blockName;
        }
        ob_start();
        if(file_exists( $this->template ))
        {
            include $this->template;
        }
        else if(file_exists($this->defaultTemplatePath . $this->template))
        {
            include $this->defaultTemplatePath . $this->template;
        }
        else if($this->template === false)
        {
            // Do nothing
        }
        else
        {
            Ntentan::error("View template <b><code>{$this->template}</code></b> not Found!");
        }
        $data = ob_get_clean();

        if(!Ntentan::isAjax())
        {
            ob_start();
            $this->_layout->out($data, $blocks, $viewData);
            $data = ob_get_clean();
        }
        
        return $data;
    }
    
    public static function nl2br($text) {
        return str_replace("\n", "<br/>", $text);
    }

    public static function truncate($text, $size, $ending = "...") {
    	return substr($text, 0, $size) . $ending;
    }
}
