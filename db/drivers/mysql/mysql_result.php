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