<?php
include_once "Field.php";

class RadioGroup extends Field
{
	protected $buttons = array();
	
	public function __construct($label="",$name="",$description="")
	{
		$this->setLabel($label);
		$this->setName($name);	
		$this->setDescription($description);
	}
	
	public function add($button)
	{
		if($button->getType()=="RadioButton")
		{
			$button->setName($this->name);
			array_push($this->buttons, $button);
		}
		else
		{
			throw new Exception("Object added to radio group is not of type RadioButton");
		}
	}
	
	public function removeRadioButton($index)
	{
		
	}
	
	public function render()
	{
		foreach($this->buttons as $button)
		{
			print "<div class='fapi-radio-button'>";
			$button->render();
			print "</div>";
		}
	}
	
	public function getData()
	{
		if($this->getMethod()=="POST")
		{
			$this->setValue($_POST[$this->getName()]);
		}
		else if($this->getMethod()=="GET")
		{
			$this->setValue($_GET[$this->getName()]);
		}
		return array($this->getName() => $this->getValue());
	}
}
?>