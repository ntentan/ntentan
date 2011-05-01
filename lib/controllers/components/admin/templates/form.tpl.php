<div id='form-area'>
<?php
foreach($fields as $field)
{
    if($field["primary_key"]) continue;
    echo $this->helpers->form->get($field);
}?>
</div>
