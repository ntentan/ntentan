<?php
/**
 * The view class for dealing with views
 * 
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
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
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan\views;

use ntentan\views\template_engines\TemplateEngine;

use ntentan\Ntentan;

/**
 * An extension of the presentation class for the purposes of rendering views.
 * @author ekow
 */
class View extends Presentation
{
    public $layout;
    public $template;
    public $defaultTemplatePath;
    public $encoding;
    public $cacheTimeout = false;

    public function __construct()
    {
        $this->setContentType("text/html");
        $this->layout = "main.tpl.php";
        TemplateEngine::appendPath("views");
        TemplateEngine::appendPath("views/default");
    }

    public function setContentType($contentType, $encoding="UTF-8")
    {
        $this->encoding = $encoding;
    	header("Content-type: $contentType;charset=$encoding");
    }

    public function out($viewData)
    {
        try
        {
            if($this->template === false)
            {
                $data = null;
            }
            else
            {
                $data = TemplateEngine::render($this->template, $viewData, $this);
            }

            if($this->layout !== false && !Ntentan::isAjax())
            {
                $viewData['contents'] = $data;
                $output = TemplateEngine::render($this->layout, $viewData, $this);
            }
            else
            {
                $output = $data;
            }
        }
        catch(Exception $e)
        {
            print "Error!";
        }
        
        return $output;
    }
}
