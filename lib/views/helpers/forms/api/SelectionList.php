<?php
/**
 * Selection Lists for forms
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
    
    protected $default;

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
    
    public function initial($default)
    {
        $this->default = $default;
        return $this;
    }

    public function render()
    {
        $keys = array_keys($this->options);
        array_unshift($keys, '');
        array_unshift($this->options, $this->default);
        $this->options = array_combine($keys, $this->options);
        
        $ret = "<select {$this->getAttributes()} class='fapi-list ".$this->getCSSClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
        
        // get the element and force it to be a string
        $elementValue = $this->getValue();
        if(is_object($elementValue)) $elementValue = (string)($elementValue);
        
        foreach($this->options as $value => $label)
        {
            $ret .= "<option value='$value' ".($elementValue == $value?"selected='selected'":"").">$label</option>";
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
