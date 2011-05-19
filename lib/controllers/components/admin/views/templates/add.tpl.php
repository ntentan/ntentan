<h<?php echo $heading_level ?>>Add new <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
echo $this->helpers->form->open();
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
echo t("add_{$entity}_form.tpl.php", array('fields' => $fields));
echo $this->helpers->form->close('Save');
