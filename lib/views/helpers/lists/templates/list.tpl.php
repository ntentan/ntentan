<?php  ?>
<table class='item-table'>
    <?php if($has_headers === true): ?>
    <thead>
        <tr>
            <?php foreach($headers as $header):?>
            <td><?php echo s($header) ?></td>
            <?php endforeach;?>
        </tr>
    </thead>
    <?php endif; ?>
    <tbody><?php 
        foreach($data as $row)
        {
            echo t($row_template, array('row' => $row, 'cell_templates'=> $cell_templates, 'default_cell_template'=>$default_cell_template, 'variables'=>$variables));
        }
    ?></tbody>
</table>
