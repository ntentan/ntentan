<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\views\helpers\forms\api;

/**
 * A special container for containing Tab elements. This makes it possible
 * to layout form elements in a tab fashion. The TabLayout container takes
 * the tab as the elements it contains.
 * @ingroup Form_API
 */
class Tabs extends Container
{
	protected $tabs = array();

	/**
	 * Adds a tab to the tab layout.
	 * @param $tab The tab to be added to the tab layout.
	 */
	public function add($tab)
	{
		array_push($this->tabs,$tab->getLegend());
		array_push($this->elements,$tab);
		$tab->setMethod($this->getMethod());
		$tab->addAttribute("id","fapi-tab-".strval(count($this->tabs)-1));
		$tab->parent=$this;
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
	
    public function renderHead()
    {
        return "<div class='fapi-tabs'>";
    }
	
	public function renderFoot()
	{
		$ret = "<ul class='fapi-tab-list ".$this->getCSSClasses()."'>";
		for($i=0; $i<count($this->tabs); $i++)
		{
			$ret .= "<li id='fapi-tab-top-$i' onclick='fapiSwitchTabTo($i)' class='".($i==0?"fapi-tab-selected":"fapi-tab-unselected")."'>".$this->tabs[$i]."</li>";
		}
		$ret .= "</ul><p style='clear:both' ></p>";
		foreach($this->elements as $element)
		{
			$ret .= $element->render();
		}
		$ret .= "</div>";
		return $ret;
	}
}
