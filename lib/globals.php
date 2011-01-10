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
use ntentan\views\template_engines\Template;

/**
 * A shortcut wrapper function around the Ntentan::getUrl method
 * @param $url
 */
function u($url)
{
    return Ntentan::getUrl($url);
}

/**
 * A shortcut wrapper around the Ntentan::getFilePath method
 * @param unknown_type $path
 */
function n($path)
{
    return Ntentan::getFilePath($path);
}

/**
 * A shortcut wrapper around the Ntentan::toSentence method
 * @param $string
 */
function s($string)
{
    return Ntentan::toSentence($string);
}

/**
 * A shortcut wrapper around the 
 * @param string $template
 * @param array $data
 */
function t($template, $data)
{
    return Template::out($template, $data);
}

