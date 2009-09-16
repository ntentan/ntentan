<?php
include_once "SelectionList.php";

//! A sub class of the SelectionList class which populates its self with
//! data returned from a database query. The query is supposed to return
//! two columns. The first column is supposed to contain the internal value and the second
//! field is supposed to contain the value that is to be displayed to the user.
//! An example query may be
//! \code
//! SELECT company_id, company_name FROM companies
//! \endcode
//! \ingroup Form_API
//!
class DatabaseColumnField extends SelectionList
{
	//! The constructor
	//! \param $label The label for the field
	//! \param $name The name of the field
	//! \param $description The description for the field
	//! \param $query The query that should be run for this field.
	public function __construct($label="",$name="",$description="",$query="")
	{
		parent::__construct($label,$name,$description);
		if($query!="")
		{
			$this->setQuery($query);
		}
	}
	
	//! Sets the query for this Field.
	public function setQuery($query)
	{
		$result = mysql_query($query) or die($query);
		if(mysql_num_rows($result)>0)
		{
			while($row=mysql_fetch_array($result))
			{
				$this->addOption($row[1],$row[0]);
			}
		}
	}
}

?>
