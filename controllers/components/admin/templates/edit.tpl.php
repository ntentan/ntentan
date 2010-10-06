<h4>Edit <?php echo $item ?></h4>
<?php 
include "form.tpl.php";
$form->setErrors($errors);
$form->setData($data);
echo $form;