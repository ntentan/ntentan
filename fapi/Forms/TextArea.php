<?php
class TextArea extends Field
{
	public function __construct($label="",$name="",$description="")
	{
		$this->setLabel($label);
		$this->setName($name);
		$this->setDescription($description);
	}
	
	public function render()
	{
		print "<textarea class='fapi-textarea ".$this->getCSSClasses()."' name='".$this->getName()."'>".$this->getValue()."</textarea>"; 
	}
}
?>