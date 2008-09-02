<?php
class MonthField extends SelectionList
{
	public function __construct($label="",$name="",$descriptiom="")
	{
		parent::__construct($label,$name,$description);
		$this->addOption("January","01");
		$this->addOption("February","02");
		$this->addOption("March","03");
		$this->addOption("April","04");
		$this->addOption("May","05");
		$this->addOption("June","06");
		$this->addOption("July","07");
		$this->addOption("August","08");
		$this->addOption("September","09");
		$this->addOption("October","09");
		$this->addOption("November","11");
		$this->addOption("Deceber","12");
	}
}
?>