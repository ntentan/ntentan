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
 * Implementation of a regular text field. This field is used to
 * accept single line text input from the user.
 * @ingroup Form_API
 */
class TextField extends Field
{
	protected $max_length;
	protected $type;
	protected $max_val;
	protected $min_val;
	protected $regexp;

	public function __construct($label="",$name="",$description="",$value="")
	{
		Field::__construct($name,$value);
		Element::__construct($label, $description);
		$this->type = "TEXT";
		$this->addAttribute("type","text");
	}

	public function render()
	{
		$this->addAttribute("class", "textfield ".$this->getCSSClasses());
		$this->addAttribute("name", $this->getName());
		$this->addAttribute("id", $this->id());
		$this->addAttribute("value", $this->getValue());
		return "<input {$this->getAttributes()} />";
	}
}
