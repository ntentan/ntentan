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

namespace ntentan\views\helpers\forms\api;

/**
 * The form class. This class represents the overall form class. This
 * form represents the main form for collecting data from the user.
 */
use ntentan\Ntentan;

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

    public function addFileUploadSupport($maxFileSize = "")
    {
        $this->addAttribute("enctype", "multipart/form-data");
        $this->add(new HiddenField("MAX_FILE_SIZE", "10485760"));
    }
    
    public function renderHead()
    {
        $this->addAttribute("method", $this->method);
        $this->addAttribute("id", $this->getId());
        $this->addAttribute("class", "fapi-form");
        $this->addAttribute('action', $this->action);

        return '<form '.$this->getAttributes().'>';
    }

	public function renderFoot()
	{
	    $ret = "";
		if($this->showSubmit)
		{
			$ret .= '<div class="fapi-submit-area">';
			$submitValue = $this->submitValue?("value='{$this->submitValue}'"):"";
			if($this->ajaxSubmit)
			{
				$ret .= sprintf('<input class="fapi-submit" type="button" %s onclick="%s"  />',$submitValue,$onclickFunction);
			}
			else
			{
				$ret .= sprintf('<input class="fapi-submit" type="submit" %s />',$submitValue);
			}
			$ret .= '</div>';
		}
		$ret .= '</form>';
		return $ret;
	}

	public function setShowFields($show_field)
	{
		Container::setShowField($show_field);
		$this->setShowSubmit($show_field);
	}

    public function setId($id)
    {
        parent::setId($id == "" ? "form" . Form::$numForms++ : $id);
        return $this;
    }
}
