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

abstract class Cipher
{
	
	private $key; 
	
	abstract public function setParams($params = array());
	abstract public function encrypt($text);
	abstract public function decrypt($text);

	public static function quickEncrypt($text,$driver="MD5",$key="",$params=array())
	{
		require_once ("$driver.php");
		$crypt = new $driver;
		$crypt->setParams($params);
		$crypt->setKey($key);
		return $crypt->encrypt($text);
	}
	
	public static function quickDecrypt($text,$driver="MD5",$key="",$params=array())
	{
		require_once ("$driver.php");
		$crypt = new $driver;
		$crypt->setParams($params);
		$crypt->setKey($key);
		return $crypt->decrypt($text);		
	}
	
	public function setKey($key)
	{
		$this->key=$key;
	}
	
	public function getKey($key)
	{
		return $this->key;
	}
	
}
?>