<?php
namespace ntentan\sessions;

use ntentan\Ntentan;

class Manager
{
    public static $expiry = 86400;
    
    public static function start($store)
    {
        if($store != '')
        {   
            $handlerClass = "ntentan\\sessions\\stores\\" . Ntentan::camelize($store) . 'Store';
            $handler = new $handlerClass;
            
            $return = session_set_save_handler(
                array($handler, 'open'), 
                array($handler, 'close'), 
                array($handler, 'read'), 
                array($handler, 'write'), 
                array($handler, 'destroy'), 
                array($handler, 'gc')
            );
            register_shutdown_function('session_write_close');
        }
        session_start();
    }
        
}