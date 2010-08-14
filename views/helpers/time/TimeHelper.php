<?php
class TimeHelper extends Helper
{
	private $timestamp;
	
	public function parse($time)
	{
		return new TimeObject(strtotime($time));
	}	
}
