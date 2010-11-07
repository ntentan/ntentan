<?php foreach($row as $index => $column):
$cell_template = $cell_templates[$index] == null ? $default_cell_template : $cell_templates[$index];
?>
<td id='cell_<?php echo $index?>>'><?php echo t($cell_template, array("column"=>$column,"variables"=>$variables)) ?></td>
<?php endforeach;?>
