<?php
/**
 * A template for the CLI messages
 * 
 * Ntentan Framework
 * Copyright (c) 2008-2013 James Ekow Abaka Ainooson
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
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2013 James Ekow Abaka Ainooson
 * @license MIT
 */

$stream = defined('STDERR') ? STDERR : fopen("php://output", w);

fputs($stream, "Ntentan Error!\n");
fputs($stream, strip_tags("$message\n\n"));
if($showTrace === true)
{
    foreach($trace as $trace_item)
    {
        fputs($stream, "{$trace_item["file"]}\t{$trace_item["line"]}\t"
         . $trace_item["class"] . $trace_item["type"]
         . $trace_item["function"] . "\n"
        );
    }
}
