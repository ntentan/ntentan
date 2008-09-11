<?php
include_once "Field.php";

class SubmitButton extends Field
{
	public function __construct($label="")
	{
		$this->setLabel($label);
	}

	public function render()
	{
		if($this->getLabel()!="")
		{
			$this->addAttribute("value",$this->getLabel());
		}
		$this->addAttribute("class","fapi-submit-button");
		$this->addAttribute("id",$this->getId());
		
		print "<input type='submit' ".$this->getAttributes()."/>";
	}
}
?>