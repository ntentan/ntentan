<?php
namespace ntentan;

class Config
{
    private static $config;
    
    private static $contexts = [];
    private static $context = 'default';
    
    public static function init($path)
    {
        self::$config = ['default' => self::parseDir($path, true)];
        foreach(self::$contexts as $context) {
            self::$config[$context] = array_merge(
                self::$config['default'], self::parseDir("$path/$context", false)
            );
        }
    }
    
    private static function parseDir($path, $contexts) 
    {
        $config = [];
        $files = scandir($path);
        foreach($files as $file) {
            if(fnmatch("*.conf.php", $file)) {
                self::readFile($path, $file, $config);
            }
            else if(is_dir("$path/$file") && $file != '.' && $file != '..') {
                self::$contexts[] = $file;
            }
        } 
        
        return $config;
    }
    
    private static function readFile($path, $file, &$config)
    {
        $prefix = substr($file, 0, -9);
        $fileContents = require("$path/$file");
        if(is_array($fileContents)) {
            foreach($fileContents as $key => $value) {
                $config["$prefix.$key"] = $value;
            }
            $config[$prefix] = $fileContents;
        }
    }
    
    public static function get($key)
    {
        return self::$config[self::$context][$key];
    }
    
    public static function setContext($context)
    {
        self::$context = $context;
    }
    
    public static function dump()
    {
        return self::$config;
    }
    
    public static function set($key, $value)
    {
        $exploded = explode('.', $key);
        if(count($exploded) == 2) {
            self::$config[self::$context][$exploded[0]][$exploded[1]] = $value;
        } 
        self::$config[self::$context][$key] = $value;
    }
}
