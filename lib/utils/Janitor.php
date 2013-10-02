<?php
namespace ntentan\utils;

/**
 * 
 */
class Janitor
{
    public static function cleanHtml($string, $strip = false)
    {
        if(!is_string($string)) return;
        if($strip === false)
        {
            return htmlspecialchars($string);
        }
        else
        {
            return strip_tags($string);
        }
    }

    public static function unquote($string)
    {
        return \stripcslashes($string);
    }
    
    public static function sanitizeArray($array, $method = 'cleanHtml')
    {
        $modeMethod = new \ReflectionMethod('\\ntentan\\utils\\Janitor', $method);
        foreach($array as $key => $value)
        {
            if(is_array($value))
            {
                $array[$key] = self::sanitizeArray($value, $method);
            }
            else
            {
                $array[$key] = $modeMethod->invoke(null, $value);
            }
        }
        
        return $array;
    }
}