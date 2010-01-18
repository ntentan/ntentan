<?php
/**
 * 
 */
class Layout
{
    public $name;
    public $title;
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

    public function addJavaScript($script)
    {
        $this->javaScripts[] = $script;
    }

    public function addStyleSheet($styleSheet, $media = "all")
    {
        $this->styleSheets[] = array("src"=>$styleSheet, "media"=>$media);
    }

    public function out($contents)
    {
        foreach($this->javaScripts as $javaScript)
        {
            $scripts .= "<script type='text/javascript' src='$javaScript'></script>";
        }

        foreach($this->styleSheets as $styleSheet)
        {
            $stylesheets .= "<link rel='stylesheet' type='text/css' href='{$styleSheet["src"]}' media='{$styleSheet["media"]}' >";
        }

        $title = $this->title;

        include Ntentan::$layoutsPath . "{$this->name}.tpl.php";
    }
}
