<?php if($headings): ?>
<h2><?php echo $model ?></h2>
<?php endif ?>
<div id="item-actions-menu">
    <?php echo $item_actions_menu_block ?>
</div>
<?php 
switch($notification_type)
{
case 1:
    echo "<div class='notification'>Successfully added $model <b>$notification_item</b></div>";
    break;
case 2:
    echo "<div class='notification'>Successfully edited $model <b>$notification_item</b></div>";
    break;
case 3:
    echo "<div class='notification'>Successfully deleted $model <b>$notification_item</b></div>";
    break;
} 
?>
<div>
    <table class='item-table'>
        <thead>
            <tr>
                <?php foreach($list_fields as $field):?>
                <td><?php echo to_sentence($field) ?></td>
                <?php endforeach;?>
                <td></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $row): ?>
            <tr>
                <?php foreach ($list_fields as $field):?>
                <td><?php echo $row[$field] ?></td>
                <?php endforeach;?>
                <td class='operations'>
                    <?php foreach ($operations as $operation):?>
                    <a class='buttonlike grey-gradient grey-border' href="<?php echo $operation["link"] . $row["id"]; ?>">
                        <?php echo $operation["label"]?>
                    </a>
                    <?php endforeach;?>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <?php if(isset($pages)):?>
    <div class='item-pages-list'>
        <?php foreach ($pages as $page):?>
        <a href='<?php echo $page["link"] ?>'><?php echo $page["label"] ?></a>
        <?php endforeach;?>
    </div>
    <?php endif?>
</div>
