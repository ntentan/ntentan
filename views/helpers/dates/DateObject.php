<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


namespace ntentan\views\helpers\dates;

class DateObject
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
	    return date("g:i a", $this->timestamp);
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
            return date("jS F, Y", $this->timestamp);
        }
	}
}