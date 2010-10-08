<?php if(count($items) > 0):?>
<ul class='menu <?php echo $alias ?>'>
<?php foreach($items as $item): ?>
<li class='menu-item <?php echo \ntentan\Ntentan::$route == $item["url"] ? "menu-selected" : "" ?> <?php echo str_replace(" ","_",strtolower($item["label"])) ?>'>
<?php if($this->hasLinks == true):?>
<a href='<?php echo isset($item["url"]) ? $item["url"] : str_replace(" ", "_", strtolower($item["label"]))?>'><?php echo $item["label"]?></a>
<?php endif?>
</li>
<?php endforeach;?>
</ul>
<?php endif ?>
