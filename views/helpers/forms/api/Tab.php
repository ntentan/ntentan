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
