<h<?php echo $heading_level ?>>Edit <?php echo $item ?></h<?php echo $heading_level?>>
<?php echo $console_menu_block; ?>
<div id='form-area'>
<?php
echo $this->form->open();
$this->form->setErrors($errors);
$this->form->setData($data);
include "form.tpl.php";
echo $this->form->close();
?>
</div>
