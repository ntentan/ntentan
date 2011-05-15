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
 * 
 */

namespace ntentan\views\helpers\forms\api;

/**
 * A standard radio button. Can be added to the radio button group.
 * @ingroup Form_API
 */
class RadioButton extends Field
{
	protected $checked_value;
    public $renderLabel = false;

	/**
	 * The constructor of the radio button.
	 *
	 * @param $label
	 * @param $value
	 * @param $description
	 * @param $id
	 */
	public function __construct($label="", $value="", $description="", $id="")
	{
		Element::__construct($label, $description, $id );
		//Field::__construct("", $value);
		$this->setCheckedValue($value);
	}

	/**
	 * Returns the type of the
	 *
	 * @return unknown
	 */
	public function getType()
	{
		return __CLASS__;
	}

	public function getCheckedValue()
	{
		return $this->checked_value;
	}

	public function setCheckedValue($checked_value)
	{
		$this->checked_value = $checked_value;
	}

	public function render()
	{
		$ret = "<input class='fapi-radiobutton ".$this->getCSSClasses()."' ".$this->getAttributes()." type='radio' name='".$this->getName()."' value='".$this->getCheckedValue()."' ".($this->getValue()==$this->getCheckedValue()?"checked='checked'":"")."/>";
		$ret .= '<label class="fapi-radiobutton-label">'.$this->getLabel()."</label>";
		$ret .= "<div class='fapi-description'>".$this->getDescription()."</div>";
		return $ret;
	}
}
