<?php
include_once("TextField.php");
/**
 * A text field for accepting email addresses. This field validates
 * the email addresses using a regular expression.
 * \ingroup Form_API
 */
class EmailField extends TextField
{
	public function __construct($label="",$name="",$description="",$value="")
	{
		parent::__construct($label,$name,$description,$value);
		$this->setRegExp('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$');
		/*$this->addJsValidation
		(array(
			"func"=>"fapiCheckRegexp",
			"regexp"=>Field::prepareMessage('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i'),
			"message"=>Field::prepareMessage("This is not a valid email address")
			)
		);*/		
	}
	
	public function validate()
	{
		if(!parent::validate())
		{
			array_push($this->errors, "Invalid email address entered");
			$this->error = true;
			return false;
		}
		else
		{
			return true;
		}
	}
}
?>
