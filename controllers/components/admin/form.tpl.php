<?php
$form = $this->loadHelper("forms");
foreach($fields as $field)
{
    if($field["primary_key"]) continue;
    $form->addModelField($field);
}
$form->submitValue = "Save";
