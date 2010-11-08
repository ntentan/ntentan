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
        </tr>
        <?php endforeach;?>
    </tbody>
</table>