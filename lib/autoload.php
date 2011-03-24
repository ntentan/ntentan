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

error_reporting(E_ALL ^ E_NOTICE);
/**
 * Auto loading function. The function whic his responsible for loading all
 * unloaded classes.
 * 
 * @param string $class
 */
function __autoload($class)
{
    $fullPath = explode("\\", $class);
    $class = array_pop($fullPath);
    if($fullPath[0] == \ntentan\Ntentan::$modulesPath)
    {
        //\ntentan\Ntentan::addIncludePath(implode("/",$fullPath));
        $basePath = implode("/",$fullPath);
    }
    else if($fullPath[0] == 'ntentan')
    {
        array_shift($fullPath);
        //\ntentan\Ntentan::addIncludePath(\ntentan\Ntentan::getFilePath('lib/' . implode("/",$fullPath)));
        $basePath = \ntentan\Ntentan::getFilePath('lib/' . implode("/",$fullPath));
    }

    $classFile = $basePath . '/' . $class . '.php';
    if(file_exists($classFile))
    {
        require_once $classFile;
    }
    else
    {
        throw new ntentan\exceptions\FileNotFoundException("Class file <code><b>$classFile</b></code> for <code><b>$class</b></code> class not found.");
    }
}
