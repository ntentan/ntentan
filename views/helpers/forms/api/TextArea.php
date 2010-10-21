<?php
namespace ntentan\views\helpers\forms\api;

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
		return "<textarea ".$this->getAttributes()." class='fapi-textarea ".$this->getCSSClasses()."' name='".$this->getName()."'>".$this->getValue()."</textarea>";
	}
}
