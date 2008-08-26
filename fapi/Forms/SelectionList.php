<?php
/**
 * An item that can be added to a selection list.
 *
 */
class SelectionListItem
{
	public function __construct($label="", $value="")
	{
		$this->label = $label;
		$this->value = $value;
	}
	public $label;
	public $value;
}

class SelectionList extends Field
{
	protected $options = array();
	protected $multiple;
	
	public function __construct($label="", $name="", $description="")
	{
		Field::__construct($name);
		Element::__construct($label, $description);
		$this->addOption("","");
	}
	
	public function setMultiple($multiple)
	{
		$this->multiple = $multiple;
	}
	
	public function addOption($label="", $value="")
	{
		array_push($this->options, new SelectionListItem($label, $value));
	}
	
	public function render()
	{
		print "<select class='fapi-list ".$this->getClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
		foreach($this->options as $option)
		{
			print "<option value='$option->value' ".($this->getValue()==$option->value?"selected='selected'":"").">$option->label</option>";
		}
		print "</select>";
	}
}
?>