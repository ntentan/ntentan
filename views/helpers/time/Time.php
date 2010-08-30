<?php
namespace ntentan\views\helpers\time;

use ntentan\views\helpers\Helper;

class Time extends Helper
{
	private $timestamp;
	
	public function parse($time)
	{
		return new TimeObject(strtotime($time));
	}	
}
