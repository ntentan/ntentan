<?php
include_once "Field.php";

/**
 * Implementation of a regular text field. This field is used to
 * accept single line text input from the user.
 *
 */
class TextField extends Field
{
	protected $max_lenght;
	protected $type;
	protected $max_val;
	protected $min_val;
	protected $regexp;
	
	public function __construct($label="",$name="",$description="",$value="")
	{
		Field::__construct($name,$value);
		Element::__construct($label, $description);
		$this->type = "TEXT";
	}
		
	public function render()
	{
		$this->addAttribute("class","fapi-textfield ".$this->getCSSClasses());
		$this->addAttribute("type","text");
		$this->addAttribute("name",$this->getName());
		$this->addAttribute("id",$this->getId());
		$this->addAttribute("value",$this->getValue());
		print "<input {$this->getAttributes()} >"; //class="fapi-textfield '.$this->getCSSClasses().'" type="text" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getValue().'" />';
	}
	
	public function getCSSClasses()
	{
		return Field::getCSSClasses().strtolower($this->type);
	}
	
	public function setRegExp($regexp)
	{
		$this->type="REGEXP";
		$this->regexp = $regexp;
	}
	
	/**
	 * Sets the text field as text for validation purposes.
	 *
	 * @param $max_lenght The maximum lenght of the text in the field.
	 */
	public function setAsText($max_lenght = 0)
	{
		$this->type = "TEXT";
		$this->max_lenght = $max_lenght; 
	}
	
	/**
	 * Sets the text field as numeric for validation purposes.
	 *
	 * @param $min_val The smallest value the field can take (optional).
	 * @param $max_val The largest value the field can take (optional).
	 */
	public function setAsNumeric($min_val=0, $max_val=0)
	{
		$this->type = "NUMERIC";
		$this->min_val = $min_val;
		$this->max_val = $max_val;
	}
	
	public function validate()
	{
		
		//Perform validation on the parent class.  
		if(!parent::validate())
		{
			return false;
		}
		
		if($this->getValue()=="") return true;
		
		//Perform validation on text fields
		if($this->type=="TEXT")
		{
			if(!is_numeric($this->getValue()))
			{
				if($this->max_lenght>0 && strlen($this->getValue())>$this->max_lenght)
				{
					$this->error = true;
					array_push($this->errors,"The lenght of the text in this field cannot exceed $this->max_lenght");
					return false;
				}
			}
			return true;
		}
		
		//Perform validation of the numeric fields
		else if($this->type=="NUMERIC")
		{
			if(is_numeric($this->getValue()))
			{
				if($this->min_val!=$this->max_val)
				{
					if(!($this->getValue()>=$this->min_val && $this->getValue()<=$this->max_val))
					{
						$this->error = true;
						array_push($this->errors, "The value of the number in this field must be between $this->min_val and $this->max_val");
						return false;
					}
				}
			}
			else
			{
				$this->error = true;
				array_push($this->errors, "The value of this field is expected to be a number.");
				return false;
			}
			return true;
		}

		else if($this->type=="REGEXP")
		{
			if(eregi($this->regexp, $this->getValue()))
			{
				return true;
			}
			else
			{
				$this->error = true;
				return false;
			}
		}
	}
}
?>