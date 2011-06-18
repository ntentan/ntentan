<td align='right'>
<?php foreach ($variables["operations"] as $operation):?>
<a class='admin-operation <?php echo strtolower($operation["label"]) ?>-admin-operation' href="<?php echo $operation["link"] . $column; ?>">
    <?php echo $operation["label"]?>
</a>
<?php endforeach;?>
</td>