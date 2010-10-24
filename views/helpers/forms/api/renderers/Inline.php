<?php
namespace ntentan\views\helpers\forms\api\renderers;

use \ntentan\views\helpers\forms\api\Element;

class Inline extends Renderer
{

    /**
     * The default renderer head function
     *
     */
    public function head()
    {
    
    }
    
    /**
     * The default renderer body function
     *
     * @param $element The element to be rendererd.
     */
    public function element($element, $showfields=true)
    {
    	$ret = "";
    	// Ignore Hidden Fields
    	if($element->getType()=="HiddenField")
    	{
    		return $element->render();
    	}
    
        $ret .= "<div class='fapi-element-div' ".($element->getId()==""?"":"id='".$element->getId()."_wrapper'")." ".$element->getAttributes(Element::SCOPE_WRAPPER).">";
        
        if(!$element->isContainer)
        {
            $ret .= "<label class='fapi-label'>".$element->getLabel();
            if($element->getRequired() && $element->getLabel()!="" && $element->getShowField())
            {
                $ret .= "<span class='fapi-required'>*</span>";
            }
            $ret .= "</label>";
        }
    
    	$ret .= "<div class='fapi-message' id='".$element->getId()."-fapi-message'></div>";
    
        if($element->hasError())
        {
            $ret .= "<div class='fapi-error'>";
            $ret .= "<ul>";
            foreach($element->getErrors() as $error)
            {
                $ret .= "<li>$error</li>";
            }
            $ret .= "</ul>";
            $ret .= "</div>";
        }
    
        if($element->getType()=="Field")
        {
            if($element->getShowField())
            {
                $ret .= "<div>" . $element->render() . "</div>";
            }
            else
            {
                $ret .= $element->getDisplayValue();
                $ret .= "<input type='hidden' name='".$element->getName()."' value='".$element->getValue()."'/>";
            }
        }
        else
        {
            $ret .= $element->render();
        }
    
        if($element->getType()!="Container" && $element->getShowField())
        {
            if($element->getDescription() != "")
            {
                $ret .= "<div ".($element->getId()==""?"":"id='".$element->getId()."_desc'")." class='fapi-description'>".$element->getDescription()."</div>";
            }
        }
        $ret .= "</div>";
    
        return $ret;
    }
    
    /**
     * The foot of the default renderer.
     *
     */
    public function foot()
    {
    
    }
    
    public function type()
    {
        return "inline";
    }
}

