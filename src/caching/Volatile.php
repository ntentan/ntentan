<?php
/**
 * Source file for the volatile cache class
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

/**
 * The volitle cache is a special cache which keeps its data only during the
 * lifetime of the request to the server. This cache is normally used during
 * testing to ensure that nothing is stored in between test cases. It could
 * also be used for debugging but in that case your application may perform very
 * poorly.
 */
class Volatile extends Cache
{
    private $data;
    private $expiries;

    protected function addImplementation($key, $object, $ttl)
    {
        $this->data[$key] = $object;
        $this->expiries[$key] = time() + $ttl;
    }

    protected function existsImplementation($key)
    {
        if(time() < $this->expiries[$key])
        {
            return isset($this->data[$key]);
        }
        else
        {
            return false;
        }
    }

    protected function getImplementation($key)
    {
        if(time() < $this->expiries[$key])
        {
            return $this->data[$key];
        }
        else
        {
            return false;
        }
    }
    
    protected function removeImplementation($key)
    {
        unset($this->data[$key]);
    }
    
    public function reset()
    {
        $this->data = array();
    }
}
