<h<?php echo $heading_level ?>>Add new <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
$form = t("add_{$entity}_form.tpl.php", array('fields' => $fields));
echo $this->helpers->form->open();
echo $form;
echo $this->helpers->form->close('Save');
