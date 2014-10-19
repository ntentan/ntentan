<div id='form-area'>
<?php
foreach($fields->unescape() as $field)
{
    if($field["primary_key"]) continue;
    echo $this->helpers->form->get($field);
}?>
</div>
