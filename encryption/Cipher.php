<?php
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