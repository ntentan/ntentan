<?php
/**
 * Renders a form
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

namespace ntentan\views\helpers\forms\api;
use ntentan\views\helpers\forms\FormsHelper;

/**
 * The form class. This class represents the overall form class. This
 * form represents the main form for collecting data from the user.
 */
class Form extends Container
{
    public $submitValue;
    public $showSubmit = true;
    public $successUrl;
    protected $method = "POST";
    private $action;
    
    private static $numForms;

    //! Constructor for initialising the forms. This constructor accepts
    //! the method of the form.
    public function __construct($id="", $method="POST")
    {
        $this->setId($id);
        $this->method = $method;
        $this->action = $_SERVER["REQUEST_URI"];
    }

    public function action($action)
    {
        $this->action = $action;
        return $this;
    }
    
    public function renderHead()
    {
        $this->addAttribute("method", $this->method);
        $this->addAttribute("id", $this->id());
        $this->addAttribute("class", "fapi-form");
        $this->addAttribute('action', $this->action);
        $this->addAttribute('accept-charset', 'utf-8');
        
        return '<form '.$this->getAttributes().'>' . FormsHelper::getRendererInstance()->head();
    }

    public function renderFoot()
    {
        $ret = "";
        if($this->showSubmit)
        {
            $ret .= '<div class="form-submit-area">';
            $submitValue = $this->submitValue?("value='{$this->submitValue}'"):"";
            if($this->ajaxSubmit)
            {
                $ret .= sprintf('<input class="form-submit" type="button" %s onclick="%s"  />',$submitValue,$onclickFunction);
            }
            else
            {
                $ret .= sprintf('<input class="form-submit" type="submit" %s />',$submitValue);
            }
            $ret .= '</div>';
        }
        $ret .= '</form>';
        return FormsHelper::getRendererInstance()->foot() . $ret;
    }

    public function setShowFields($show_field)
    {
        Container::setShowField($show_field);
        $this->setShowSubmit($show_field);
    }

    public function setId($id)
    {
        parent::id($id == "" ? "form" . Form::$numForms++ : $id);
        return $this;
    }
}
