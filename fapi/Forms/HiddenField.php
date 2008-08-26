<?php
include_once "Field.php";
/**
 * Implementation of a regular hidden field. This field is used to hold
 * form information that is not supposed to be visible to the user.
 *
 */
class HiddenField extends Field
{
	public function __construct($name="", $value="")
	{
		parent::__construct($name, $value);
	}
	
	public function render()
	{
		print '<input type="hidden" name="'.$this->getName().'" value="'.$this->getValue().'" />'; 
	}
}

?>