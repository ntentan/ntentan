<?php
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