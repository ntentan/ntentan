<h2><?php echo $model_name ?></h2>
<?php
// Add toolbar block
echo $toolbarBlock;

// Load the helpers
$this->addHelper("lists");
$this->addHelper("menus");
$toolbar = $this->menus->createMenu($operations);

// Setup the column headers
$fields = array_keys($list_data[0]);

array_shift($fields);
foreach($fields as $field)
{
    $columns[] = array("name"=>$field, "label"=>Ntentan::toSentence($field));
}
$columns[] =
    array(
        "name"=>"id",
        "label"=>"Operations",
        "operations"=>$side_operations,
        "renderer"=>"operations"
    );

$list = $this->lists->createListView($list_data, $columns);
?>
<div id="toolbar"><?php echo $toolbar?></div>
<div id="list"><?php echo $list?></div>