<?php
/**
 * The default renderer head function
 *
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