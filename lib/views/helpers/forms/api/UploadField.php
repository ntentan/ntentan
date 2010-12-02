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

class UploadField extends Field
{
	public function __construct($label="",$name="",$description="",$value="",$destinationFile="")
	{
		Field::__construct($name,$value);
		Element::__construct($label, $description);
		$this->addAttribute("type","file");
	}

	public function render()
	{
		$this->setAttribute("id",$this->getId());
		$this->addAttribute("name",$this->getName());
		$attributes = $this->getAttributes();
		$ret .= "<input $attributes />";
		return $ret;
	}
}
