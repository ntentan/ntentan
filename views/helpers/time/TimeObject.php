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
	
	public function time()
	{
	    return date("g:i a");
	}
	
	public function friendly()
	{
	    $date = date("Y-m-d", $this->timestamp);
	    if($date == date("Y-m-d"))
	    {
	        return "Today";
	    }
	    elseif ($date == date("Y-m-d", time()- 86400))
	    {
	        return "Yesterday";
	    }
        elseif ($date == date("Y-m-d", time()- (86400 * 2)))
        {
            return "Two days ago";
        }
        elseif ($date == date("Y-m-d", time()- (86400 * 3)))
        {
            return "Three days ago";
        }
        else
        {
            return date("jS F, Y");
        }
	}
}