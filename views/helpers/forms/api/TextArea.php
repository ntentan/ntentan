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
	    $this->addAttribute('rows', 10);
        $this->addAttribute('cols', 80);
        $this->addAttribute('class', 'fapi-textarea');
        $this->addAttribute('name', $this->getName());
	    return "<textarea ".$this->getAttributes().$this->getCSSClasses()."'>".
	           $this->getValue()."</textarea>";
	}
}
