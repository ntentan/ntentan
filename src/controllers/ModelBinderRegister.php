<?php

namespace ntentan\controllers;

use ntentan\panie\InjectionContainer;

/**
 * 
 *
 * @author ekow
 */
class ModelBinderRegister
{
    private static $binders = [];
    private static $customBinderInstances = [];
    private static $defaultBinderClass;
    
    private static function getCustomBinder($binder)
    {
        if(!isset(self::$customBinderInstances[$binder])) {
            self::$customBinderInstances[$binder] = new $binder();
        }
        return self::$customBinderInstances[$binder];
    }
    
    public static function setDefaultBinderClass($defaultBinderClass)
    {
        self::$defaultBinderClass = $defaultBinderClass;
    }
    
    public static function getDefaultBinderClass()
    {
        return self::$defaultBinderClass;
    }
    
    public static function register($type, $binder) 
    {
        self::$binders[$type] = $binder;
    }
    
    public static function get($type)
    {
        if(isset(self::$binders[$type])) {
            return self::getCustomBinder(self::$binders[$type]);
        } else {
            return InjectionContainer::singleton(ModelBinderInterface::class);
        }
    }
}
