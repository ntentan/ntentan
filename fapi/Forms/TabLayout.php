<?php
/*   Copyright 2008, James Ainooson 
 *
 *   This file is part of Ntentan.
 *
 *   Ntentan is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Ntentan is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Ntentan.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


/**
 * A special container for containing Tab elements. This makes it possible
 * to layout form elements in a tab fashion. The TabLayout container takes
 * the tab as the elements it contains.
 *
 */
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
		$tab->addAttribute("id","fapi-tab-".strval(count($this->tabs)-1));
		if(count($this->tabs)==1)
		{
			$tab->addCSSClass("fapi-tab-seleted");
		}
		else
		{
			$tab->addCSSClass("fapi-tab-unselected");
		}
	}
	
	public function validate()
	{
		$retval = true;
		foreach($this->elements as $element)
		{
			if($element->validate()==false) 
			{
				$retval=false;
				$element->addCSSClass("fapi-tab-error");
				$this->error = true;
				array_push($this->errors,"There were some errors on the ".$element->getLegend()." tab");
			}
		}
		return $retval;
	}
	
	/**
	 * Renders all the tabs.
	 */
	public function render()
	{
		print "<ul class='fapi-tab-list ".$this->getCSSClasses()."'>";
		for($i=0; $i<count($this->tabs); $i++)
		{
			print "<li id='fapi-tab-top-$i' onclick='fapiSwitchTabTo($i)' class='".($i==0?"fapi-tab-selected":"fapi-tab-unselected")."'>".$this->tabs[$i]."</li>";
		}
		print "</ul><p style='clear:both' ></p>";
		foreach($this->elements as $element)
		{
			$element->render();
		}
	}
}
?>