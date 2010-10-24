<?php
namespace ntentan\views\helpers\forms\api;

class Tab extends Container
{
	protected $legend;
	protected $selected;

	public function __construct($legend="")
	{
		parent::__construct();
		$this->legend = $legend;
	}

	//! Gets the legend displayed at the top of the Tab.
	public function getLegend()
	{
		return $this->legend;
	}

	//! Sets the legend displaued at the top of the Tab.
	public function setLegend($legend)
	{
		$this->legend = $legend;
	}

	public function renderHead()
	{
		$this->addAttribute("class","fapi-tab {$this->getCSSClasses()}");
		$ret = "<div {$this->getAttributes()}>";
		return $ret;
	}
	
	public function renderFoot()
	{
		return "</div>";
	}

	//! Returns whether this Tab is selected.
	public function getSelected()
	{
		return $selected;
	}

	//! Sets the selected status of the Tab.
	public function setSelected($selected)
	{
		$this->selected = $selected;
	}
}
?>
