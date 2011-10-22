<div class="invoice-block">
	<ul class="btns-list">
		<li>&nbsp;</li>
    </ul><!-- /btns-list end -->

	<div class="head-box">
		<h3 class="ttl ttl3"><?php echo __('Success!'); ?></h3>
	</div>

	<div class="row base-indent">
	<p>You have added an invoice for

		<?php if($invoice->email != ''): ?>
			<a href="mailto:<?php echo $invoice->email;?>"><?php echo $invoice->first_name;?> <?php echo $invoice->last_name;?></a><?php if($invoice->company != ''){?>, from <?php echo $invoice->company;?>, <?php }?>
		<?php else: ?>
			<?php echo $invoice->first_name;?> <?php echo $invoice->last_name;?><?php if($invoice->company != ''){?>, from <?php echo $invoice->company;?>, <?php }?>
		<?php endif; ?>

		for the invoice <strong>#<?php echo $invoice->invoice_number;?></strong> totalling <strong><?php echo Currency::format($invoice->amount, $invoice->currency_code);?></strong>.
	</p>

	
	<p class="urlToSend">Here is the url to send: <a href="<?php echo site_url($unique_id); ?>" class="url-to-send"><?php echo site_url($unique_id); ?></a> <a href="#" id="copy-to-clipboard" class="yellow-btn"><span>Copy to clipboard</span></a></p>
        
	</div>
</div>
<div class="invoice-block" id="mailperson">
	<?php if($invoice->email != ''): ?>

	<?php echo form_open('admin/invoices/send/'.$unique_id, 'id="send-invoice"'); ?>
		<input type="hidden" name="unique_id" value="<?php echo $unique_id; ?>" />

		<fieldset>
			
			<div class="head-box">
				<h3 class="ttl ttl3"><?php echo lang('invoices:send_now_title') ?></h3>
			</div>
			<div class="row base-indent"><p><?php echo lang('invoices:send_now_body') ?></p>
				
				<h4><?php echo __('global:to') ?>: <span><?php echo $invoice->email;?></span></h4></div>
			<div class="row base-indent">
				<label for="subject"><?php echo __('global:subject') ?>: </label><input type="text" id="subject" name="subject" class="txt" value="<?php echo __('invoices:number').$invoice->invoice_number ?>">
			</div>
			<div class="row base-indent">
				<textarea name="message" rows="15" style="height:200px"><?php echo PAN::setting('email_new_invoice'); ?></textarea>
			</div>
			<div class="row base-indent">
				<a href="#" class="yellow-btn" onclick="$('#send-invoice').submit();"><span><?php echo __('invoices:send_now') ?> &rarr;</span></a>
			</div>
			

		</fieldset>
	</form>
	<?php endif;?>
</div>
<?php
asset::js('jquery.zclip.min.js', array(), 'created');
echo asset::render('created');
?>
<script>
    $('a#copy-to-clipboard').click(function() {return false;}).zclip({
        path: '<?php echo asset::get_src('ZeroClipboard.swf')?>',
        copy: $('.url-to-send').text(),
        afterCopy:function(){
            $('.url-to-send').width($('.url-to-send').width()).text('Copied!');
        }
    });
</script>