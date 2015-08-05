<?php
/**
 * Source file for the file cache class
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
        if(Ntentan::$appHome != '')
        {
            $this->setCachePath(Ntentan::$appHome . "/cache/");
        }
        else
        {
            $this->setCachePath(getcwd() . "/cache/");
        }
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
        return md5($key);
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
            // Throwing exceptions here sometimes breaks the template engine
            Ntentan::error("The file cache directory *".self::getCacheFile()."* was not found or is not writable!", 'Caching error!');
            trigger_error("The file cache directory *".self::getCacheFile()."* was not found or is not writable!");
        }
    }
    
    protected function existsImplementation($key)
    {
        $key = $this->hashKey($key);
        $file = self::getCacheFile($key);
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
        $key = $this->hashKey($key);
        $cacheFile = self::getCacheFile($key);
        if(file_exists($cacheFile))
        {
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
