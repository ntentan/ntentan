<?php
foreach($fields as $field)
{
    if($field["primary_key"]) continue;
    echo $this->form->get($field);
}
$this->form->submitValue = "Save";
