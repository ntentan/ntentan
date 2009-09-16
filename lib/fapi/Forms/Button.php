<?php
class Button extends Field
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
		$this->addAttribute("class","fapi-button");
		$this->addAttribute("id",$this->getId());
		$this->addAttribute("type","button");
		return "<input ".$this->getAttributes()."/>";
	}

	public function getType()
	{
		return __CLASS__;
	}
}
?>
