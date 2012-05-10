<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2012 James Ekow Abaka Ainooson
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

namespace ntentan\views\template_engines;

use ntentan\Ntentan;

/**
 * A class for loading the helpers in views.
 */
class HelpersLoader
{
    private $loadedHelpers = array();

    private function getHelper($helper)
    {
        $helperPlural = Ntentan::plural($helper);
        $helper = $helperPlural == null ? $helper : $helperPlural;
        if($helper === null)
        {
            throw new \Exception("Unknown helper <b>$helper</b>");
        }
        if(!isset($this->loadedHelpers[$helper]))
        {
            $camelizedHelper = Ntentan::camelize($helper) . "Helper";
            $helperFile = Ntentan::$namespace . "/helpers/$helper/$camelizedHelper.php";
            if(file_exists($helperFile))
            {
                require_once $helperFile;
                $helperClass = "\\" . Ntentan::$namespace . "\\helpers\\$helper\\$camelizedHelper";
            }
            else
            {
                Ntentan::addIncludePath(Ntentan::getFilePath("lib/views/helpers/$helper"));
                $helperClass = "\\ntentan\\views\\helpers\\$helper\\$camelizedHelper";                
            }
            $this->loadedHelpers[$helper] = new $helperClass();
        }
        return $this->loadedHelpers[$helper];
    }

    public function __get($helper)
    {
        return $this->getHelper($helper);
    }

    public function __call($helper, $arguments)
    {
        $helper = $this->getHelper($helper);
        $method = new \ReflectionMethod($helper, 'help');
        return $method->invokeArgs($helper, $arguments);
    }
}
