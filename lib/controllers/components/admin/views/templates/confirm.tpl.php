<?php if($headings):?>
<h<?php echo $heading_level?>>Confirm</h<?php echo $heading_level?>>
<?php endif;?>
<div id='admin-confirm-body'>
<p><?php echo str_replace("%item%", $item, $message->unescape())?></p>
<div>
    <a class='buttonlike grey-gradient grey-border big-button' href="<?php echo $positive_route?>">Yes</a>
    <a class='buttonlike grey-gradient grey-border big-button' href="<?php echo $negative_route?>">No</a>
</div>
</div>
