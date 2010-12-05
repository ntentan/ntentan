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
 * 
 * @author ekow
 *
 */
class SelectionList extends Field
{
	//! Array of options.
	protected $options = array();

	//! A boolean value set if multiple selections.
	protected $multiple;

	public function __construct($label="", $name="", $description="")
	{
		Field::__construct($name);
		Element::__construct($label, $description);
		$this->addOption("","");
	}

	//! Sets weather multiple selections could be made.
	public function setMultiple($multiple)
	{
		$this->name.="[]";
		$this->multiple = $multiple;
		return $this;
	}

	//! Add an option to the selection list.
	//! \param $label The label of the options
	//! \param $value The value associated with the label.
	public function addOption($label="", $value="")
	{
		if($value==="") $value=$label;
		$this->options[$value] = $label;
		return $this;
	}

	public function render()
	{
		$this->addAttribute("id",$this->getId());
		$ret = "<select {$this->getAttributes()} class='fapi-list ".$this->getCSSClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
		foreach($this->options as $value => $label)
		{
			$ret .= "<option value='$value' ".($this->getValue()==$value?"selected='selected'":"").">$label</option>";
		}
		$ret .= "</select>";
		return $ret;
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
        return $this->value;
	}

	public function setOptions($options, $merge = true)
	{
	    if($merge) 
	    {
	        foreach($options as $value => $label)
	        {
	            $this->addOption($label, $value);
	        }
	    }
	    else
	    {
	        $this->options = $options;
	    }
	    return $this;
	}
	
	public function getOptions()
	{
		return $options;
	}
}

