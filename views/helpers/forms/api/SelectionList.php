<?php
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

