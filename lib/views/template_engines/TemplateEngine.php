<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;

abstract class TemplateEngine
{
    public $template;
    private $widgetsLoader;
    private $helpersLoader;
    
    public function loadAsset($asset, $copyFrom = null)
    {
        if(file_exists(dirname("public/$asset")) && \is_writable(dirname("public/$asset")))
        {
            copy("assets/".($copyFrom==null ? $asset : $copyFrom), "public/$asset");
        }
        else
        {
            throw new \Exception("File not found or not writable <b><code>public/$asset</code></b>");
        }
        return Ntentan::getUrl("public/$asset");
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