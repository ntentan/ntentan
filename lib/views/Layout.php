<?php
/* 
 * Ntentan PHP Framework
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
 */

namespace ntentan\views;

use ntentan\views\template_engines\Template;

use ntentan\Ntentan;
use ntentan\exceptions\FileNotFoundException;

/**
 * 
 */
class Layout
{
    public $title;
    public $layoutPath;
    private $javaScripts = array();
    private $styleSheets = array();

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
    
    public function __set($variable, $value)
    {
        switch($variable)
        {
            case "name":
                $this->layoutPath = Ntentan::$layoutsPath . "$value.tpl.php";
                break;
            case "file":
                $this->layoutPath = $value;
                break;
        }
    }

    public function addJavaScript($script)
    {
        $this->javaScripts[] = $script;
    }

    public function addStyleSheet($styleSheet, $media = "all")
    {
        if(is_array($styleSheet))
        {
            foreach($styleSheet as $sheet)
            {
                $this->styleSheets[] = array("path"=>$sheet, "media"=>$media);
            }
        }
        else
        {
            $this->styleSheets[] = array("path"=>$styleSheet, "media"=>$media);
        }
    }

    public function removeStyleSheet($styleSheet, $media = "all")
    {
        foreach($this->styleSheets as $key=>$style)
        {
            if($style["src"]==$styleSheet && $styleSheet["media"]==$media)
            {
                unset($this->styleSheets[$key]);
                break;
            }
        }
    }

    public function out($contents, $widgets = array(), $viewData = array())
    {
        $sheets = array();
        $layoutData = array();
        
        /**
         * Process all the javascripts
         */
        if(count($this->javaScripts) > 0)
        {
            foreach($this->javaScripts as $javaScript)
            {
                $javaScripts .= file_get_contents($javaScript);
            }
            file_put_contents("public/bundle.js", $javaScripts);
            $layoutData["javascripts"] =
                "<script type='text/javascript' src='"
                .   Ntentan::getUrl('public/bundle.js')
                .   "'></script>";
        }

        /**
         * Process all the stylesheets
         */
        foreach($this->styleSheets as $styleSheet)
        {
            $sheets[$styleSheet["media"]][] = $styleSheet;
        }

        foreach(array_keys($sheets) as $media)
        {
            foreach($sheets[$media] as $sheet)
            {
                if(file_exists($sheet["path"]))
                {
                    $$media .= "/** ntentan stylesheet - {$sheet["path"]} **/\n"
                    . file_get_contents($sheet["path"]);
                }
                else
                {
                    throw new FileNotFoundException("Stylesheet file <b><code>{$sheet["path"]}</code></b> not found!");
                }
            }
            $url = Ntentan::getUrl("public/" . $media . ".css");
            $path = "public/$media.css";
            file_put_contents($path, $$media);
            $layoutData["stylesheets"] .= "<link rel='stylesheet' type='text/css' href='$url' media='$media' />";
        }

        // Render all the widgets into string variables
        foreach($widgets as $name => $widget)
        {
            $layoutData[$name] = $widget;
        }

        $layoutData["title"] = $this->title;
        $layoutData["contents"] = $contents;
        $layoutData = array_merge($layoutData, $viewData);

        if(file_exists($this->layoutPath))
        {
            return Template::out($this->layoutPath, $layoutData);
        }
        else if($this->name == false)
        {
            return $contents;
        }
        else
        {
            echo Ntentan::message("Layout path does not exist <code><b>{$this->layoutPath}</b></code>");
            die();
        }
    }
}
