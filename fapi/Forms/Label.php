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
}
?>