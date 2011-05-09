<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;

abstract class TemplateEngine
{
    public $template;
    private $widgetsLoader;
    private $helpersLoader;

    public static function loadAsset($asset, $copyFrom = null)
    {

        $assetPath = "assets/".($copyFrom==null ? $asset : $copyFrom);
        if(file_exists($assetPath) && file_exists(dirname("public/$asset")) && is_writable(dirname("public/$asset")))
        {
            copy($assetPath, "public/$asset");
        }
        else if(file_exists(Ntentan::getFilePath("assets/$asset")) && file_exists(dirname("public/$asset")) && is_writable(dirname("public/$asset")))
        {
            copy(Ntentan::getFilePath("assets/$asset"), "public/$asset");
        }
        else if(file_exists($copyFrom) && is_writable(dirname("public/$asset")))
        {
            copy($copyFrom, "public/$asset");
        }
        else
        {
            Ntentan::error("File not found or not writable <b><code>public/$asset</code></b>");
            die();
        }
        return "public/$asset";
    }

    public function __get($property)
    {
        switch($property)
        {
            case "widgets":
                if($this->widgetsLoader == null)
                {
                    $this->widgetsLoader = new WidgetsLoader();
                }
                return $this->widgetsLoader;


            case "helpers":
                if($this->helpersLoader == null)
                {
                    $this->helpersLoader = new HelpersLoader();
                }
                return $this->helpersLoader;
        }
    }

    abstract public function out($data, $view);
}