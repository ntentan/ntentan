<h<?php echo $heading_level ?>>Edit <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
echo $this->form->open();
$this->form->setErrors($errors);
$this->form->setData($data);
echo t($form_template, array('fields' => $fields, 'data' => $data));
echo $this->form->close('Update');