<?php
include_once "TextField.php";

/**
 * A text field for accepting the number of hours in a day. Performs
 * all validations internally. If the number of hours specified is more
 * than 24 it flags an error.
 * \ingroup Form_API
 */
class DayHoursField extends TextField
{
	public function __construct($label="",$name="",$description="",$value="")
	{
		parent::__construct($label,$name,$description,$value);
		$this->setAsNumeric(0,24);
	}
}
?>
