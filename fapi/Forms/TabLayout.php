<?php
class TabLayout extends Container
{
	protected $tabs = array();
	
	/**
	 * Constructor for the Tab Layout.
	 * 
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Adds a tab to the tab layout.
	 * @param $tab The tab to be added to the tab layout.
	 * 
	 */
	public function add($tab)
	{
		array_push($this->tabs,$tab->getLegend());
		array_push($this->elements,$tab);
		$tab->setMethod($this->getMethod());
		if(count($this->tabs)==1)
		{
			$tab->addCSSClass("fapi-tab-seleted");
		}
		else
		{
			$tab->addCSSClass("fapi-tab-unselected");
		}
	}
	
	/**
	 * Renders all the tabs.
	 */
	public function render()
	{
		print "<ul class='fapi-tab-list ".$this->getCSSClasses()."'>";
		for($i=0; $i<count($this->tabs); $i++)
		{
			print "<li ".($i==0?"class='fapi-tab-selected'":"").">".$this->tabs[$i]."</li>";
		}
		print "</ul>";
		foreach($this->elements as $element)
		{
			$element->render();
		}
	}
}
?>