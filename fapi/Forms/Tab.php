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
		print "<div class='fapi-tab {$this->getCSSClasses()}'>";
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