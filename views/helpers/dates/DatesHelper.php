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

use ntentan\views\helpers\Helper;

class DatesHelper extends Helper
{
    const DEPTH_NONE        = 0;
    const DEPTH_MINUTES     = 1;
    const DEPTH_HOURS       = 2;
    const DEPTH_YESTERDAY   = 3;
    const DEPTH_DAYS        = 4;
    const DEPTH_WEEKS       = 5;
    const DEPTH_MONTHS      = 6;
    const DEPTH_YEARS       = 7;

	private $timestamp;

    private function selectTimestamp($date = null)
    {
        return $date == null ? $this->timestamp : strtotime($date);
    }

	public function parse($time)
	{
		$this->timestamp =strtotime($time);
	}

	public function format($format, $date = null)
	{
		return date($format, $this->selectTimestamp($date));
	}

	public function time($date)
	{
	    return date("g:i a", $this->selectTimestamp($date));
	}

    /**
     * 
     * @return string
     * @deprecated
     */
    public function friendly()
    {
        $date = date("Y-m-d", $this->timestamp);
        if ($date == date("Y-m-d"))
        {
            return "Today";
        }
        elseif ($date == date("Y-m-d", time() - 86400))
        {
            return "Yesterday";
        }
        elseif ($date == date("Y-m-d", time() - (86400 * 2)))
        {
            return "Two days ago";
        }
        elseif ($date == date("Y-m-d", time() - (86400 * 3)))
        {
            return "Three days ago";
        }
        else
        {
            return date("jS F, Y", $this->timestamp);
        }
    }

    public function sentence($date, $options = null)
    {
        $timestamp = $this->selectTimestamp($date);
        $now = time();
        $elapsed = $now - $timestamp;

        if($elapsed < 10)
        {
            $englishDate = 'now';
        }
        elseif($elapsed >= 10 && $elapsed < 60)
        {
            $englishDate = "$elapsed seconds";
        }
        elseif($elapsed >= 60 && $elapsed < 3600)
        {
            $minutes = floor($elapsed / 60);
            $englishDate = "$minutes minutes";
        }
        elseif($elapsed >= 3600 && $elapsed < 86400)
        {
            $hours = floor($elapsed / 3600);
            $englishDate = "$hours hour" . ($hours > 1 ? 's' : '');
        }
        elseif($elapsed >= 86400 && $elapsed < 172800)
        {
            $englishDate = "yesterday";
        }
        elseif($elapsed >= 172800 && $elapsed < 604800)
        {
            $days = floor($elapsed / 86400);
            $englishDate = "$days days";
        }
        elseif($elapsed >= 604800 && $elapsed < 2419200)
        {
            $weeks = floor($elapsed / 604800);
            $englishDate = "$weeks weeks";
        }
        elseif($elapsed >= 2419200 && $elapsed < 31536000)
        {
            $months = floor($elapsed / 2419200);
            $englishDate = "$months months";
        }
        elseif($elapsed >= 31536000)
        {
            $years = floor($elapsed / 31536000);
            $englishDate = "$years years";
        }

        switch($options['elaborate_with'])
        {
            case 'ago':
                if($englishDate != 'now' && $englishDate != 'yesterday')
                {
                    $englishDate .= ' ago';
                }
                break;
        }

        return $englishDate;
    }
}
