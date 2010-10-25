<?php
namespace ntentan\views\helpers\forms\api;

class UploadField extends Field
{
	public function __construct($label="",$name="",$description="",$value="",$destinationFile="")
	{
		Field::__construct($name,$value);
		Element::__construct($label, $description);
		$this->addAttribute("type","file");
	}

	public function render()
	{
		$this->setAttribute("id",$this->getId());
		$this->addAttribute("name",$this->getName());
		$attributes = $this->getAttributes();
		$ret .= "<input $attributes />";
		return $ret;
	}
}
