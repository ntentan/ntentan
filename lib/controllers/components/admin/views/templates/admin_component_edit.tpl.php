<h<?php echo $heading_level ?>>Edit <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
foreach($errors as $error)
{
    var_dump($error);
}
echo $console_menu_block;
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
$form = t("edit_{$entity_code}_form.tpl.php", array('fields' => $fields, 'data' => $data));
echo $this->helpers->form->open("add-$entity-form");
echo $form;
echo $this->helpers->form->close('Update');