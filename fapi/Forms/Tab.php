<?php
class Tab extends Container
{
	protected $legend;
	protected $selected;
	
	public function __construct($legend="")
	{
		parent::__construct();
		$this->legend = $legend;	
	}
	
	public function getLegend()
	{
		return $this->legend;
	}
	
	public function setLegend($legend)
	{
		$this->legend = $legend;
	}
	
	public function render()
	{
		$this->addAttribute("class","fapi-tab {$this->getCSSClasses()}");
		print "<div {$this->getAttributes()}>";
		$this->renderElements();
		print "</div>";
	}
	
	public function getSelected()
	{
		return $selected;
	}
	
	public function setSelected($selected)
	{
		$this->selected = $selected;
	}
}
?>