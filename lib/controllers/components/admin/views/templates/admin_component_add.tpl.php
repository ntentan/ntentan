<h<?php echo $heading_level ?> class="admin-heading">Add new <?php echo $entity ?></h<?php echo $heading_level?>>
<?php
echo $console_menu_block;
if(count($errors) > 0)
{
    echo "<div class='form-errors'>There were some errors on the form</div>";
}
$this->helpers->form->setErrors($errors);
$this->helpers->form->setData($data);
$form = t(
    "add_{$entity_code}_form.tpl.php", 
    array(
        'fields' => $fields,
        'errors' => $errors,
        'data' => $data,
        'variables' => $form_variables
    )
);
echo $this->helpers->form->open("add-$entity_code-form");
echo $form;
echo $this->helpers->form->close('Save');
