<h2><?php echo $model_name ?></h2>
<?php
// Add toolbar block
echo $toolbarBlock;

// Load the helpers
$this->addHelper("lists");
$this->addHelper("menus");
$toolbar = $this->menus->createMenu($operations);

?>
<div id="toolbar"><?php echo $toolbar?></div>
<div id="list"><?php
$listFile = Ntentan::$modulesPath . "$controller_path/{$base_class_name}list.inc.php";

if(file_exists($listFile))
{
    include $listFile;
}
else
{
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
}
echo $list;
?></div>