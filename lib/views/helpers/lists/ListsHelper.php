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

namespace ntentan\views\helpers\lists;

use ntentan\Ntentan;
use ntentan\views\template_engines\Template;
use ntentan\views\helpers\Helper;

class ListsHelper extends Helper
{
    public $headers = array();
    public $hasHeaders = true;
    public $data = array();
    public $rowTemplate = null;
    public $defaultCellTemplate = null;
    public $cellTemplates = array();
    public $variables = array();
    
    public function __toString()
    {
        $this->rowTemplate = $this->rowTemplate == null ? 
            Ntentan::getFilePath('views/helpers/lists/templates/row.tpl.php') :
            $this->rowTemplate;
        $this->defaultCellTemplate = $this->defaultCellTemplate == null ? 
            Ntentan::getFilePath('views/helpers/lists/templates/default_cell.tpl.php') :
            $this->defaultCellTemplate;
        return Template::out(
            Ntentan::getFilePath('views/helpers/lists/templates/list.tpl.php'),
            array(
                "headers"               =>  $this->headers,
                "data"                  =>  $this->data,
                "row_template"          =>  $this->rowTemplate,
                "cell_templates"        =>  $this->cellTemplates,
                "default_cell_template" =>  $this->defaultCellTemplate,
                "variables"             =>  $this->variables,
                "has_headers"           =>  $this->hasHeaders
            )
        );
    }
}

