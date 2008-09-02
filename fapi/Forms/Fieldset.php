<?php
include_once "Container.php";
include_once "DefaultRenderer.php";

class Fieldset extends Container
{	
	public function __construct($label="",$description="")
	{
		parent::__construct();
		$this->setLabel($label);
		$this->setDescription($description);
	}
	
	public function render()
	{
		print "<fieldset class='fapi-fieldset ".$this->getCSSClasses()."'>";
		print "<legend>".$this->getLabel()."</legend>";
		print "<div class='fapi-description'>".$this->getDescription()."</div>";
		/*foreach($this->elements as $element)
		{
			DefaultRenderer::render($element);
		}*/
		$this->renderElements();	
		print "</fieldset>";
	}
}
?>