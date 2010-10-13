<?php
namespace ntentan\caching;

use ntentan\Ntentan;

abstract class Cache
{
    const DEFAULT_TTL = 360;
    private static $instance = null;
    
    private static function instance()
    {
        if(Cache::$instance == null)
        {
            require "config/site.php";
            $class = Ntentan::camelize($site["cache"]);
            require "$class.php";
            $class = "ntentan\\caching\\$class";
            Cache::$instance = new $class();
        }
        return Cache::$instance; 
    }
    
    public static function add($key, $object, $ttl = 3600)
    {
        Cache::instance()->addImplementation($key, $object, $ttl);
    }
    
    public static function get($key)
    {
        return Cache::instance()->getImplementation($key);
    }
    
    public static function exists($key)
    {
        return Cache::instance()->existsImplementation($key);
    }
    
    abstract protected function addImplementation($key, $object, $ttl);
    abstract protected function getImplementation($key);
    abstract protected function existsImplementation($key);
}
