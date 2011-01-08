<td>
<?php foreach ($variables["operations"] as $operation):?>
<a href="<?php echo $operation["link"] . $column; ?>">
    <?php echo $operation["label"]?>
</a>
<?php endforeach;?>
</td>