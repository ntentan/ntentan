<?php
class BoxContainer extends Container
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function render()
	{
		$this->addAttribute("class","fapi-box {$this->getCSSClasses()}");
		print "<div {$this->getAttributes()}>";
		$this->renderElements();
		print "</div>";
	}
}
?>