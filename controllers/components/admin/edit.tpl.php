<h2><?php echo $model_name ?></h2>
<?php
$this->addHelper("forms");
$formFile = Ntentan::$packagesPath . "$controller_path/{$base_class_name}form.inc.php";
if(file_exists($formFile))
{
    include $formFile;
}
else
{
    $form = $this->forms->createModelForm($fields,"add-form");
    $form->setErrors($form_errors);
    echo $form;
}