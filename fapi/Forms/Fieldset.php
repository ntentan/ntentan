<?php
include_once "Container.php";
include_once "DefaultRenderer.php";

class Fieldset extends Container
{	
	public function render()
	{
		print "<fieldset>";
		print "<legend>".$this->getLabel()."</legend>";
		print "<div class='fapi-description'>".$this->getDescription()."</div>";
		foreach($this->elements as $element)
		{
			DefaultRenderer::render($element);
		}	
		print "</fieldset>";
	}
}
?>