<?php
class PasswordField extends TextField
{
	public function __construct($label="",$name="",$description="")
	{
		parent::__construct($label,$name,$description);
		$this->setAttribute("type","password");
	}
	
	public function getData()
	{
		parent::getData();
		if($this->getValue()!="") $this->setValue(md5($this->getValue()));
		return array($this->getName() => $this->getValue());
	}
}
?>