<?php
/**
 * Source file for the view helpers loader class
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
 * @category Template Engines
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\views\template_engines;

use ntentan\Ntentan;

/**
 * A class for loading the helpers in views.
 */
class HelpersLoader
{
    private $pluginMode = false;
    private $plugin;
    private $loadedHelpers = array();
    private $viewData = array();

    private function getHelper($helper)
    {
        $helperPlural = Ntentan::plural($helper);
        $helper = $helperPlural == null ? $helper : $helperPlural;
        if($helper === null)
        {
            return false;
        }
        if(!isset($this->loadedHelpers[$this->plugin . $helper]))
        {
            $camelizedHelper = Ntentan::camelize($helper) . "Helper";
            $helperFile = Ntentan::$modulesPath . "/helpers/$helper/$camelizedHelper.php";
            if(file_exists($helperFile))
            {
                require_once $helperFile;
                $helperClass = "\\" . Ntentan::$namespace . "\\helpers\\$helper\\$camelizedHelper";
            }
            else if($this->pluginMode)
            {
                $path = Ntentan::getPluginPath("{$this->plugin}/helpers/$helper");
                Ntentan::addIncludePath("{$this->plugin}");
                $helperClass = "\\ntentan\\plugins\\{$this->plugin}\\helpers\\$helper\\$camelizedHelper";
            }
            else if(file_exists(Ntentan::getFilePath("lib/views/helpers/$helper")))
            {
                $path = Ntentan::getFilePath("lib/views/helpers/$helper");
                $helperClass = "\\ntentan\\views\\helpers\\$helper\\$camelizedHelper";                
            }
            else
            {
                return false;
            }
                        
            Ntentan::addIncludePath($path);
            $helperInstance = new $helperClass();
            $this->loadedHelpers[$this->plugin . $helper] = $helperInstance;
        }
        return $this->loadedHelpers[$this->plugin . $helper];
    }

    public function __get($helper)
    {
        $helperInstance = $this->getHelper($helper);
        if($helperInstance === false)
        {
            if($this->pluginMode === false)
            {
                $this->pluginMode = true;
                $this->plugin = $helper;
                return $this;
            }
        }
        else
        {
            return $helperInstance;
        }
    }

    public function __call($helper, $arguments)
    {
        $helper = $this->getHelper($helper);
        $method = new \ReflectionMethod($helper, 'help');
        $this->plugin = null;
        $this->pluginMode = false;
        return $method->invokeArgs($helper, $arguments);
    }
}
