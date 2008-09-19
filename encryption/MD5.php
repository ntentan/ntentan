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

class MD5 extends Cipher
{
	/**
	 * @todo Sets parameters specific to the Mcrypt library
	 */
	public function setParams($params=array())
	{
		
	}
	
	public function encrypt($text)
	{
		return md5($text);
	}
	
	public function decrypt($text)
	{
		return false;
	}
}
?>