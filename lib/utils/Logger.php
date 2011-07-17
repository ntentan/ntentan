<?php
namespace ntentan\utils;

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
    		self::$logFiles[$file] = fopen($file, 'a');
    	}
    	return self::$logFiles[$file];
    }
    
	public static function log($message, $file = Logger::DEFAULT_LOG_FILE)
	{
		fputs(self::getFile($file), date("[Y-m-d H:i:s]") . $message);
	}
}
