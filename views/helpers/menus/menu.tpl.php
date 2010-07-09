<ul <?php echo $id == "" ? "" : "id='$id'" ?> <?php echo $id=="" ? "" : "class='menu menu-$id'" ?>>
    <?php foreach($this->items as $item): ?>
        <li><a href='<?php echo $item["path"] ?>'><?php echo $item["label"] ?></a></li>
    <?php endforeach ?>
</ul>