<?php
/*
 *  
 *  Copyright 2008, James Ainooson 
 *
 *  This file is part of Ntentan.
 *
 *   Ntentan is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Ntentan is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Ntentan.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

include_once "Field.php";

/**
 * A regular checkbox with a label.
 *
 */
class Checkbox extends Field
{
	
	protected $checkedValue;

	
	/**
	 * Constructor for the checkbox.
	 *
	 * @param $label The label of the checkbox.
	 * @param $name The name of the checkbox used for the name='' attribute of the HTML output
	 * @param $description A description of the field.
	 * @param $value A value to assign to this checkbox.
	 */
	public function __construct($label="", $name="", $description="", $value="")
	{
		Element::__construct($label, $description);
		parent::__construct($name, $value);
	}

	public function setCheckedValue($checkedValue)
	{
		$this->checkedValue = $checkedValue;
    }

	public function getCheckedValue()
	{
		return $this->checkedValue;
    }

	public function render()
	{
		print '<input class="fapi-checkbox" type="checkbox" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getCheckedValue().'" '.
		      (($this->getValue()==$this->getCheckedValue())?"checked='checked'":"").' />';

		print '<span class="fapi-label">'.$this->getLabel()."</span>";
	}
	
	public function getRequired()
	{
		return false;
	}
	
	public function getType()
	{
		return __CLASS__;
	}
	
}

?>
