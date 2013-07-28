<?php
/**
 * Source code file for text fields
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
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan\views\helpers\forms\api;

/**
 * Implementation of a regular text field. This field is used to
 * accept single line text input from the user.
 * @ingroup Form_API
 */
class TextField extends Field
{
    protected $max_length;
    protected $type;
    protected $max_val;
    protected $min_val;
    protected $regexp;

    public function __construct($label="",$name="",$description="",$value="")
    {
        Field::__construct($name,$value);
        Element::__construct($label, $description);
        $this->type = "TEXT";
        $this->addAttribute("type","text");
    }

    public function render()
    {
        $this->addAttribute("class", "textfield ".$this->getCSSClasses());
        $this->addAttribute("name", $this->getName());
        //$this->addAttribute("id", $this->id());
        $this->addAttribute("value", $this->getValue());
        return "<input {$this->getAttributes()} />";
    }
}
