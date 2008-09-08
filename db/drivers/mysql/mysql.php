<?php
require_once "db/Database.php";
require_once "mysql_result.php";

/**
 * MySQL driver for the Database.
 */
class mysql extends Database
{
	
	private $link;
	/**
     * Creates a new mysql database driver connection. The parameters for the
     * connection are encoded as a structured array. The format for the 
     * structured query follows
     * <code>
     * array();
     * </code>
     */
	public function createConnection($params)
	{
		$this->link = mysql_connect($params['host'], $params['username'], $params['password']);
		if(!$this->link)
		{ 
			throw new Exception("Error connecting to MySQL database!");
		}
		else
		{
			return true;
		}
	}
	
	public function selectDatabase($database)
	{
		$selected = mysql_select_db($database, $this->link);
		if(!$selected) throw new Exception("Invalid database selected");
	}
	
	public function query($query)
	{
		$result = mysql_query($query, $this->link);
		return new MysqlResult($result);
	}
	
	public function createTable($table)
	{
		
	}
}
?>