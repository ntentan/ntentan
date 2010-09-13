<?php
namespace ntentan\views\blocks\menu;

use ntentan\views\blocks\Block;

class Menu extends Block {
    
    protected $items = array();
    public $hasLinks = true;
    
    public function addItem($item)
    {
        $items = func_get_args();
        foreach($items as $item)
        {
            if(is_string($item) || is_numeric($item))
            {
                $this->items[] = array("label" => $item);
            }
            else if(is_array($item))
            {
                $this->items[] = $item;
            }
        }
        $this->set("items", $this->items);
    }
}
