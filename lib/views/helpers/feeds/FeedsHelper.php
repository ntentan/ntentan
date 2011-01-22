<?php
namespace ntentan\views\helpers\feeds;

use ntentan\Ntentan;
use ntentan\views\helpers\Helper;

class FeedsHelper extends Helper
{
    private $properties;
    private $items = array();

    public function __set($property, $value)
    {
        $this->properties[$property] = $value;
    }

    public function item($argument)
    {
        if(is_array($argument))
        {
            $this->items[] = $argument;
        }
        else if(is_numeric($argument))
        {
            return $this->items[$argument];
        }
    }

    public function generate($format)
    {
        $generatorClassName = Ntentan::camelize($format) . "Feed";
        require "generators/$format/" . $generatorClassName . ".php";
        $generatorClass = "\\ntentan\\views\\helpers\\feeds\\generators\\$format\\$generatorClassName";
        $generator = new $generatorClass();
        $generator->setup($this->properties, $this->items);
        return $generator->execute();
    }
    
    public function __get($property)
    {
        switch($property)
        {
            case 'rss':
            case 'atom':
                $this->generate($property);
                break;
            default:
                return $this->properties[$property];
        }
    }
}
