<h2>Edit <?php echo $item ?></h2>
<?php echo $console_menu_block; ?>
<div id='form-area'>
<?php 
include "form.tpl.php";
$form->setErrors($errors);
$form->setData($data);
echo $form;
?>
</div>
