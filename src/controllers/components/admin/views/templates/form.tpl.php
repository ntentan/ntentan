<div id='form-area'>
<?php
$primary_key = $primary_key->unescape();
foreach($fields->unescape() as $field)
{
    if(array_search($field['name'], $primary_key) !== false) 
    {
        continue;
    }
    echo $this->helpers->form->get($field);
}
?>
</div>
