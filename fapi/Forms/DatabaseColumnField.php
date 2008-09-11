<?php
include_once "SelectionList.php";

class DatabaseColumnField extends SelectionList
{
	public function __construct($label="",$name="",$description="",$query="")
	{
		parent::__construct($label,$name,$description);
		if($query!="")
		{
			$this->setQuery($query);
		}
	}
	
	public function setQuery($query)
	{
		$result = mysql_query($query);
		while($row=mysql_fetch_array($result))
		{
			$this->addOption($row[1],$row[0]);
		}
	}
}

?>