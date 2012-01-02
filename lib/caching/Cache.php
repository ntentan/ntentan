<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\caching;

use ntentan\Ntentan;

/**
 * Abstract cache class. This class forms the base through which all other
 * caching classes are written. It also implements a sigleton interface which
 * means that the applications never directly instantiate their own copies
 * of the cache class.
 */
abstract class Cache
{
    const DEFAULT_TTL = 3600;
    private static $instance = null;

    /**
     * Generates an instance of a Cache class. This method is here to provide
     * a more transparent interface through which caching classes could be
     * instantiated.
     * 
     * @return Cache
     */
    private static function instance()
    {
        if(Cache::$instance == null)
        {
            $class = Ntentan::camelize(Ntentan::$cacheMethod);
            require "$class.php";
            $class = "ntentan\\caching\\$class";
            Cache::$instance = new $class();
        }
        return Cache::$instance; 
    }

    /**
     * Store an item into the cache.
     * @param string $key A unique identifier for the object to be stored.
     * @param mixed $object The object to be stored.
     * @param integer $ttl The time to live of the object. This figure could be
     *                     expressed in seconds provided the number of secods
     *                     doesn't exceed 2592000. If it exceeds, the value is
     *                     taken as a date expressed in unix timestamps.
     */
    public static function add($key, $object, $ttl = 0)
    {
        $ttl = $ttl > 2592000 || $ttl == 0 ? $ttl : $ttl + time();
        Cache::instance()->addImplementation($key, $object, $ttl);
    }

    /**
     * Retrieve an item from the cache. If the item doesn't exist this method
     * returns a false.
     * 
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return Cache::instance()->getImplementation($key);
    }

    /**
     * Checks if a particular item exists in the cache.
     * @param string $key
     * @return boolean
     */
    public static function exists($key)
    {
        return Cache::instance()->existsImplementation($key);
    }
    
    /**
     * Removes an item from the cache.
     * @param string $key
     */
    public static function remove($key)
    {
        Cache::instance()->removeImplementation($key);
    }
    
    /**
     * Deletes the Cache objects internal singleton so it could be re instantiated upon
     * the next call Cache object. This method is very necessary when you want to
     * dynamically switch between caching backends.
     */
    public static function reInstantiate()
    {
        self::$instance = null;
    }

    /**
     * Implementation of the add function by the appropriate caching backend.
     * @param string $key
     */
    abstract protected function addImplementation($key, $object, $expires);

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
