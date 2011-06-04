<?php
/*
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
 *
 */

namespace ntentan\views;

use ntentan\views\template_engines\TemplateEngine;
use ntentan\views\template_engines\Template;

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

    public function __construct()
    {
        $this->setContentType("text/html");
        $this->layout = "main.tpl.php";
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
                return TemplateEngine::render($this->layout, $viewData, $this);
            }
            else
            {
                return $data;
            }
        }
        catch(Exception $e)
        {
            print "Error!";
        }
    }
}
