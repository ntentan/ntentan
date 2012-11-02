<?php
/**
 * Source file for the abstract cache class
 *  
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @category Caching
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\caching;

use ntentan\exceptions\MethodNotFoundException;
use ntentan\Ntentan;

/**
 * Abstract caching class which also acts as a caching singleton. 
 * 
 * This class forms the base through which all other caching classes are 
 * written. It also implements a sigleton interface which means that the 
 * applications never directly instantiate their own copies of the cache class. 
 * The implementation to be used when the singleton is invoked is mostly 
 * specified through the ntentan framework's configuration files.
 *
 * 
 * @abstract
 */
abstract class Cache
{
    /**
     * The default time to live for all items added to the cache.
     * 
     * @var int
     */
    private static $defaultTimeToLive = 3600;
    
    /**
     * The singleton instance.
     * 
     * @var Cache
     */
    private static $instance = null;

    /**
     * Generates an instance of a Cache class. 
     * 
     * This method is here to provide a more transparent interface through 
     * which caching classes could be instantiated. The implementation to be
     * used is determined from the ntentan configuration files and assigned
     * to the Ntentan::$cacheMethod property.
     * 
     * @return Cache
     */
    private static function instance()
    {
        if(Cache::$instance == null)
        {
            $class = Ntentan::camelize(Ntentan::$cacheMethod);
            require_once "$class.php";
            $class = "ntentan\\caching\\$class";
            Cache::$instance = new $class();
        }
        return Cache::$instance; 
    }
    
    /**
     * A static wrapper around the Cache class to provide an interface through
     * which methods on the caching backend could be called.
     * 
     * @param string $method
     * @param array $arguments
     */
    public static function __callstatic($method, $arguments)
    {
        $instance = Cache::instance();
        if(method_exists($instance, $method))
        {
            $method = new \ReflectionMethod($instance, $method);
            return $method->invokeArgs($instance, $arguments);
        }
        else
        {
            throw new MethodNotFoundException($method);
        }
    }

    /**
     * Store an item into the cache.
     * 
     * Items stored in the cache are stored using a key-value approach. Every item
     * must have a unique key to help identify it. Due to the diverse nature 
     * of the backends it is advisable to keep the keys as plain strings.
     * 
     * @param string $key A unique identifier for the object to be stored.
     * @param mixed $object The object to be stored.
     * @param integer $ttl The time to live of the object. This figure is
     *                     expressed in seconds.
     */
    public static function add($key, $object, $ttl = false)
    {
        if($ttl === false)
        {
            $ttl = self::$defaultTimeToLive;
        }
        
        Cache::instance()->addImplementation($key, $object, $ttl);
    }

    /**
     * Retrieve an item from the cache.
     * 
     * @param    string $key
     * @return   mixed Returns false when the item is not found.
     * @todo     Look at the possibility of making this throw an exception for 
     *           items not found
     */
    public static function get($key)
    {
        return Cache::instance()->getImplementation($key);
    }

    /**
     * Checks if a particular item exists in the cache.
     *
     * @param string $key
     * @return boolean True if the item exists false otherwise
     */
    public static function exists($key)
    {
        return Cache::instance()->existsImplementation($key);
    }
    
    /**
     * Removes an item from the cache.
     * 
     * @param string $key
     */
    public static function remove($key)
    {
        Cache::instance()->removeImplementation($key);
    }
    
    /**
     * Resets the instance of the Cache singleton.
     * 
     * Deletes the Cache object's internal singleton so it could be re instantiated upon
     * the next call Cache object. This method is very necessary when you want to
     * switch between caching backends during the runtime. Note that when the
     * cache is reinstantiated, the data on the old cache backend is not transfered to
     * the new cache backend. The new backend can be specified through the 
     * Ntentan::$cacheMethod property.
     */
    public static function reInstantiate()
    {
        self::$instance = null;
    }

    /**
     * Implementation of the add function by the appropriate caching backend.
     * @param string $key
     * @param mixed $object
     * @param integer $ttl
     */
    abstract protected function addImplementation($key, $object, $ttl);

    /**
     * Implementation of the get function by the appropriate caching backend.
     * @param string $key
     */
    abstract protected function getImplementation($key);
    
    /**
     * Implementation of the exists function by the appropriate caching backend
     * @param string $key
     */
    abstract protected function existsImplementation($key);
    
    /**
     * Implementation of the remove function by the appropriate caching backend
     * @param string $key
     */
    abstract protected function removeImplementation($key);
}
