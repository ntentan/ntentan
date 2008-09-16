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


/**
 * An item that can be added to a selection list.
 *
 */
class SelectionListItem
{
	public function __construct($label="", $value="")
	{
		$this->label = $label;
		$this->value = $value;
	}
	public $label;
	public $value;
}

class SelectionList extends Field
{
	protected $options = array();
	protected $multiple;
	
	public function __construct($label="", $name="", $description="")
	{
		Field::__construct($name);
		Element::__construct($label, $description);
		$this->addOption("","");
	}
	
	public function setMultiple($multiple)
	{
		$this->multiple = $multiple;
	}
	
	public function addOption($label="", $value="")
	{
		array_push($this->options, new SelectionListItem($label, $value));
	}
	
	public function render()
	{
		print "<select class='fapi-list ".$this->getCSSClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
		foreach($this->options as $option)
		{
			print "<option value='$option->value' ".($this->getValue()==$option->value?"selected='selected'":"").">$option->label</option>";
		}
		print "</select>";
	}
	
	public function getDisplayValue()
	{
		foreach($this->options as $option)
		{
			if($option->value == $this->getValue())
			{
				return $option->label;
			}
		}
	}
}
?>