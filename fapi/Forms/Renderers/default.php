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
 *   along with Ntentan.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * The default renderer head function
 * @category  Renderers
 */
function default_renderer_head()
{
	
}

/**
 * The default renderer body function
 *
 * @param $element The element to be rendererd.
 */
function default_renderer_element($element, $showfields=true)
{
	/*if($element->getType()=="Field")
	{*/
		print "<div class='fapi-element-div'>";
		
		if($element->getType()=="Field")
		{
			print "<div class='fapi-label'>".$element->getLabel();
			if($element->getRequired() && $element->getLabel()!="" && $showfields)
			{	
				print "<span class='fapi-required'>*</span>";
			}
			print "</div>";
		}
		
		if($element->hasError())
		{
			print "<div class='fapi-error'>";
			print "<ul>";
			foreach($element->getErrors() as $error)
			{
				print "<li>$error</li>";
			}
			print "</ul>";
			print "</div><p></p>";
		}
	/*}*/
	
	if($element->getType()=="Field")
	{
		if($showfields)
		{
			$element->render();
		}
		else
		{
			print $element->getDisplayValue();
		}
	}
	else
	{
		$element->render();
	}
	
	if($element->getType()!="Container" && $showfields)
	{
		print "<div class='fapi-description'>".$element->getDescription()."</div>";
	}
	/*if($element->getType()=="Field")
	{*/
		print "</div>";
	/*}*/		
}

/**
 * The foot of the default renderer.
 *
 */
function default_renderer_foot()
{
	
}

?>