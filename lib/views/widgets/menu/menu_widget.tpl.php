<?php
/**
 * Source file for the menu widget template
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
 * @category Widgets
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */
?>
<?php if(count($items) > 0):?>
<ul class='menu <?php echo $alias ?>' id="<?echo $alias ?>-menu">
<?php foreach($items as $item): ?>
    <?php
    $params = '';
    foreach($item as $key => $value)
    {
        if($key == 'url' || $key == 'label' || $key == 'selected' || $key == 'id') continue;
        $params .= "$key = '$value' ";
    }
$id = isset($item['id']) ? $item['id'] : 'menu-item' . str_replace("/","-",strtolower($item["url"]));
?>
<li <?php echo $params ?> id="<?php echo $id ?>" class='menu-item <?php echo $item['selected'] ? "menu-selected " : ""; echo $id; echo $item['fully_matched'] ? ' menu-fully-matched ' : ''?>'>
<?php if($has_links == true):?>
<a <?php if($item['url'] !== false): ?>href='<?php echo isset($item["url"]) ? $item["url"] : str_replace(" ", "_", strtolower($item["label"]))?>'<?php endif; ?>><?php echo $item["label"]?></a>
<?php endif?>
</li>
<?php endforeach;?>
</ul>
<?php endif ?>
