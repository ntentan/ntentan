<?php
/**
 * Source file for the memcached cache class
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

/**
 * A memcache caching backend. Stores objects to be cached on memcached servers.
 */
class Memcache extends Cache
{
    private $cache;
    private $buffer;

    public function __construct()
    {
        $this->cache = new \Memcached();
        $this->cache->addServer('localhost', 11211);
    }

    protected function addImplementation($key, $object, $ttl)
    {
        $this->cache->add($key, $object, $ttl + time());
    }
    
    protected function existsImplementation($key)
    {
        $value = $this->cache->get($key);
        
        if($value === false)
        {
            return false;
        }
        else
        {
            $value = array("$key" => $value);
            return true;
        }
    }
    
    protected function getImplementation($key)
    {
        if(isset($value[$key]))
        {
            return $value[$key];
        }
        else
        {
            return $this->cache->get($key);
        }
    }
    
    protected function removeImplementation($key)
    {
        $this->cache->delete($key);
    }
}
