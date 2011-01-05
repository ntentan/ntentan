<?php
namespace ntentan\utils;

class Janitor
{
    public static function cleanHtml($string, $strip = false)
    {
        if($strip === false)
        {
            return htmlspecialchars($string);
        }
        else
        {
            return strip_tags($str);
        }
    }

    public static function unquote($string)
    {
        return \stripcslashes($string);
    }
}