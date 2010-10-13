<?php
namespace ntentan\caching;

use ntentan\exceptions\FileNotFoundException;

class File extends Cache
{
    protected function addImplementation($key, $object, $ttl)
    {
        if(file_exists("cache"))
        {
            file_put_contents("cache/$key", serialize($object));
        }
        else
        {
            throw new FileNotFoundException("Directory <b><code>cache</code></b> not found!");
        }
    }
    
    protected function existsImplementation($key)
    {
        return file_exists("cache/$key");
    }
    
    protected function getImplementation($key)
    {
        return unserialize(file_get_contents("cache/$key"));
    }
}
