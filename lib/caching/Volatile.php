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

/**
 * The volitle cache is a special cache which keeps its data only during the
 * lifetime of the request to the server. This cache is normally used during
 * testing to ensure that nothing is stored in between test cases. It could
 * also be used for debugging.
 */
class Volatile extends Cache
{
    private $data;

    protected function addImplementation($key, $object, $ttl)
    {
        $this->data[$key] = $object;
    }

    protected function existsImplementation($key)
    {
        return isset($this->data[$key]);
    }

    protected function getImplementation($key)
    {
        return $this->data[$key];
    }
    
    protected function removeImplementation($key)
    {
    	
    }
}
