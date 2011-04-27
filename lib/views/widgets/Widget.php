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

namespace ntentan\views\widgets;

use ntentan\views\template_engines\Template;
use ntentan\Ntentan;
use ntentan\views\Presentation;
use ntentan\caching\Cache;

/**
 * Enter description here ...
 * @author ekow
 * @todo Look at the possibility of renaming blocks to widgets
 */
abstract class Widget extends Presentation
{
    protected $data = array();
    protected $template;
    public $name;
    public $filePath;
    public $cacheLifetime = -1;
    public $alias;

    public function init()
    {
        
    }

    public function setData($data)
    {
        $this->data = $data;
    }
    
    protected function set($params1, $params2 = null)
    {
        if(is_array($params1))
        {
            $this->data = array_merge($this->data, $params1);
        } 
        else
        {
            $this->data[$params1] = $params2;
        }
    }

    protected function getData()
    {
        return $this->data;
    }

    public function preRender()
    {

    }

    public function postRender()
    {
        
    }

    public function __toString()
    {
        $cacheKey = $this->getCacheKey();
        if(Cache::exists($cacheKey))
        {
            $output = Cache::get($cacheKey);
        }
        else
        {
            $this->preRender();
            if($this->template == "")
            {
                $this->template = $this->filePath . "/{$this->name}.tpl.php";
            }
            if(\file_exists($this->template))
            {
                $output = Template::out($this->template, $this->data);
            }
            else
            {
                throw new \ntentan\exceptions\FileNotFoundException("Template <code><b>{$this->template}</b></code> for widget <code><b>{$this->name}</b></code> not found!");
            }
            
            $this->postRender();
            Cache::add($cacheKey, $output, $this->cacheLifetime);
        }
        return $output;
    }

    public function alias($alias)
    {
        $this->alias = $alias;
        $this->set('alias', $alias);
        return $this;
    }

    private function getCacheKey()
    {
        return ($this->alias == '' ? $this->name : $this->alias) . '_widget';
    }

    public function cached()
    {
        return Cache::exists($this->getCacheKey());
    }
}
