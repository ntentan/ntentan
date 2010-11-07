<table class='item-table'>
    <thead>
        <tr>
            <?php foreach($headers as $header):?>
            <td><?php echo s($header) ?></td>
            <?php endforeach;?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <?php echo t($row_template, array('row' => $row, 'cell_templates'=> $cell_templates, 'default_cell_template'=>$default_cell_template, 'variables'=>$variables))?>
            <!--<?php foreach ($list_fields as $field):?>
            <td><?php echo $row[$field] ?></td>
            <?php endforeach;?>
            <td class='operations'>
                <?php foreach ($operations as $operation):?>
                <a class='buttonlike grey-gradient grey-border' href="<?php echo $operation["link"] . $row["id"]; ?>">
                    <?php echo $operation["label"]?>
                </a>
                <?php endforeach;?>
            </td>-->
        </tr>
        <?php endforeach;?>
    </tbody>
</table>