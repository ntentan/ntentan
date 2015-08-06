<?php
namespace ntentan;

class Config
{
    private static $config = [
        'default' => []
    ];
    
    private static $contexts = [];
    private static $context = 'default';
    
    public static function init($path)
    {
        self::$config['default'] = self::parseDir($path, true);
        foreach(self::$contexts as $context) {
            self::$config[$context] = array_merge(
                self::$config['default'], self::parseDir("$path/$context", false)
            );
        }
    }
    
    private function parseDir($path, $contexts) 
    {
        $config = [];
        $files = scandir($path);
        foreach($files as $file) {
            if(fnmatch("*.conf.php", $file)) {
                self::readFile($path, $file, $config);
            }
            else if(is_dir($file) && $file != '.' && $file != '..') {
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
        return self::$config[$key];
    }
}
