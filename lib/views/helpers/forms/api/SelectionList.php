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
 * A selection list class for the forms helper. This class renders an HTML
 * select form object with its associated options.
 */
class SelectionList extends Field
{
	/**
     * An array of options to display with this selection list
     * @var array
     */
	protected $options = array();

	/**
     * When set true, this selection list would allow multiple selections
     * @var boolean
     */
	protected $multiple;

    /**
     * Constructs a new selection list. This constructor could be invoked through
     * the form helper's $this->form->get_* method as $this->form->get_selection_list().
     *
     * @param string $label The label for the selection list
     * @param string $name The name of the selection list
     * @param string $description A brief description for the selection list
     */
	public function __construct($label="", $name="", $description="")
	{
		Field::__construct($name);
		Element::__construct($label, $description);
		$this->addOption("","");
	}

	/**
     * Sets whether multiple selections are allowed. This method automatically
     * appends the array symbol '[]' to the name of the selection list object.
     * @param boolean $multiple
     * @return SelectionList
     */
	public function setMultiple($multiple)
	{
		$this->name.="[]";
		$this->multiple = $multiple;
		return $this;
	}

	/**
     * Add an option to the selection list.
     * @param string $label
     * @param string $value
     * @return SelectionList
     */
	public function addOption($label="", $value="")
	{
		if($value==="") $value=$label;
		$this->options[$value] = $label;
		return $this;
	}

    /**
     * An alias for SelectionList::addOption
     * @param string $label
     * @param string $value
     * @return SelectionList
     */
    public function option($label='', $value='')
    {
        $this->addOption($label, $value);
        return $this;
    }

	public function render()
	{
		$this->addAttribute("id",$this->id());
		$ret = "<select {$this->getAttributes()} class='fapi-list ".$this->getCSSClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
		foreach($this->options as $value => $label)
		{
			$ret .= "<option value='$value' ".($this->getValue()==$value?"selected='selected'":"").">$label</option>";
		}
		$ret .= "</select>";
		return $ret;
	}

    /**
     * Set the options using a key value pair datastructure represented in the form of
     * a structured array.
     *
     * @param array $options An array of options
     * @param boolean $merge If set to true the options in the array are merged
     *                       with existing options
     * 
     * @return SelectionList
     */
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

    public function options($options, $merge = true)
    {
        $this->setOptions($options, $merge);
        return $this;
    }

    /**
     * Return the array of options
     * @return array
     */
	public function getOptions()
	{
		return $options;
	}
}

