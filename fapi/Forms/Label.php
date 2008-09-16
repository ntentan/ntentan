<?php
class Label extends Element
{
	public function __construct($label)
	{
		$this->setLabel($label);
	}
	
	public function render()
	{
		print "<label>".$this->getLabel()."</label>";
	}
	
	public function getData()
	{
		return array();
	}
	
	public function validate()
	{
		//Always return true cos labels can't be validated.
		return true;
	}
}
?>