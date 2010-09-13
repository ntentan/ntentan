<ul class='menu <?php echo $alias ?>'>
<?php foreach($items as $item): ?>
<li class='menu-item <?php echo str_replace(" ","_",strtolower($item["label"])) ?>'>
<?php if($this->hasLinks == true):?>
<a href='<?php echo isset($item["path"]) ? $item["path"] : str_replace(" ", "_", strtolower($item["label"]))?>'><?php echo $item["label"]?></a>
<?php endif?>
</li>
<?php endforeach;?>
</ul>
