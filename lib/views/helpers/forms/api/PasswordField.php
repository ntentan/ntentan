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

class PasswordField extends TextField
{
	protected $md5 = false;
	
	public function __construct($label="",$name="",$description="")
	{
		parent::__construct($label,$name,$description);
		$this->setAttribute("type","password");
	}
	
	public function setEncrypted($encrypted)
	{
		$this->md5 = $encrypted;
	}
	
	public function getData($storable=false)
	{
		parent::getData();
		if($this->md5)
		{
			if($this->getValue()!="") $this->setValue(md5($this->getValue()),false);
		}
		return array($this->getName(false) => $this->getValue());
	}
	

	/*public function render()
	{
		$this->addAttribute("class","fapi-textfield ".$this->getCSSClasses());
		$this->addAttribute("name",$this->getName());
		$this->addAttribute("id",$this->getId());
		return "<input {$this->getAttributes()} />"; //class="fapi-textfield '.$this->getCSSClasses().'" type="text" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getValue().'" />';
	}*/
	
	public function getDisplayValue()
	{
		return "This field cannot be viewed";
	}
}
