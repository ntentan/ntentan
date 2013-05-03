<?php
/**
 * A renderer which renders forms using a tabular layout
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

class Table extends Renderer
{
    private $hiddenItems = array();
    
    public function head()
    {
        return "<table class='form-layout-table'>";
    }
    
    public function foot()
    {
	$return = "</table>";
        foreach($this->hiddenItems as $hiddenItem)
        {
            $return .= $hiddenItem->render();
        }
        return $return;
    }
    
    public function element($element)
    {
        if($element->getType() == 'HiddenField')
        {
            $this->hiddenItems[] = $element;
            return '';
        }
	$ret = "<tr class='form-layout-table-row' " . ($element->id()==""?"":"id='".$element->id()."_wrapper'") . " " . $element->getAttributes(Element::SCOPE_WRAPPER) . " >";
        if(!$element->isContainer && $element->renderLabel)
        {
            $ret .= "<td class='form-layout-table-label'>" . $this->renderLabel($element) . "</td>";		
        }

        $ret .="<td class='form-layout-table-field'>";
        if($element->getType()=="Field")
        {
            $ret .= $element->render();
        }
        else
        {
            $ret .= $element->render();
        }

        $ret .= "<div class='form-message' id='".$element->id()."-form-message'></div>";
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

        if(!$element->isContainer && $element->renderLabel)
        {
            $ret .= "<div class='form-description'>".$element->getDescription()."</div>";
        }

        $ret .= "</td></tr>";

        return $ret;        
    }
    
    public function type()
    {
        return "table";
    }
}
