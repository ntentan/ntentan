<?php
namespace ntentan\utils;

/**
 * A utility to allow for easy logging. Opens log files and keeps their handes
 * open till the end of the session. This prevents a file being open multiple
 * times during the runtime of the session.
 * 
 * @author Ekow Abaka Ainooson
 */
class Logger
{
    /**
     * The default file to use for logging when no other log file is specified.
     * @var string
     */
    const DEFAULT_LOG_FILE = 'logs/application.log';
    
    private static $logFiles = array();
    
    private static function getFile($file = Logger::DEFAULT_LOG_FILE)
    {
        if(!isset(self::$logFiles[$file]))
        {
            if(is_writable($file))
            {
                self::$logFiles[$file] = fopen($file, 'a');
            }
            else
            {
                return false;
            }
        }
        return self::$logFiles[$file];
    }
    
    public static function log($message, $file = Logger::DEFAULT_LOG_FILE)
    {
        $fileHandle = self::getFile($file);
        if($fileHandle !== false)
        {
            fputs($fileHandle, date("[Y-m-d H:i:s]") . " $message\n");
            return true;
        }
        else
        {
            return false;
        }
    }
}
