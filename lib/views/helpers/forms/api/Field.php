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
 * The form field class. This class represents a form field element.
 * Sublcasses of this class are to be used to capture information from
 * the user of the application.
 * \ingroup Form_API
 */
abstract class Field extends Element
{
	/**
	 * A flag for setting the required state of the form. If this value
	 * is set as true then the form would not be validated if there is
	 * no value entered into this field.
	 */
	public $required = false;

	/**
	 * The value of the form field.
	 */
	protected $value;

	/**
	 * The enabled state of the field.
	 */
	protected $enabled;

	//! The name of a custom validation function which can be used to
	//! perform further validations on the field.
	protected $validationFunc;

	//! A validation constraint which expects that the value entered in
	//! this field is unique in the database.
	protected $unique;
	
	public $isField = true;

	public function getId()
	{
		$id = parent::getId();
		if($id == "" && $this->ajax)
		{
			$id = str_replace(".","_",$this->getName());
		}
		return $id;
	}

	/**
	 * The constructor for the field element.
	 */
	public function __construct($name="", $value="")
	{
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * Sets the value of the field.
	 *
	 * @param $value The value of the field.
	 */
	public function setValue($value)
	{
		$this->value = $value;
        return $this;
	}

	/**
	 * Get the value of the field.
	 *
	 * @return unknown
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
     * Sets the required status of the field.
     *
     * @param The required status of the field.
     */
	public function setRequired($required)
	{
		$this->required = $required;
		return $this;
	}

    public function required($required = null)
    {
        if($required === null)
        {
            return $this->required;
        }
        else
        {
            $this->required = $required;
            return $this;
        }
    }

	/**
     * Returns the required status of the field.
     *
     * @return The required status of the field.
     */
	public function getRequired()
	{
		return $this->required;
	}

	//! Sets the data that is stored in this field.
	//! \param $data An array of fields. This method just looks through for
	//!              a field that matches it and then applies its value to
	//!              itself.
	public function setData($data)
	{
		if(array_search($this->getName(false),array_keys($data))!==false)
		{
			$this->setValue($data[$this->getName(false)]);
		}
	}

	public function validate()
	{
		//Perform the required validation. Generate an error if this
		//field is empty.
		if($this->getRequired() && $this->getValue() === "" )
		{
			$this->error = true;
			array_push($this->errors,$this->getLabel()." is required.");
			return false;
		}

		// Call the custom validation function.
		$validationFunc = $this->validationFunc;
		if($validationFunc!="")
		{
			$this->error = !$validationFunc($this,$this->errors);
			return !$this->error;
		}
		return true;
	}

	public function getType()
	{
		return __CLASS__;
	}

	public function getCSSClasses()
	{
		$classes=parent::getCSSClasses();
		if($this->error) $classes.="fapi-error ";
		if($this->getRequired()) $classes .="required ";
		return $classes;
	}

	public function getOptions()
	{
		return array();
	}

}
