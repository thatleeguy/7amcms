<div class="invoice-block">
	<ul class="btns-list">
		<li><a href="#" class="yellow-btn" onclick="$('#settings-form').submit();"><span>Save Settings &rarr;</span></a></li>
    </ul><!-- /btns-list end -->

	<div class="head-box">
		<h3 class="ttl ttl3"><?php echo __('Settings'); ?></h3>
	</div>

<?php echo form_open('admin/settings', 'id="settings-form"'); ?>
<div class="tabs">
	<ul>
		<li><a href="#general">General</a></li>
		<li><a href="#templates">Email Templates</a></li>
		<li><a href="#taxes">Taxes</a></li>
		<li><a href="#currencies">Currencies</a></li>
		<li><a href="#feeds">Feeds</a></li>
		<li><a href="#api_keys">API Keys</a></li>
	</ul>

	<div id="general">
		<div class="row">
			<label for="site_name">Site name</label>
			<input type="text" name="site_name" value="<?php echo $settings['site_name']; ?>" class="txt" />&nbsp;<?php echo form_error('site_name'); ?>
		</div>

		<div class="row">
			<label for="notify_email">Notify email</label>
			<input type="text" name="notify_email" value="<?php echo $settings['notify_email']; ?>" class="txt" />&nbsp;<?php echo form_error('notify_email'); ?>
		</div>

		<div class="row">
			<label for="paypal_email">PayPal email</label>
			<input type="text" name="paypal_email" value="<?php echo $settings['paypal_email']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="currency">Currency</label>
			<div class="sel-item">
			<?php echo form_dropdown('currency', $currencies, $settings['currency']); ?>
			</div>
		</div>
		
		<div class="row">
			<label for="theme">Frontend Theme</label>
			<div class="sel-item">
			<select name="theme">
				<?php foreach(glob(FCPATH.'third_party/themes/*') as $theme):
						if (basename($theme) == 'admin' || strstr(basename($theme), '.'))
						{
							continue;
						}
				?>
				<option value="<?php echo basename($theme); ?>" <?php echo basename($theme) == $settings['theme'] ? 'selected="selected"' : ''; ?>><?php echo basename($theme) ?></option>
				<?php endforeach; ?>
			</select>
			</div>
		</div>

		<div class="row">
			<label for="admin_theme">Admin Theme</label>
			<div class="sel-item">
			<select name="admin_theme">
				<?php foreach(glob(FCPATH.'third_party/themes/admin/*') as $theme): ?>
				<option value="<?php echo basename($theme); ?>" <?php echo basename($theme) == $settings['admin_theme'] ? 'selected="selected"' : ''; ?>>
					<?php echo basename($theme) ?>
				</option>
				<?php endforeach; ?>
			</select>
			</div>
		</div>

		<div class="row">
			<label for="license_key">License Key <span style="font-size:80%">(Version: <?php echo PANCAKE_VERSION; ?> )</span></label>
			<input type="text" name="license_key" value="<?php echo $settings['license_key']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="admin_name">Admin name</label>
			<input type="text" name="admin_name" value="<?php echo $settings['admin_name']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="date_format">Date Format</label>
			<input type="text" name="date_format" value="<?php echo $settings['date_format']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="task_time_interval">Task Time Interval</label>
			<input type="text" name="task_time_interval" value="<?php echo $settings['task_time_interval']; ?>" class="txt" size="3" />
		</div>

		<div class="row">
			<label for="mailing_address">Mailing Address:</label>
			<textarea name="mailing_address" rows="6"><?php echo $settings['mailing_address']; ?></textarea>
		</div>
		
	</div><!--/general-->

	<div id="taxes">
		<table class="pc-table" cellspacing="0" style="width: 400px;">
			<thead>
			<tr>
				<th>Tax Name</th>
				<th>Value (%)</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach (Settings::all_taxes() as $id => $tax): ?>
				<tr>
					<td><?php echo form_input(array(
						'name' => 'tax_name['.$id.']',
						'value' => set_value('tax_name['.$id.']', $tax['name']),
						'class' => 'txt small'
					)); ?></td>
					<td><?php echo form_input(array(
						'name' => 'tax_value['.$id.']',
						'value' => set_value('tax_value['.$id.']', @$tax['value']),
						'class' => 'txt small'
					)); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table><br />
		<a href="#" id="add-tax" class="yellow-btn"><span>Add Another Tax</span></a>
		<br /><br />
	</div><!--/taxes-->


	<div id="currencies">
		<table class="pc-table" cellspacing="0" style="width: 400px;">
			<thead>
			<tr>
				<th>Currency Name</th>
				<th>Currency Code</th>
				<th>Exchange Rate</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach (Settings::all_currencies() as $id => $currency): ?>
				<tr>
					<td><?php echo form_input(array(
						'name' => 'currency_name['.$id.']',
						'value' => set_value('currency_name['.$id.']', $currency['name']),
						'class' => 'txt small'
					)); ?></td>
					<td><?php echo form_input(array(
						'name' => 'currency_code['.$id.']',
						'value' => set_value('currency_code['.$id.']', $currency['code']),
						'class' => 'txt small'
					)); ?></td>
					<td><?php echo form_input(array(
						'name' => 'currency_rate['.$id.']',
						'value' => set_value('currency_rate['.$id.']', $currency['rate']),
						'class' => 'txt small'
					)); ?></td>
				</tr>
			<?php endforeach; ?>
			
			<?php if(!Settings::all_currencies()):?>
				<tr>
					<td><input name="new_currency_name[]" value="" class="txt small text" type="text"></td>
					<td><input name="new_currency_code[]" value="" class="txt small currency_code text" type="text"></td>
					<td><input name="new_currency_rate[]" value="" class="txt small currency_rate text" type="text"></td>
				</tr>
			<?php endif?>
			</tbody>
		</table><br />
		<a href="#" id="add-currency" class="yellow-btn"><span>Add Another Currency</span></a>
		<br /><br />
	</div><!--/currencies-->
	

	<div id="templates">
		<div class="row">
		  <label for="email_new_invoice">New Invoice:</label>
			<?php echo form_textarea(array(
				'name'	=> 'email_new_invoice',
				'id'	=> 'email_new_invoice',
				'rows'	=> 8,
				'cols'	=> 70,
				'value'	=> $settings['email_new_invoice']
			)); ?>
		</div>
		<div class="row">
		  <label for="email_paid_notification">Paid Notification:</label>
			<?php echo form_textarea(array(
				'name'	=> 'email_paid_notification',
				'id'	=> 'email_paid_notification',
				'rows'	=> 8,
				'cols'	=> 70,
				'value' => $settings['email_paid_notification']
			)); ?>
		</div>
		<div class="row">
		  <label for="email_receipt">Payment Receipt:</label>
			<?php echo form_textarea(array(
				'name'	=> 'email_receipt',
				'id'	=> 'email_new_receipt',
				'rows'	=> 8,
				'cols'	=> 70,
				'value' => $settings['email_receipt']
			)); ?>
		</div>
	</div><!--/templates-->
	<div id="feeds">
		<div class="row">
			<label for="rss_password">RSS Password:</label>
			<?php echo form_input(array(
				'name' => 'rss_password',
				'id'	=> 'rss_password',
				'class'	=> 'txt',
				'value' => set_value('rss_password', $settings['rss_password']),
			)); ?>
		</div>
		<br />
		<h3>Default Feeds</h3>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing">Paid:</label>
			<?php echo anchor('feeds/paid/10/'.PAN::setting('rss_password')); ?>
		</div>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing">Unpaid:</label>
			<?php echo anchor('feeds/unpaid/10/'.PAN::setting('rss_password')); ?>
		</div>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing">Overdue:</label>
			<?php echo anchor('feeds/overdue/10/'.PAN::setting('rss_password')); ?>
		</div>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing">Cron Jobs:</label>
			<?php echo anchor('cron/invoices/'.PAN::setting('rss_password')); ?>
		</div>
		<br />
		<h3>Feed Generator</h3>
		<div id="feed_generator" style="padding-top: 10px;">
			<div class="row">
				<label for="rss_type">Type:</label>
				<div class="sel-item">
				<select name="rss_type" id="rss_type">
					<option value="paid">Paid</option>
					<option value="unpaid">Unpaid</option>
					<option value="overdue">Overdue</option>
				</select>
				</div>
			</div>
			<div class="row">
				<label for="rss_type">Items:</label>
				<input type="text" name="rss_items" id="rss_items" value="10" size="5" class="txt" />
			</div>
			<div class="row">
				<label for="nothing">Your Link:</label>
				<span id="rss_link_gen">&nbsp;</span>
			</div>
			<br />
		</div>
	</div><!--/feeds-->
	
	<!--api keys-->
	<div id="api_keys">
		<table class="pc-table" cellspacing="0" style="width: 400px;">
			<thead>
			<tr>	
				<th>Name / Note</th>
				<th>Key</th>
				<th>Created</th>
				<th>Remove</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($api_keys as $key): ?>
				<tr>
					<td><?php echo form_input(array(
						'name' => 'key_note['.$key->id.']',
						'value' => set_value('key_note['.$key->id.']', $key->note),
						'class' => 'txt small'
					)); ?></td>
					<td><?php echo $key->key.form_hidden('key_key['.$key->id.']', $key->key); ?></td>
					<td>
						<?php echo format_date($key->date_created); ?>
					</td>
					<td>
						<a href="#" class="delete-key"><img src="<?php echo base_url(); ?>third_party/themes/admin/pancake/img/ui_icons/cancel_24.png" /></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table><br />
		<a href="#" id="add-key" class="yellow-btn"><span>Add Another Key</span></a>
		<br /><br />
	</div><!--/api keys-->
</div>
<br />

<?php echo form_close(); ?>

</div>

<script type="text/javascript">
$(document).ready(function () {
	$('.form_error').parent().find('input').addClass('error');
	$('.tabs').tabs();
	$('#add-tax').click(function () {
		$(this).parent().children('table').children('tbody').append('<tr><td><?php echo form_input(array(
				'name' => 'new_tax_name[]',
				'value' => '',
				'class' => 'txt small'
			)); ?></td><td><?php echo form_input(array(
				'name' => 'new_tax_value[]',
				'value' => '',
				'class' => 'txt small'
			)); ?></td></tr>');
						
			return false;
	});
	$('#add-currency').click(function () {
		$(this).parent().children('table').children('tbody').append('<tr><td><?php echo form_input(array(
			'name' => 'new_currency_name[]',
			'value' => '',
			'class' => 'txt small'
		)); ?></td><td><?php echo form_input(array(
			'name' => 'new_currency_code[]',
			'value' => '',
			'class' => 'txt small currency_code'
		)); ?></td><td><?php echo form_input(array(
			'name' => 'new_currency_rate[]',
			'value' => '',
			'class' => 'txt small currency_rate'
		)); ?></td></tr>');

		return false;
	});

	$('#add-key').click(function () {
		
		key = random_string(40);
		
		$(this).parent().children('table').children('tbody').append('<tr><td><?php echo form_input(array(
			'name' => 'new_key_note[]',
			'value' => '',
			'class' => 'txt small'
		)); ?></td><td>' + key + '<input type="hidden" name="new_key[]" value="' + key + '" /></td><td><?php echo format_date(now()); ?></td></tr>');

		return false;
	});
	
	$('.delete-key').click(function () {
		$(this).closest('tr').fadeOut().find('input').val('');
		return false;
	});

	$('input.currency_code').live('keyup', function(){
		var rate = $(this).closest('tr').find('input.currency_rate');

		if (rate.val() == "") {
			$.get('<?php echo base_url(); ?>ajax/convert_currency/' + this.value, function(amount) {

				if (parseFloat(amount) > 0) {
					rate.val(Math.round(amount * 100000) / 100000);
				}
			});
		}
	});

	$('#rss_type').change(function () {
		update_rss_link();
	});

	$('#rss_items').keyup(function () {
		update_rss_link();
	});

	function update_rss_link()
	{
		var type = $('#rss_type').val();
		var items = $('#rss_items').val();
		var password = $('#rss_password').val();

		var link = '<?php echo site_url('feeds'); ?>/'+type+'/'+items+'/'+password

		$('#rss_link_gen').html('<a href="'+link+'">'+link+'</a>');
	}
	update_rss_link();
	
	function random_string(string_length) {
		var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.substring(rnum,rnum+1);
		}
		return randomstring;
	}
});
</script>