<div class="update-notification" id="update">
    <h2>Pancake was upgraded from <?php echo $from; ?> to <?php echo $to; ?>!</h2>

    <div class="changelog-container">
	<h3><?php echo __('update:whatschanged', array($to))?></h3>
	<div class="changelog">
	    <?php echo $changelog; ?>
	</div>
    </div>
</div>