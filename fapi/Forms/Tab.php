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
 *   along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


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