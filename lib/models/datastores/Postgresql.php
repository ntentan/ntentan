<?php
/**
 * Source file for the postgresql orm driver
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
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
 * @category ORM
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\models\datastores;

class Postgresql extends Atiaa
{
    protected function limit($limitParams)
    {
        return (isset($limitParams['limit']) ? " LIMIT {$limitParams['limit']}":'') .
               (isset($limitParams['offset']) ? " OFFSET {$limitParams['offset']}":'');
    }
    
    public function __toString()
    {
        return 'postgresql';
    }

    protected function fixType($type)
    {
        switch($type)
        {
            case 'timestamp without time zone':
                return 'datetime';
            case 'character varying':
                return 'string';
            default:
                return $type;
        }
    }
    
    protected function getLastInsertId() 
    {
        $lastval = $this->query("SELECT LASTVAL() as last");
        return $lastval[0]["last"];        
    }
}
