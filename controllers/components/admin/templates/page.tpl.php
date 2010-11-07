<?php if($headings): ?>
<h2><?php echo $model ?></h2>
<?php endif ?>
<div id="item-actions-menu">
    <?php echo $item_actions_menu_block ?>
</div>
<?php if($notifications):?> 
<div class="notification"><?php
switch($notification_type)
{
case 1:
    echo "Successfully added $model <b>$notification_item</b>";
    break;
case 2:
    echo "Successfully edited $model <b>$notification_item</b>";
    break;
case 3:
    echo "Successfully deleted $model <b>$notification_item</b>";
    break;
}
?></div>
<?php endif?>
<div>
<?php
$headers[] = "";
$this->list->headers = $headers;
$this->list->data = $data;
$this->list->cellTemplates['id'] = $operations_template;
$this->list->variables["operations"] = $operations;
echo $this->list;
?>
<?php if(isset($pages)):?>
<div class='item-pages-list'>
    <?php foreach ($pages as $page):?>
    <a href='<?php echo $page["link"] ?>'><?php echo $page["label"] ?></a>
    <?php endforeach;?>
</div>
<?php endif?>
</div>
