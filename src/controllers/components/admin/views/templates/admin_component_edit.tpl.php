<h<?php echo $heading_level ?>>Edit <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
echo $this->helpers->form->open("add-$entity-form");
$form = t(
    "edit_{$entity_code}_form.tpl.php", 
    array(
        'fields' => $fields, 
        'data' => $data,
        'variables' => $form_variables
    )
);
echo $form;
echo $this->helpers->form->close('Update');