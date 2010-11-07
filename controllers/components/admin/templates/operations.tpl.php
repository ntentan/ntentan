<?php foreach ($variables["operations"] as $operation):?>
<a class='buttonlike grey-gradient grey-border' href="<?php echo $operation["link"] . $column; ?>">
    <?php echo $operation["label"]?>
</a>
<?php endforeach;?>
