<?php
namespace ntentan\views\helpers\time;

class TimeObject
{
	private $timestamp;
	
	public function __construct($timestamp)
	{
		$this->timestamp = $timestamp;
	}
	
	public function format($format)
	{
		return date($format, $this->timestamp);
	}
}