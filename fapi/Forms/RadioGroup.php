<?php
include_once "Field.php";

class RadioGroup extends Field
{
	protected $buttons = array();
	
	public function __construc($name="")
	{
		$this->setName($name);	
	}
	
	public function addRadiobutton($button)
	{
		if($button->getType()=="RadioButton")
		{
			$button->setName($this->name);
			array_push($this->buttons, $button);
		}
		else
		{
			throw new Exception("Non radio button added to radio group");
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
			return array($this->getName() => $_POST[$this->getName()]);
		}
		else if($this->getMethod()=="GET")
		{
			return array($this->getName() => $_GET[$this->getName()]);
		}
		
	}
}

?>