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
                <td>
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
