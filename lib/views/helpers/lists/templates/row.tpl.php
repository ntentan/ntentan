<tr><?php 
foreach($row as $index => $column)
{
    $cell_template = $cell_templates[$index] == null ? 
    $default_cell_template : $cell_templates[$index];
    echo t($cell_template, array("column"=>$column,"variables"=>$variables)); 
}?></tr>