<?php if($headings): ?>
<h<?php echo $heading_level?>><?php echo $model ?></h<?php echo $heading_level?>>
<?php endif ?>
<div id="item-actions-menu">
    <?php echo $item_actions_menu_widget ?>
</div>
<?php if($notifications & is_numeric($notification_type)):?>
<div class="notification"><?php
$modelSingular = strtolower(\ntentan\Ntentan::singular($model));
switch($notification_type)
{
case 1:
    echo "Successfully added $modelSingular <b>$notification_item</b>";
    break;
case 2:
    echo "Successfully edited $modelSingular <b>$notification_item</b>";
    break;
case 3:
    echo "Successfully deleted $modelSingular <b>$notification_item</b>";
    break;
}
?></div>
<?php endif?>
<div>
<?php
$headers[] = "";
$this->list->headers = $headers;
$this->list->data = $data;
$this->list->rowTemplate = $row_template;
if(is_array($cell_templates))
{
    $this->list->cellTemplates = $cell_templates;
}

if($this->list->cellTemplates['id'] == null)
{ 
    $this->list->cellTemplates['id'] = $operations_template;
}
$this->list->variables["operations"] = $operations;
if(is_array($variables))
{
    foreach($variables as $variable => $value)
    {
        $this->list->variables[$variable] = $value;
    }
}
echo $this->list;
?>
<?php if(isset($pages)):?>
<div class='item-pages-list'>
    <?php foreach ($pages as $page):?>
    <a <?php echo $page['selected'] ? "class='selected'" : "" ?> href='<?php echo $page["link"] ?>'><?php echo $page["label"] ?></a>
    <?php endforeach;?>
</div>
<?php endif?>
</div>
