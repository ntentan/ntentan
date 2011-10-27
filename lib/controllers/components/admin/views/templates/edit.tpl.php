<h<?php echo $heading_level ?>>Edit <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
$form = t("edit_{$entity}_form.tpl.php", array('fields' => $fields, 'data' => $data));
echo $this->helpers->form->open();
echo $form;
echo $this->helpers->form->close('Update');