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

use ntentan\exceptions\FileNotFoundException;

/**
 * A file caching backend
 */
class File extends Cache
{
    protected function addImplementation($key, $object, $expires)
    {
        if(file_exists("cache") && is_writable("cache"))
        {
            $object = array(
                'expires' => $expires,
                'object' => $object
            );
            file_put_contents("cache/$key", serialize($object));
        }
        else
        {
            throw new FileNotFoundException(
                "The file cache directory <b><code>cache</code></b> was not found or is not writable!"
            );
        }
    }
    
    protected function existsImplementation($key)
    {
        if(file_exists("cache/$key"))
        {
            $cacheObject = unserialize(file_get_contents("cache/$key"));
            if($cacheObject['expires'] > time() || $cacheObject['expires'] == 0)
            {
                return true;
            }
            else if($cacheObject['expires'] == -1)
            {
                return false;
            }
        }
        return false;
    }
    
    protected function getImplementation($key)
    {
        $cacheObject = unserialize(file_get_contents("cache/$key"));
        if($cacheObject['expires'] > time() || $cacheObject['expires'] == 0)
        {
            return $cacheObject['object'] ;
        }
        else if($cacheObject['expires'] == -1)
        {
            return false;
        }
        else
        {
            return false;
        }
    }
    
    protected function removeImplementation($key)
    {
        unlink("cache/$key");
    }
}
