<?php
foreach($fields as $field)
{
    if($field["primary_key"]) continue;
    $this->forms->addModelField($field);
}
$this->forms->submitValue = "Save";
