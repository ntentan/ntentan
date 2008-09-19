<?php
/*   Copyright 2008, James Ainooson 
 *
 *   This file is part of Ntentan.
 *
 *   Ntentan is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Ntentan is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Ntentan.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


/**
 * An abstract databas class which provides a simple method through
 * which users can connect to their databases.
 * 
 * @author James Ekow Abaka Ainooson
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