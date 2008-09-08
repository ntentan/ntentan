<?php
require_once "db/DatabaseResult.php";

class MysqlResult extends DatabaseResult
{
	protected $result;
	
	public function __construct($result)
	{
		$this->result = $result;
	}
	
	public function getNumRows()
	{
		return mysql_num_rows($this->result);
	}
	
	public function getNumFields()
	{
		return mysql_num_fields($this->result);
	}
	
	public function fetchRow()
	{
		return mysql_fetch_row($this->result);
	}
}
?>