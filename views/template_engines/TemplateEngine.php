<?php
namespace ntentan\views\template_engines;

use ntentan\Ntentan;

abstract class TemplateEngine
{
    private $loadedHelpers = array();
    
    private function loadHelper($helper)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("views/helpers/$helper"));
        $helperClass = "\\ntentan\\views\\helpers\\$helper\\" . ucfirst($helper);
        return new $helperClass();
    }
    
    public function __get($property)
    {
        $property = Ntentan::plural($property);
        if(!isset($this->loadedHelpers[$property]))
        {
            $this->loadedHelpers[$property] = $this->loadHelper($property);
        }
        return $this->loadedHelpers[$property];
    }
    
    abstract public function out($template, $data);
}