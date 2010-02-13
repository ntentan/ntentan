<h2><?php echo $model_name ?></h2>
<?php
echo $toolbarBlock;
$this->addHelper("lists");
$this->addHelper("menus");
$toolbar = $this->menus->createMenu($operations);
$list = $this->lists->createListView($list_data);
?>
<div id="toolbar"><?php echo $toolbar?></div>
<div id="list"><?php echo $list?></div>