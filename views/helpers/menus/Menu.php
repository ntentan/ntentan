<?php

class Menu
{
    public $items;
    
    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public function addItem($label, $path = "#")
    {
        $this->items[] = array("label"=>$label, "path"=>$path);
    }

    public function __toString()
    {
        ob_start();
        include "menu.tpl.php";
        return ob_get_clean();
    }
}
