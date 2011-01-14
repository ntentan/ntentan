<div class='item-pages-list'>
    <?php foreach ($pages as $page):?>
    <a <?php echo $page['selected'] ? "class='selected'" : "" ?> href='<?php echo $page["link"] ?>'><?php echo $page["label"] ?></a>
    <?php endforeach;?>
</div>