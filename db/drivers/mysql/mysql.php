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
 *   along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 */



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