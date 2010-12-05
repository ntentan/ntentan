<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;

abstract class TemplateEngine
{
    private $loadedHelpers = array();
    
    private function loadHelper($helper)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("lib/views/helpers/$helper"));
        $helperClass = "\\ntentan\\views\\helpers\\$helper\\" . Ntentan::camelize($helper) . "Helper";
        return new $helperClass();
    }
    
    public function __get($property)
    {
        $propertyPlural = Ntentan::plural($property);
        $property = $propertyPlural == null ? $property : $propertyPlural;
        if($property === null)
        {
            throw new \Exception("Unknown helper <b>$property</b>");
        }
        if(!isset($this->loadedHelpers[$property]))
        {
            $this->loadedHelpers[$property] = $this->loadHelper($property);
        }
        return $this->loadedHelpers[$property];
    }
    
    abstract public function out($template, $data);
}