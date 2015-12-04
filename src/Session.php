<?php

namespace ntentan;

class Session
{
    public static function start()
    {
        session_start();
    }
    
    public static function get($key)
    {
        if(isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
        return null;
    }
    
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    public static function reset()
    {
        $_SESSION = [];
    }
}