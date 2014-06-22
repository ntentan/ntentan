<?php
/**
 * A vertical in-line renderer
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

namespace ntentan\views\helpers\forms\api\renderers;

use \ntentan\views\helpers\forms\api\Element;

class Inline extends Renderer
{

    public $noWrap = false;
    
    /**
     * The default renderer head function
     *
     */
    public function head()
    {
    
    }
    
    /**
     * The default renderer body function
     *
     * @param $element The element to be rendererd.
     */
    public function element($element)
    {
    	$ret = "";
    	// Ignore Hidden Fields
    	if($element->getType()=="HiddenField")
    	{
            return $element->render();
    	}
    
    	if(!$this->noWrap)
    	{
            $ret .= "<div class='form-element-div' ". ($element->id()==""?"":"id='".$element->id()."_wrapper'") . " " . $element->getAttributes(Element::SCOPE_WRAPPER).">";
    	}

        if($element->getType() == "checkbox")
        {
            $element->renderLabel = false;
        }
        
        if(!$element->isContainer && $element->renderLabel)
        {
            $label = $element->getLabel();
            $ret .= $this->renderLabel($element);
        }
            
        if($element->getType()=="ntentan\views\helpers\forms\api\Field")
        {
            $ret .= "<div>" . $element->render() . "</div>";
        }
        else if($element->getType() == "checkbox")
        {
            $ret .= $element->render() . $this->renderLabel($element);
        }
        else
        {
            $ret .= $element->render();
        }
    
        if($element->getDescription() != "")
        {
            $ret .= "<div ".($element->id()==""?"":"id='".$element->id()."_desc'")." class='form-description'>".$element->getDescription()."</div>";
        }
        
        if($element->hasError())
        {
            $ret .= "<div class='form-errors'>";
            $ret .= "<ul>";
            foreach($element->getErrors() as $error)
            {
                $ret .= "<li>$error</li>";
            }
            $ret .= "</ul>";
            $ret .= "</div>";
        }
        
        if(!$this->noWrap)
        {
            $ret .= "</div>";
        }
    
        return $ret;
    }
    
    /**
     * The foot of the default renderer.
     *
     */
    public function foot()
    {
    
    }
    
    public function type()
    {
        return "inline";
    }
}
