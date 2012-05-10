<?php
namespace ntentan\sessions;

use ntentan\Ntentan;

class Manager
{
    public static $lifespan = 86400;
    private static $handler;
    
    public static function start($store)
    {
        if($store != '')
        {
            
            $handlerClass = "ntentan\\sessions\\stores\\" . Ntentan::camelize($store) . 'Store';
            self::$handler = new $handlerClass;
            $configExpiry = Ntentan::$config[CONTEXT]['session_lifespan'];
            self::$lifespan = $configExpiry > 0 ? $configExpiry : self::$lifespan;
            
            $return = session_set_save_handler(
                array(self::$handler, 'open'), 
                array(self::$handler, 'close'), 
                array(self::$handler, 'read'), 
                array(self::$handler, 'write'), 
                array(self::$handler, 'destroy'), 
                array(self::$handler, 'gc')
            );
            register_shutdown_function('session_write_close');
        }
        session_start();
    }
    
    public static function isNew()
    {
        return self::$handler->isNew();
    }
}