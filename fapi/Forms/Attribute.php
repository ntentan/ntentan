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
 * The Attribute class is used for storing and rendering HTML attributes.
 *
 */
class Attribute
{
	/**
	 * The attribute.
	 */
	protected $attribute;
	
	/**
	 * The value to be attached to the value.
	 */
	protected $value;
	
	/**
	 * The constructor of the Attribute.
	 * 
	 * @param $attribute The attribute.
	 * @param $value The value to attach to the attribute.
	 * 
	 */
	public function __construct($attribute, $value)
	{
		$this->attribute = $attribute;
		$this->value = $value;
	}
	
	public function getHTML()
	{
		return "$this->attribute=\"$this->value\"";
	}
	
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}
	
	public function getAttribute()
	{
		return $this->attribute;
	}
	
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	public function getValue()
	{
		return $this->value;
	}
}
?>