<?php
/*  Copyright 2008, James Ainooson 
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

class Label extends Element
{
	public function __construct($label)
	{
		$this->setLabel($label);
	}
	
	public function render()
	{
		print "<label>".$this->getLabel()."</label>";
	}
	
	public function getData()
	{
		return array();
	}
	
	public function validate()
	{
		//Always return true cos labels can't be validated.
		return true;
	}
}
?>