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

abstract class Renderer
{
    public $showFields = true;
    
    abstract public function head();
    abstract public function element($element);
    abstract public function foot();
    abstract public function type();
    
    protected function renderLabel($element)
    {
        $label = $element->getLabel();
        if($label != '')
        {
            $ret .= "<label class='form-label'>".$label;
            if($element->getRequired() && $label!="" && $element->getShowField())
            {
                $ret .= "<span class='required'>*</span>";
            }
            $ret .= "</label><br/>";
        }
        return $ret;
    }    
}