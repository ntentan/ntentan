<?php
include_once "Element.php";

/**
 * A standard radio button. Can be added to the radio button group.
 *
 */
class RadioButton extends Field
{
	/**
	 * The constructor of the radio button.
	 *
	 * @param $label
	 * @param $value
	 * @param $description
	 * @param $id 
	 */
	public function __construct($label="", $value="", $description="", $id="")
	{
		Element::__construct($label, $description, $id );
		Field::__construct("", $value);
	}
	
	/**
	 * Returns the type of the 
	 *
	 * @return unknown
	 */
	public function getType()
	{
		return __CLASS__;
	}
	
	public function render()
	{
		print "<input class='fapi-radiobutton ".$this->getClasses()."' type='radio' name='".$this->getName()."' value='".$this->getValue()."' ".($this->getValue()==$_POST[$this->getName()]?"checked='checked'":"")."/>";
		print '<span class="fapi-label">'.$this->getLabel()."</span>";
		print "<div class='fapi-description'>".$this->getDescription()."</div>";
	}
}
?>