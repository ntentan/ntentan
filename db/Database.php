<?php
/**
 * An abstract databas class which provides a simple method through
 * which users can connect to their databases.
 */
abstract class Database
{
	/**
     * The function to make a database connection.
     */
	public static function connect($driver="mysql",$params=array())
	{
		require_once("drivers/$driver/$driver.php");
		$db = new $driver;
		$db->createConnection($params);
		return $db;
	}
	
	/**
     * A method called by the database connection interface to establish a database connection.
     */
	abstract protected function createConnection($params);
	
	/**
     * A method to select which database schema to use.
     */
	abstract public function selectDatabase($database);
	
	/**
     * A method to query the database.
     */
	abstract public function query($query);
	
	/**
     * A method to create a database table. This method takes a table definition.
     * The table definition is a nested array structure which describes the 
     * database table in a generic form.
     *  
     */
	abstract public function createTable($table_definition);
}
?>