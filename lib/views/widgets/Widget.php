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

namespace ntentan\views\widgets;

use ntentan\views\template_engines\TemplateEngine;
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
    private $cacheLifetime = -1;
    public $cacheContents = false;
    private $alias;
    public $plugin;

    public function init($params = null)
    {

    }

    public abstract function execute();

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
        if(Cache::exists($cacheKey) && Ntentan::$debug === false)
        {
            $output = Cache::get($cacheKey);
        }
        else
        {
            $this->execute();
            $this->preRender();
            TemplateEngine::appendPath($this->filePath);
            
            if($this->template == "")
            {
                $this->template = ($this->plugin == '' ? '' : "{$this->plugin}_" )."{$this->name}_widget.tpl.php";
            }
            
            try{
                $output = TemplateEngine::render($this->template, $this->data);
            }
            catch(Exception $e)
            {
                die('Template not Found!');
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

    public function setAlias($alias)
    {
        $this->alias($alias);
    }

    public function setCacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = $cacheLifetime;
    }

    private function getCacheKey()
    {
        return ($this->alias == '' ? $this->name : "{$this->alias}_{$this->name}_" ) . TemplateEngine::getContext() . '_widget';
    }

    public function cached()
    {
        return Cache::exists($this->getCacheKey());
    }

    public function cache_lifetime($lifeTime)
    {
        $this->cacheLifetime = $lifeTime;
        return $this;
    }
}
