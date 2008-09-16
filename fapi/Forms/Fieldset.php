<?php
/*
 *  
 *  Copyright 2008, James Ainooson 
 *
 *  This file is part of Ntentan.
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