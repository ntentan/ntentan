<?php
include_once "SelectionList.php";

//! A sub class of the SelectionList class which populates its self with
//! data returned from a database query. Unlike the DatabaseColumnField,
//! the DatabaseColumnField2 class doesnt require an explicit query. The
//! class takes as input the name of the database table, the name of the
//! field to take the values from and the name of the primary key field.
//!
//! <b>Note for Korianda</b>
//! Using this field in a form automatically resolves foreing key dependencies
//! during CVS imports. If any dependency is not met during import, the
//! import is cancelled.
//!
class DatabaseColumnField2 extends DatabaseColumnfield
{
	//! The database schema.
	protected $database_schema;
	protected $database_table;
	protected $value_field;
	protected $primary_key_field;
	
	//! The constructor
	//! \param $label The label for the field
	//! \param $name The name of the field
	//! \param $description The description for the field
	//! \param $database_schema The name of the database schema in which the data is stored
	//! \param $database_table The name of the database table in which the data is stored
	//! \param $value_field The name of the field or the table column to use as the values for this list.
	//! \param $primary_key_field The name of the primary_key_field.
	//!
	public function __construct($label="",$name="",$description="",$database_schema="",$database_table="",$value_field="",$primary_key_field="")
	{
		parent::__construct($label,$name,$description);
		$this->database_schema = $database_schema;
		$this->database_table = $database_table;
		$this->value_field = $value_field;
		$this->primary_key_field = $primary_key_field;
		
		$query = "SELECT $primary_key_field, $value_field FROM $database_schema.$database_table";
		$this->setQuery($query);
	}
	
	public function getForeing()
	{
		return true;
	}
	
	public function getDatabaseSchema()
	{
		return $this->database_schema;
	}
	
	public function getDatabaseTable()
	{
		return $this->database_table;
	}
	
	public function getValueField()
	{
		return $this->value_field;
	}
}

?>
