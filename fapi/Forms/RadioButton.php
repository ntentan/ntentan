<?php
/**
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
 *   along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


include_once "Element.php";

/**
 * A standard radio button. Can be added to the radio button group.
 *
 */
class RadioButton extends Field
{
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
		Field::__construct("", $value);
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
	
	public function render()
	{
		print "<input class='fapi-radiobutton ".$this->getCSSClasses()."' type='radio' name='".$this->getName()."' value='".$this->getValue()."' ".($this->getValue()==$_POST[$this->getName()]?"checked='checked'":"")."/>";
		print '<span class="fapi-label">'.$this->getLabel()."</span>";
		print "<div class='fapi-description'>".$this->getDescription()."</div>";
	}
}
?>