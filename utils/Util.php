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

namespace ntentan\utils;

use \ReflectionClass;

/**
 * An abstract class to be used as the base class for all operations in scripted
 * utilities.
 * @author ekow
 *
 */
class Util
{
    protected $shortOptionsMap = array();
    
    public function execute($arguments)
    {
        $operation = array_shift($arguments);
        $options = $this->parseOptions($arguments);
        if(method_exists($this, $operation))
        {
            $class = new ReflectionClass($this);
            $class->getMethod($operation)->invoke($this, $options);
        }
        else
        {
            echo "Unknown operation";
        }
    }
    
    protected function getUserResponse($question, $answers=null, $default=null, $notNull = false)
    {
        echo $question;
        if(count($answers) > 0) echo " (" . implode("/", $answers) . ")";
        echo " [$default]: ";
        $response = str_replace(array("\n", "\r"),array("",""),fgets(STDIN));

        if($response == "" && $notNull === true)
        {
            echo "A value is required.\n";
            return $this->getUserResponse($question, $answers, $default, $notNull);
        }
        else if($response == "")
        {
            return strtolower($default);
        }
        else
        {
            if(count($answers) == 0)
            {
                return $response;
            }
            foreach($answers as $anwser)
            {
                if(strtolower($answer) == strtolower($response))
                {
                    return strtolower($answer);
                }
            }
            echo "Please provide a valid answer.\n";
            return $this->getUserResponse($question, $answers, $default, $notNull);
        }
    }
    
    protected function substitute($data, $text)
    {
        $values = array();
        $keys = array();
        foreach($data as $key => $value)
        {
            $keys[] = "{" . $key . "}";
            $values[] = $value;
        }
        return str_replace($keys, $values, $text);
    }
    
    protected function parseOptions($arguments)
    {
        $options = array();
        for($i = 0; $i < count($arguments); $i++)
        {
            if(preg_match("/--(?<option>.*)/", $arguments[$i], $matches))
            {
                if(preg_match("/--(.*)/", $arguments[$i+1]) > 0)
                {
                    $options[$matches["option"]] = true;
                }
                else
                {
                    $options[$matches["option"]] = $arguments[++$i];
                }
            }
            else if(preg_match("/-(?<option>.{1})/", $arguments[$i], $matches))
            {
                $options[$this->shortOptionsMap[$matches["option"]]] = true;
            }
            else
            {
                $options["stand_alone_values"][] = $arguments[$i];
            }
        }
        return $options;
    }
}
