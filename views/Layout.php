<?php

namespace ntentan\views;

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

    public function out($contents, $blocks = array(), $viewData = array())
    {
        $sheets = array();
        foreach($this->javaScripts as $javaScript)
        {
            $javascripts .= "<script type='text/javascript' src='$javaScript'></script>";
        }

        foreach($this->styleSheets as $styleSheet)
        {
            $sheets[$styleSheet["media"]][] = $styleSheet;
        }

        foreach($viewData as $key => $value)
        {
            $$key = $value;
        }

        foreach(array_keys($sheets) as $media)
        {
            foreach($sheets[$media] as $sheet)
            {
                if(file_exists($sheet["path"]))
                {
                    $$media .= file_get_contents($sheet["path"]);
                }
                else
                {
                    throw new FileNotFoundException("Stylesheet file <b><code>{$sheet["path"]}</code></b> not found!");
                }
            }
            $path = "public/" . $media . ".css";
            file_put_contents($path, $$media);
            $stylesheets .= "<link rel='stylesheet' type='text/css' href='/$path' media='$media'>";
        }

        // Render all the blocks into string variables
        foreach($blocks as $name => $block)
        {
            $$name = $block;
        }

        $title = $this->title;
        if(file_exists($this->layoutPath))
        {
            include $this->layoutPath;
        }
        else if($this->name === false)
        {
            echo $contents;
        }
        else
        {
            echo Ntentan::message("Layout path does not exist <code><b>{$this->layoutPath}</b></code>");
            die();
        }
    }
}
