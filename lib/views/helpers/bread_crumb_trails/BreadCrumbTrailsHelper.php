<?php
/*
 * Bread crumb trails helper
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

namespace ntentan\views\helpers\bread_crumb_trails;

use ntentan\Ntentan;
use ntentan\views\helpers\Helper;

class BreadCrumbTrailsHelper extends Helper
{
    private $trailData = false;
    private $separator = '>';
        
    public function trail_data($data = false)
    {
        if($data === false)
        {
            if($this->trailData === false)
            {
                $url = Ntentan::getUrl('/');
                $trailData = array(array('url' => $url, 'label' => 'Home'));
                foreach(explode('/', Ntentan::$requestedRoute) as $route)
                {
                    $url .= "$route/";
                    $trailData[] = array('url' => $url, 'label' => Ntentan::toSentence($route));
                }
                return $trailData;
            }
            else
            {
                return $this->trailData;
            }
        }
        else
        {
            $this->trailData = $data;
            return $this;
        }
    }
    
    public function separator($separator)
    {
        $this->separator = $separator;
        return $this;
    }
    
    public function help()
    {
        $markup = '';
        foreach($this->trail_data() as $i => $trailData)
        {
            if($i > 0) $markup .= "{$this->separator} ";
            $markup .= "<a href='{$trailData['url']}'>{$trailData['label']}</a> ";
        }
        return "<div class='bread-crumb'>$markup</div>";
    }
}
