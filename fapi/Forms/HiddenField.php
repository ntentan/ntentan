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


include_once "Field.php";
/**
 * Implementation of a regular hidden field. This field is used to hold
 * form information that is not supposed to be visible to the user.
 *
 */
class HiddenField extends Field
{
	public function __construct($name="", $value="")
	{
		parent::__construct($name, $value);
	}
	
	public function render()
	{
		print '<input type="hidden" name="'.$this->getName().'" value="'.$this->getValue().'" />'; 
	}
}

?>