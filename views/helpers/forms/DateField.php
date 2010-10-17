<?php
namespace ntentan\views\helpers\forms;

class DateField extends TextField
{
    public function __construct($label="",$name="",$description="")
    {
        parent::__construct($label,$name,$description);
    }

    public function getDisplayValue()
    {
        return $this->value==""?"":date("jS F, Y",$this->value);
    }

    public function render()
    {
    	$this->addCSSClass( "fapi-textfield");
    	$this->addAttribute( "class" , "fapi-sidefield ".$this->getCSSClasses());
    	$this->addAttribute( "id" , $this->getId());
    	$this->addAttribute( "name" , $this->getName());
    	$this->addAttribute( "value" , $this->getValue()!==""?date("m/d/Y",(int)$this->getValue()) : "" );
    	$id = $this->getId();
        return "<input ".$this->getAttributes()." /><input class='fapi-sidebutton' type='button' value='..' onclick=\"$('#date-picker-$id').datepicker({altField:'#$id',changeYear:true,changeMonth:true,changeDate:true,maxDate:null,yearRange:'1900:2300',dateFormat:'mm/dd/yy'}).slideToggle()\" /><div class='fapi-datepicker' id='date-picker-$id'></div>";
    }

    public function setValue($value)
    {
    	//if($value==0) throw new Exception("Try");
        if(is_numeric($value))
		{
			parent::setValue($value);
		}
		else
		{
			if(strlen($value)>0) parent::setValue(strtotime($value)); else parent::setValue("");
		}
		return $this;
    }
}

