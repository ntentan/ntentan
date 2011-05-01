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
}