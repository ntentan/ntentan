<?php $modelSingular = strtolower(\ntentan\Ntentan::singular($entity));?>
<?php if($headings && $entity != ''):?><h<?php echo $heading_level?> class="admin-heading"><?php echo $entity ?></h<?php echo $heading_level?>><?php 
    endif; ?><div id="item-actions-menu">
    <?php echo $this->widgets->menu(array(array('label'=>"Add new $modelSingular", 'url'=>u("$route/add"))))->alias('actions'); ?>
</div>
<?php if($notifications & is_numeric($notification_type)):?>
<div class="notification"><?php
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
$this->helpers->list->headers = $headers;
$this->helpers->list->data = $data;
$this->helpers->list->rowTemplate = $row_template;
if(is_array($cell_templates))
{
    $this->helpers->list->cellTemplates = $cell_templates;
}

if($this->helpers->list->cellTemplates['id'] == null)
{ 
    $this->helpers->list->cellTemplates['id'] = $operations_template;
}

$this->helpers->list->variables["operations"] = $operations;
$this->helpers->list->variables['item_operation_url'] = $item_operation_url;

if(is_array($variables))
{
    foreach($variables as $variable => $value)
    {
        $this->helpers->list->variables[$variable] = $value;
    }
}
echo $this->helpers->list;
if($pagination) {
    echo $this->widgets->pagination(array(
        'page_number' => $page_number, 
        'number_of_pages' => $number_of_pages, 
        'base_route' => $base_route->unescape()
        )
    );
}
?>
</div>
