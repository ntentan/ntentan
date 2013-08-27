<?php
/**
 * Text area for forms
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
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan\views\helpers\forms\api;

class TextArea extends Field
{
    public function __construct($label="",$name="",$description="")
    {
        $this->setLabel($label);
        $this->setName($name);
        $this->setDescription($description);
    }

    public function render()
    {
        $this->addAttribute('rows', 10);
        $this->addAttribute('cols', 80);
        $this->addAttribute('class', 'fapi-textarea');
        $this->addAttribute('name', $this->getName());
        return "<textarea ".$this->getAttributes().$this->getCSSClasses().">".
               $this->getValue()."</textarea>";
    }
}
