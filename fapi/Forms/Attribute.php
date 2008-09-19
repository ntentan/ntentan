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

/**
 * The Attribute class is used for storing and rendering HTML attributes.
 * @category Forms
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
	
	/**
	 * Returns the HTML text for the attribute.
	 *
	 * @return The text for the attribute in the format attribute="value"
	 */
	public function getHTML()
	{
		return "$this->attribute=\"$this->value\"";
	}
	
	/**
	 * Sets the attribute.
	 *
	 * @param unknown_type $attribute
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
	}
	
	/**
	 * Gets tje attribute
	 *
	 * @return The attribute
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}
	
	/**
	 * Sets the value of the attribute.
	 *
	 * @param $value The value to assign to the attribute.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	/**
	 * Returns the value for the attribute.
	 *
	 * @return The value for the attribute.
	 */
	public function getValue()
	{
		return $this->value;
	}
}
?>
