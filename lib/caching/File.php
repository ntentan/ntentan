<?php
/**
 * Source file for the file cache class
 * 
 * LICENSE
 * ======= 
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
 * 
 * @category Caching
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */
namespace ntentan\caching;

use ntentan\exceptions\FileNotFoundException;
use ntentan\Ntentan;

/**
 * A file caching backend. This class stores objects to be cached as files in
 * the cache directory. It is advisable to use it as the default callback for
 * other caching methods.
 */
class File extends Cache
{
    private $cachePath;
    
    public function __construct()
    {
        $this->setCachePath(Ntentan::$config['application']['app_home'] . "cache/");
    }
    
    public function setCachePath($path)
    {
        $this->cachePath = $path;
    }
    
    /**
     * Get the path of the cache file.
     * 
     * @param string $file The file being requested
     * @return string
     */
    private function getCacheFile($file = '')
    {
        return "{$this->cachePath}{$file}";
    }
    
    /**
     * Hashes the key into a format appropriate for file names.
     * 
     * @param string $key
     * @return string The hashed key
     */
    private function hashKey($key)
    {
        return $key;
    }
    
    protected function addImplementation($key, $object, $ttl)
    {
        if(file_exists(self::getCacheFile()) && is_writable(self::getCacheFile()))
        {
            $expires = $ttl == 0 ? $ttl : $ttl + time();
            
            $object = array(
                'expires' => $expires,
                'object' => $object
            );
            $key = $this->hashKey($key);
            file_put_contents(self::getCacheFile("$key"), serialize($object));
        }
        else
        {
            trigger_error("The file cache directory *".self::getCacheFile()."* was not found or is not writable!", E_USER_WARNING);
        }
    }
    
    protected function existsImplementation($key)
    {
        $key = $this->hashKey($key);
        $file = self::getCacheFile("$key");
        if(file_exists($file))
        {
            $cacheObject = unserialize(file_get_contents($file));
            if($cacheObject['expires'] > time() || $cacheObject['expires'] == 0)
            {
                return true;
            }
        }
        return false;
    }
    
    protected function getImplementation($key)
    {
        $cacheFile = self::getCacheFile("$key");
        if(file_exists($cacheFile))
        {
            $key = $this->hashKey($key);
            $cacheObject = unserialize(file_get_contents($cacheFile));
            if($cacheObject['expires'] > time() || $cacheObject['expires'] == 0)
            {
                return $cacheObject['object'] ;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    protected function removeImplementation($key)
    {
        $key = $this->hashKey($key);
        unlink(self::getCacheFile("$key"));
    }
}
