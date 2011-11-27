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

error_reporting(E_ALL ^ E_NOTICE);

/**
 * A view helper for formatting dates.
 *
 * @author James Ekow Abaka Ainooson
 */
class DatesHelper extends Helper
{
    /**
     * The UNIX timestamp which represents the most recently parsed date.
     * @var integer
     */
	private $timestamp;

    private function internalParse($date)
    {
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
        if(preg_match(
            "/(?<year>\d{4})-(?<first>\d{2})-(?<second>\d{2}) " .
            "(?<hours>\d{2}):(?<minutes>\d{2}):(?<seconds>\d{2})/",
            $date, $matches))
        {
            $year = $matches['year'];
            if($matches['first'] > 12)
            {
                $day = $matches['first'];
                $month = $matches['second'];
            }
            else
            {
                $day = $matches['second'];
                $month = $matches['first'];
            }
            $hours = $matches['hours'];
            $minutes = $matches['minutes'];
            $seconds = $matches['seconds'];
        }
        else if(preg_match("/(?<year>\d{4})-(?<first>\d{2})-(?<second>\d{2})/", $date, $matches))
        {
            $year = $matches['year'];
            if($matches['first'] > 12)
            {
                $day = $matches['first'];
                $month = $matches['second'];
            }
            else
            {
                $day = $matches['second'];
                $month = $matches['first'];
            }
        }
        return strtotime("$year-$month-$day") + ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    /**
     * Internal utility method for selecting a timestamp. This method returns
     * the DatesHelper::timestamp variable if the date parameter is null. This
     * method makes it possible for the helper methods to use either the
     * internally stored timestamp (which is stored by the DatesHelper::parse
     * method) or the date passed directly to the helper method.
     *
     * @param string $date
     * @return integer
     */
    private function selectTimestamp($date = null)
    {
        return $date == null ? $this->timestamp : $this->internalParse($date);
    }

    /**
     * Parse a time in string format and store. Once parsed, all calls to helper
     * methods which do not specify their own dates use the last date which was
     * parsed.
     * 
     * @param string $time
     * @return DatesHelper
     */
	public function help($time)
	{
		$this->timestamp =$this->internalParse($time);
        return $this;
	}

    /**
     * A wrapper arround the PHP date() method. This method however takes the
     * dates in various string formats.
     *
     * @param string $format
     * @param string $date
     * @return string
     */
	public function format($format = 'jS F, Y', $date = null)
	{
		return date($format, $this->selectTimestamp($date));
	}

    /**
     * Returns date in the format 12:00 am
     * 
     * @param string $date
     * @return string
     */
	public function time($date = null)
	{
	    return date("g:i a", $this->selectTimestamp($date));
	}

    /**
     * Provides a nice sentence to represents the date in age terms eg. Three Years,
     * Two days or now. The first argument is a structured array of options.
     * Currently this argument has one option which also has just one possible
     * value. This option is the <code>elaborate_with</code> option which currently
     * only takes the english word <code>ago</code> as its parameter. When this
     * argument is passed, the word 'ag'o is appended to sentences for which it
     * would make sense. For example the outputs with this argument could be
     * (two days ago, one month ago, now, yesterday, three minutes ago ...)
     *
     * @code
     * $this->date->parse(date('Y-m-d))->sentence(array('elaborate_with'=>'ago'));
     * @endcode
     * 
     * @param array $options
     * @param string $date
     * @param string $referenceDate
     * @return string
     */
    public function sentence($date = null, $options = null, $referenceDate = null)
    {
        $timestamp = $this->selectTimestamp($date);
        $now = $referenceDate == null ? time() : $this->internalParse($referenceDate);
        $elapsed = $now - $timestamp;
        
        
        if($elapsed < 10)
        {
            $englishDate = 'now';
        }
        elseif($elapsed >= 10 && $elapsed < 60)
        {
            $englishDate = "$elapsed second" . ($elapsed > 1 ? 's' : '');
        }
        elseif($elapsed >= 60 && $elapsed < 3600)
        {
            $minutes = floor($elapsed / 60);
            $englishDate = "$minutes minute" . ($minutes > 1 ? 's' : '');
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
            $englishDate = "$days day" . ($days > 1 ? 's' : '');
        }
        elseif($elapsed >= 604800 && $elapsed < 2419200)
        {
            $weeks = floor($elapsed / 604800);
            $englishDate = "$weeks week" . ($weeks > 1 ? 's' : '');
        }
        elseif($elapsed >= 2419200 && $elapsed < 31536000)
        {
            $months = floor($elapsed / 2419200);
            $englishDate = "$months month" . ($months > 1 ? 's' : '');
        }
        elseif($elapsed >= 31536000)
        {
            $years = floor($elapsed / 31536000);
            $englishDate = "$years year" . ($years > 1 ? 's' : '');
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
