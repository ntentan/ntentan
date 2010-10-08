<?php
namespace ntentan\views\blocks\menu;

use ntentan\Ntentan;
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
                $item = array(
                    "label" => $item,
                    "url" => Ntentan::getUrl(strtolower(str_replace(" ", "_",$item)))
                );
            }
            $item["selected"] = $item["url"] == Ntentan::$route;
            $this->items[] = $item;
        }
        $this->set("items", $this->items);
    }
}
