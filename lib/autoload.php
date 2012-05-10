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


use ntentan\Ntentan;
use ntentan\caching\Cache;

error_reporting(E_ALL ^ E_NOTICE);

function get_class_file($class)
{
    $key = "file_$class";
    if(Cache::exists($key))
    {
        $classFile = Cache::get($key);
    }
    else
    {
        $fullPath = explode("\\", $class);

        //Get rid of any initial empty class name
        if($fullPath[0] == "") array_shift ($fullPath);
        $class = array_pop($fullPath);


        if($fullPath[0] == \ntentan\Ntentan::$namespace)
        {
            $basePath = implode("/",$fullPath);
        }
        else if($fullPath[0] == 'ntentan' && $fullPath[1] == "plugins")
        {
            array_shift($fullPath);
            array_shift($fullPath);
            $basePath = \ntentan\Ntentan::getPluginPath(implode("/",$fullPath));
        }
        else if($fullPath[0] == 'ntentan' && $fullPath[1] == "dev")
        {
            array_shift($fullPath);
            array_shift($fullPath);
            $basePath = NTENTAN_DEV_HOME . '/' . implode("/",$fullPath);
        }
        else if($fullPath[0] == 'ntentan')
        {
            array_shift($fullPath);
            $basePath = \ntentan\Ntentan::getFilePath('lib/' . implode("/",$fullPath));
        }

        $classFile = $basePath . '/' . $class . '.php';
        Cache::add($key, $classFile);
    }
    return $classFile;
}

/**
 * Auto loading function. The function whic his responsible for loading all
 * unloaded classes.
 *
 * @param string $class
 */
function __autoload($class)
{
    $classFile = get_class_file($class);
    if(file_exists($classFile))
    {
        require_once $classFile;
    }
    else
    {
        throw new \Exception("Class file <code><b>$classFile</b></code> for <code><b>$class</b></code> class not found.");
    }
}
