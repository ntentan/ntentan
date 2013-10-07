<?php
/**
 * Source file for the menu widget
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category Widgets
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */


namespace ntentan\views\widgets\menu;

use ntentan\Ntentan;
use ntentan\views\widgets\Widget;

/**
 * Standard menu widget which ships with the menu widget.
 */
class MenuWidget extends Widget
{
    public $hasLinks = true;
    public $items = array();

    public function init($items = null)
    {
        $this->items = $items;
    }
    
    public function execute()
    {
        $menuItems = array();
        $selected = false;
        $default = false;
        $fullyMatched = false;
        
        $route = Ntentan::getUrl(Ntentan::$route);
        $requestedRoute = Ntentan::getUrl(Ntentan::$requestedRoute);
        
        foreach($this->items as $index => $item)
        {
            if(is_string($item) || is_numeric($item))
            {
                $item = array(
                    'label' => $item,
                    'url' => Ntentan::getUrl(strtolower(str_replace(' ', '_', $item)))
                );
            }
            
            
            $item['selected'] = (
                $item['url'] == substr($route, 0, strlen($item['url'])) || 
                $item['url'] == substr($requestedRoute, 0, strlen($item['url']))
            );
            
            $item['fully_matched'] = $item['url'] == $requestedRoute || $item['url'] == $route;
            
            if($item['selected'] === true) $selected = true;
            if($item['fully_matched'] === true) $fullyMatched = true;
            if($item['default'] === true) $default = $index;
            
            $menuItems[$index] = $item;
        }
        
        if(!$selected && $default !== false && !$fullyMatched !== false)
        {
            $menuItems[$default]['selected'] = true;
            $menuItems[$default]['fully_matched'] = true;
        }
        
        $this->set('items', $menuItems);
    }
    
    public function preRender()
    {
        $this->set('has_links', $this->hasLinks);
    }
}
