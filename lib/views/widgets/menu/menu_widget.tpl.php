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
?>
<?php if(count($items) > 0):?>
<ul class='menu <?php echo $alias ?>' id="<?echo $alias ?>-menu">
<?php foreach($items as $item): ?>
    <?php
    $params = '';
    foreach($item as $key => $value)
    {
        if($key == 'url' || $key == 'label' || $key == 'selected') continue;
        $params .= "$key = '$value' ";
    }
$id = str_replace(" ","_",strtolower($item["label"])) . '-menu-item';
?>
<li <?php echo $params ?> id="<?php echo $id ?>" class='menu-item <?php echo $item['selected'] ? "menu-selected " : ""; echo $id; ?>'>
<?php if($has_links == true):?>
<a <?php if($item['url'] !== false): ?>href='<?php echo isset($item["url"]) ? $item["url"] : str_replace(" ", "_", strtolower($item["label"]))?>'<?php endif; ?>><?php echo $item["label"]?></a>
<?php endif?>
</li>
<?php endforeach;?>
</ul>
<?php endif ?>
