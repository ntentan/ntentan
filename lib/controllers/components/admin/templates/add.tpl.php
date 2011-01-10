<h<?php echo $heading_level ?>>Add new <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
echo $this->form->open();
$this->form->setErrors($errors);
$this->form->setData($data);
echo t($form_template, array('fields' => $fields));
echo $this->form->close('Save');
