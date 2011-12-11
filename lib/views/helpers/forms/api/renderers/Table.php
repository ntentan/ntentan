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
    
    public function element($element, $showFields = true)
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
