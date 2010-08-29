<?php

namespace ntentan\views;

use \ntentan\Ntentan;

/**
 * 
 */
class Layout
{
    public $name;
    public $title;
    private $javaScripts = array();
    private $styleSheets = array();
    public $blocks = array();

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function addJavaScript($script)
    {
        $this->javaScripts[] = $script;
    }

    public function addStyleSheet($styleSheet, $media = "all")
    {
        $this->styleSheets[] = array("src"=>$styleSheet, "media"=>$media);
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

    public function out($contents)
    {
        foreach($this->javaScripts as $javaScript)
        {
            $javascripts .= "<script type='text/javascript' src='$javaScript'></script>";
        }

        foreach($this->styleSheets as $styleSheet)
        {
            $stylesheets .= "<link rel='stylesheet' type='text/css' href='{$styleSheet["src"]}' media='{$styleSheet["media"]}' >";
        }

        // Render all the blocks into string variables
        foreach($this->blocks as $alias => $block)
        {
            $blockName = $alias."_block";
            $$blockName = (string)$block;
        }

        $title = $this->title;
        $layoutPath = Ntentan::$layoutsPath . "{$this->name}.tpl.php";
        if(file_exists($layoutPath))
        {
            include $layoutPath;
        }
        else if($this->name === false)
        {
            echo $contents;
        }
        else
        {
            echo Ntentan::message("Layout path does not exist <code><b>$layoutPath</b></code>");
            die();
        }
    }
}
