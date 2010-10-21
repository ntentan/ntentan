<?php
namespace ntentan\views\helpers\forms\api;

class PasswordField extends TextField
{
	protected $md5 = false;
	
	public function __construct($label="",$name="",$description="")
	{
		parent::__construct($label,$name,$description);
		$this->setAttribute("type","password");
	}
	
	public function setEncrypted($encrypted)
	{
		$this->md5 = $encrypted;
	}
	
	public function getData($storable=false)
	{
		parent::getData();
		if($this->md5)
		{
			if($this->getValue()!="") $this->setValue(md5($this->getValue()),false);
		}
		return array($this->getName(false) => $this->getValue());
	}
	

	/*public function render()
	{
		$this->addAttribute("class","fapi-textfield ".$this->getCSSClasses());
		$this->addAttribute("name",$this->getName());
		$this->addAttribute("id",$this->getId());
		return "<input {$this->getAttributes()} />"; //class="fapi-textfield '.$this->getCSSClasses().'" type="text" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getValue().'" />';
	}*/
	
	public function getDisplayValue()
	{
		return "This field cannot be viewed";
	}
}
?>
