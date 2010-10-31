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
 * The Attribute class is used for storing and rendering HTML attributes.
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
	 * Returs the HTML representation of the attribute.
	 * @return string
	 */
	public function getHTML()
	{
		return "$this->attribute=\"$this->value\"";
	}

	/**
	 * Sets the value for the attribute.
	 * @return Attribute
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
		return $this;
	}

	/**
	 * Gets the value of the attribute.
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * Sets the value represented as the value of the attribute.
	 * @return Attribute
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * Returns the value of the attribute.
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	public function __tostring()
	{

	}
}

