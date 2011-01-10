<tr onmouseover="$(this).addClass('mouseover')"
    onmouseout="$(this).removeClass('mouseover')"
    <?php echo $variables['item_operation_url'] == '' ? '' : "onclick=\"document.location='".($variables['item_operation_url'] .'/'. $row['id'])."'\"" ?> >
<?php
foreach($row as $index => $column)
{
    $cell_template = $cell_templates[$index] == null ? 
    $default_cell_template : $cell_templates[$index];
    echo t($cell_template, array("column"=>$column,"variables"=>$variables)); 
}
?>
</tr>