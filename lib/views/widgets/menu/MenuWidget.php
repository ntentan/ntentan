<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\views\widgets\menu;

use ntentan\Ntentan;
use ntentan\views\widgets\Widget;

/**
 * Standard menu widget which ships with the menu widget.
 */
class MenuWidget extends Widget {
    
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
                    'label' => $item,
                    'url' => Ntentan::getUrl(strtolower(str_replace(' ', '_', $item)))
                );
            }
            $item['selected'] = $item['url'] == Ntentan::getUrl(Ntentan::$route);

            $this->items[] = $item;
        }
        $this->set('items', $this->items);
    }
    
    public function preRender()
    {
        $this->set('has_links', $this->hasLinks);
    }
}
