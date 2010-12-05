<h<?php echo $heading_level ?>>Edit <?php echo $item ?></h<?php echo $heading_level?>>
<?php echo $console_menu_block; ?>
<div id='form-area'>
<?php 
include "form.tpl.php";
$form->setErrors($errors);
$form->setData($data);
echo $form;
?>
</div>
