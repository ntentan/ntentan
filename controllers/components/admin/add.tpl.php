<h2><?php echo $model_name ?></h2>
<?php
$this->addHelper("forms");
if(file_exists(Ntentan::$packagesPath . "$controller_path/{$util_class_name}Form.php"))
{
    include Ntentan::$packagesPath . "$controller_path/{$util_class_name}Form.php";
    $formClassName = "{$util_class_name}Form";
    $form = new $formClassName($fields);
    $form->setErrors($form_errors);
}
else
{
    $form = $this->forms->createModelForm($fields,"add-form");
    $form->setErrors($form_errors);
}
echo $form;
?>