<?php
/**
 * Checkboxes for forms
 * 
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan\views\helpers\forms\api;

include_once "Field.php";

/**
 * A regular checkbox with a label.
 */
class Checkbox extends Field
{
	/**
	 * The value that this field should contain if this checkbox is checked.
	 */
	protected $checkedValue;

	/**
	 * Constructor for the checkbox.
	 *
	 * @param $label The label of the checkbox.
	 * @param $name The name of the checkbox used for the name='' attribute of the HTML output
	 * @param $description A description of the field.
	 * @param $value A value to assign to this checkbox.
	 */
	public function __construct($label="", $name="", $description="", $value="1")
	{
		Element::__construct($label, $description);
		parent::__construct($name);
		$this->setCheckedValue($value);
	}

	/**
	 * Sets the value that should be assigned as the checked value for
	 * this check box.
	 * @param $checkedValue The value to be assigned.
	 * @return Checkbox
	 */
	public function setCheckedValue($checkedValue)
	{
		$this->checkedValue = $checkedValue;
		$this->addAttribute("id", $this->id());
		return $this;
    }

	/**
	 * Gets and returns the checkedValue for the check box.
	 * @return string
	 */
	public function getCheckedValue()
	{
		return $this->checkedValue;
    }

	public function render()
	{
		$ret = "";
		$ret .= '<input class="form-checkbox" type="checkbox" name="'.$this->getName().'" value="'.$this->getCheckedValue().'" '.
		      (($this->getValue()==$this->getCheckedValue())?"checked='checked'":"").' '.$this->getAttributes().' />';
		return $ret;
	}
}

