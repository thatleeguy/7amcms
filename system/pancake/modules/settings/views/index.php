<script src="<?php echo Asset::get_src('codemirror/lib/codemirror.js');?>"></script>
<link rel="stylesheet" href="<?php echo Asset::get_src('codemirror/lib/codemirror.css');?>">
<script src="<?php echo Asset::get_src('codemirror/mode/css/css.js');?>"></script>
<link rel="stylesheet" href="<?php echo Asset::get_src('codemirror/theme/default.css');?>">
<div class="invoice-block">
	<ul class="btns-list">
		<li><a href="#" class="yellow-btn" onclick="$('#settings-form').submit();"><span><?php echo __('settings:save') ?> &rarr;</span></a></li>
    </ul><!-- /btns-list end -->

	<div class="head-box">
		<h3 class="ttl ttl3"><?php echo __('global:settings'); ?></h3>
	</div>

<?php echo form_open_multipart('admin/settings', 'id="settings-form"'); ?>
<div class="tabs">
	<ul>
		<li><a href="#general"><?php echo __('settings:general') ?></a></li>
		<li><a href="#templates"><?php echo __('settings:email_templates') ?></a></li>
		<li><a href="#taxes"><?php echo __('settings:taxes') ?></a></li>
		<li><a href="#currencies"><?php echo __('settings:currencies') ?></a></li>
        <li><a href="#branding"><?php echo __('settings:branding') ?></a></li>
        <li><a href="#payment"><?php echo __('settings:payment_methods') ?></a></li>
	<li><a href="#update"><?php echo __('global:update') ?></a></li>
		<li><a href="#feeds"><?php echo __('settings:feeds') ?></a></li>
		<li><a href="#api_keys"><?php echo __('settings:api_keys') ?></a></li>
	</ul>

	<div id="general">
		<div class="row">
			<label for="site_name"><?php echo __('settings:site_name') ?></label>
			<input type="text" name="site_name" value="<?php echo $settings['site_name']; ?>" class="txt" />&nbsp;<?php echo form_error('site_name'); ?>
		</div>

        <div class="row">
            <label for="labguage"><?php echo __('settings:language') ?></label>
            <div class="sel-item">
                <?php echo form_dropdown('language', $languages, Settings::get('language'), 'id="language"'); ?>
            </div>
        </div>
        
        <div class="row">
            <label for="timezone"><?php echo __('settings:timezone') ?></label>
            <div class="sel-item">
                <?php echo form_dropdown('timezone', $this->config->item('timezones'), Settings::get('timezone'), 'id="timezone"'); ?>
            </div>
        </div>
            
		<div class="row">
			<label for="notify_email"><?php echo __('settings:notify_email') ?></label>
			<input type="text" name="notify_email" value="<?php echo $settings['notify_email']; ?>" class="txt" />&nbsp;<?php echo form_error('notify_email'); ?>
		</div>
	    
		<div class="row">
		    <label><?php echo __('settings:bcc') ?></label>
		    <input type="checkbox" name="bcc" <?php echo $settings['bcc'] == 1 ? 'checked="checked"' : 0;?> value="1" class="txt" />
		    <div style="float:left;margin-top:4px;"><?php echo __('settings:automaticallybccclientemail');?></div>
		</div>
	    
		<div class="row">
		    <label>Email Server</label>
		    <div class="sel-item">
			<?php echo form_dropdown('email_server', $email_servers, $email['type']);?>
			</div>
		</div>
		<div class="smtp">
		    <div class="row">
			<label>SMTP Host</label>
			<input type="text" name="smtp_host" value="<?php echo $email['smtp_host'];?>" class="txt" />
		    </div>

		    <div class="row">
			<label>SMTP Username</label>
			<input type="text" name="smtp_user" value="<?php echo $email['smtp_user'];?>" class="txt" />
		    </div>

		    <div class="row">
			<label>SMTP Password</label>
			<input type="password" name="smtp_pass" value="<?php echo $email['smtp_pass']; ?>" class="txt" />
		    </div>

		    <div class="row">
			<label>SMTP Port</label>
			<input type="text" name="smtp_port" value="<?php echo $email['smtp_port']; ?>" class="txt" />
		    </div>
		</div>
	    
		<div class="row sendmail">
		    <label>Sendmail Path</label>
		    <input type="text" name="mailpath" value="<?php echo $email['mailpath']; ?>" class="txt" />
		</div>
		<div class="gmail">
		    <div class="row">
			<label>Gmail Email</label>
			<input type="text" name="gmail_user" value="<?php echo $email['gmail_user']; ?>" class="txt" />
		    </div>

		    <div class="row">
			<label>Gmail Password</label>
			<input type="password" name="gmail_pass" value="<?php echo $email['gmail_pass']; ?>" class="txt" />
		    </div>
		</div>
		<div class="gapps">
		    <div class="row">
			<label>Google Apps Email Address</label>
			<input type="text" name="gapps_user" value="<?php echo $email['gapps_user']; ?>" class="txt" />
		    </div>

		    <div class="row">
			<label>Google Apps Password</label>
			<input type="password" name="gapps_pass" value="<?php echo $email['gapps_pass']; ?>" class="txt" />
		    </div>
		</div>

		<input type="hidden" name="paypal_email" value="" />

		<div class="row">
			<label for="currency"><?php echo __('settings:currency') ?></label>
			<div class="sel-item">
			<?php echo form_dropdown('currency', $currencies, $settings['currency']); ?>
			</div>
		</div>
		
		<div class="row">
			<label for="theme"><?php echo __('settings:theme') ?></label>
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
			<label for="admin_theme"><?php echo __('settings:admin_theme') ?></label>
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
			<label for="license_key"><?php echo __('global:license_key') ?> <span style="font-size:80%">(<?php echo __('global:version') ?>:&nbsp;<?php echo Settings::get('version'); ?>)</span></label>
			<input type="text" name="license_key" value="<?php echo $settings['license_key']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="admin_name"><?php echo __('settings:admin_name') ?></label>
			<input type="text" name="admin_name" value="<?php echo $settings['admin_name']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="date_format"><?php echo __('settings:date_format') ?></label>
			<input type="text" name="date_format" value="<?php echo $settings['date_format']; ?>" class="txt" />
		</div>

		<div class="row">
			<label for="task_time_interval"><?php echo __('settings:task_time_interval') ?></label>
			<input type="text" name="task_time_interval" value="<?php echo $settings['task_time_interval']; ?>" class="txt" size="3" />
		</div>

		<div class="row">
			<label for="mailing_address"><?php echo __('settings:mailing_address') ?></label>
			<textarea name="mailing_address" rows="6"><?php echo $settings['mailing_address']; ?></textarea>
		</div>
		
	</div><!--/general-->

	<div id="taxes">
		<table class="pc-table" cellspacing="0" style="width: 400px;">
			<thead>
			<tr>
				<th><?php echo __('settings:tax_name') ?></th>
				<th><?php echo __('settings:tax_value') ?> (%)</th>
				<th><?php echo __('settings:tax_reg') ?></th>
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
					<td><?php echo form_input(array(
						'name' => 'tax_reg['.$id.']',
						'value' => set_value('tax_reg['.$id.']', @$tax['reg']),
						'class' => 'txt small'
					)); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table><br />
		<a href="#" id="add-tax" class="yellow-btn"><span><?php echo __('settings:add_tax') ?></span></a>
		<br /><br />
	</div><!--/taxes-->


	<div id="currencies">
		<table class="pc-table" cellspacing="0" style="width: 400px;">
			<thead>
			<tr>
				<th><?php echo __('settings:currency_name') ?></th>
				<th><?php echo __('settings:currency_code') ?></th>
				<th><?php echo __('settings:exchange_rate') ?></th>
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
		<a href="#" id="add-currency" class="yellow-btn"><span><?php echo __('settings:add_currency') ?></span></a>
		<br /><br />
	</div><!--/currencies-->
	

	<div id="templates">
		<div class="row">
		  <label for="email_new_invoice"><?php echo __('settings:new_invoice') ?></label>
			<?php echo form_textarea(array(
				'name'	=> 'email_new_invoice',
				'id'	=> 'email_new_invoice',
				'rows'	=> 8,
				'cols'	=> 70,
				'value'	=> $settings['email_new_invoice']
			)); ?>
		</div>
		<div class="row">
		  <label for="email_new_proposal"><?php echo __('settings:new_proposal') ?></label>
			<?php echo form_textarea(array(
				'name'	=> 'email_new_proposal',
				'id'	=> 'email_new_proposal',
				'rows'	=> 8,
				'cols'	=> 70,
				'value'	=> $settings['email_new_proposal']
			)); ?>
		</div>
		<div class="row">
		  <label for="email_paid_notification"><?php echo __('settings:paid_notification') ?></label>
			<?php echo form_textarea(array(
				'name'	=> 'email_paid_notification',
				'id'	=> 'email_paid_notification',
				'rows'	=> 8,
				'cols'	=> 70,
				'value' => $settings['email_paid_notification']
			)); ?>
		</div>
		<div class="row">
			<label for="email_receipt"><?php echo __('settings:payment_receipt') ?></label>
			<?php echo form_textarea(array(
				'name'	=> 'email_receipt',
				'id'	=> 'email_new_receipt',
				'rows'	=> 8,
				'cols'	=> 70,
				'value' => $settings['email_receipt']
			)); ?>
		</div>
	</div><!--/templates-->
        
        <div id="branding">
            <div class="row">
                <label for="logo"><?php echo __('settings:logo') ?></label>
                <input type="file" name="logo[]" />
		<?php if (logo(true, false) != '') :?>
		<a href="<?php echo site_url('admin/settings/remove_logo');?>" class="icon delete" title="<?php echo __('settings:removelogo')?>"></a>
		<?php endif;?>
		<?php echo logo(true); ?>
		<div class="explanation"><?php echo __('settings:logodimensions');?><br /><?php echo __('settings:logoformatsallowed');?></div>
            </div>

			<div class="row">
				<label for="email_receipt"><?php echo __('settings:frontend_css') ?></label>
				<?php echo form_textarea(array(
					'name'	=> 'frontend_css',
					'id'	=> 'frontend_css',
					'rows'	=> 8,
					'cols'	=> 70,
					'value' => $settings['frontend_css']
				)); ?>
			</div>

			<div class="row">
				<label for="email_receipt"><?php echo __('settings:backend_css') ?></label>
				<?php echo form_textarea(array(
					'name'	=> 'backend_css',
					'id'	=> 'backend_css',
					'rows'	=> 8,
					'cols'	=> 70,
					'value' => $settings['backend_css']
				)); ?>
			</div>
			
        </div>
        
        <div id="payment">
            <?php foreach (Gateway::get_gateways() as $gateway) : ?>
            <?php $enabled = $gateway['enabled'] ? 'checked="checked"' : ''; ?>
            <div class="gateway">
                <h2><?php echo $gateway['title'] ?></h2>
                <div class="gateway-input row">
                    <label for="<?php echo $gateway['gateway'];?>-enabled"><?php echo __('global:is_enabled') ?></label>
                    <input type="checkbox" class="enabled" value="1" id="<?php echo $gateway['gateway'];?>-enabled" name="gateways[<?php echo $gateway['gateway'];?>][enabled]" <?php echo $enabled;?> />
                </div>
                <div class="gateway-fields">
                    <?php foreach ($gateway['fields'] as $field => $title) : ?>
                        <div class="gateway-input row">
                            <label for="<?php echo $gateway['gateway'];?>-<?php echo $field;?>"><?php echo $title;?></label>
                            <input type="text" class="txt text" id="<?php echo $gateway['gateway'];?>-<?php echo $field;?>" value="<?php echo isset($gateway['field_values'][$field]) ? $gateway['field_values'][$field] : '';?>" name="gateways[<?php echo $gateway['gateway'];?>][<?php echo $field;?>]" />
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
	
	<div id="update">
	    <?php if (!$temporary_no_internet_access) :?>
		<div class="row">
		    <h3 class="latest-version"><?php echo __('settings:'.($outdated ? 'newversionavailable' : 'uptodate'), array($latest_version));?></h3>
		    <?php if ($outdated) {?>
			<?php if ($conflicted_files != array()) :?>
			    <div class="conflicted"><p class="conflict-warning">Pancake will have to overwrite <?php echo count($conflicted_files);?> files that you have modified manually!</p>
				<ul>
				<?php foreach($conflicted_files as $file => $operation) :?>
				    <li><?php echo $operation == 'M' ? '[You modified]' : '[You deleted]'; ?> <?php echo $file; ?></li>
				<?php endforeach;?>
				</ul>
			    </div>
			    <p class="conflict-reviewfiles"><?php echo __('update:review_files');?><br /><br /><?php echo __('update:ifyourenotsurecontactus')?></p>
			<?php endif;?>
			<?php if (($this->update->write or $this->update->ftp)) :?>
			    <div class="cf"><a href="<?php echo site_url('admin/upgrade/update');?>" class="yellow-btn" ><span><?php echo __('settings:updatenow');?></span></a></div>
			<?php else:?>
			    <p class="youneedtoconfigurefirst"><?php echo __('settings:youneedtoconfigurefirst');?></p>
			<?php endif;?>
			<h3 class="whatschanged"><?php echo __('update:whatschanged', array($latest_version));?></h3>
			<div class="changelog"><?php echo $changelog;?></div>
		    <?php } ?>
		</div>
		<div class="row auto-update">
		    <label>When new updates are available</label>
		    <div class="sel-item">
			<select name="auto_update">
			    <option value="1" <?php echo ($settings['auto_update'] == 1 or (isset($_POST['auto_update']) and $_POST['auto_update'] == 1)) ? 'selected="selected"' : '';?>>Download and install automatically</option>
			    <option value="0" <?php echo ($settings['auto_update'] == 0 or (isset($_POST['auto_update']) and $_POST['auto_update'] == 0)) ? 'selected="selected"' : '';?>>Don't install update, just notify me</option>
			</select>
		    </div>
		</div>
		<?php if (!$this->update->write) : ?>
		    <h3><?php echo __('settings:nophpupdates')?></h3>
		    <div class="row">
			<div class="row">
			    <label><?php echo __('settings:ftp_user') ?></label>
			    <input type="text" name="ftp_user" value="<?php echo $settings['ftp_user']; ?>" class="txt" />
			</div>
			<div class="row">
			    <label><?php echo __('settings:ftp_pass') ?></label>
			    <input type="password" name="ftp_pass" value="<?php echo $settings['ftp_pass']; ?>" class="txt" />
			</div>
			    <div class="row">
				<label><?php echo __('settings:ftp_host') ?></label>
				<input type="text" name="ftp_host" value="<?php echo empty($settings['ftp_host']) ? $guessed_ftp_host : $settings['ftp_host']; ?>" class="txt" />
			    </div>
			    <div class="row">
				<label><?php echo __('settings:ftp_path') ?></label>
				<input type="text" name="ftp_path" value="<?php echo $settings['ftp_path']; ?>" class="txt" />
			    </div>
			    <div class="row">
				<label><?php echo __('settings:ftp_port') ?></label>
				<input type="text" name="ftp_port" value="<?php echo $settings['ftp_port']; ?>" class="txt" />
			    </div>
			    <div class="row">
				<label><?php echo __('settings:ftp_pasv') ?></label>
				<input type="checkbox" name="ftp_pasv" <?php echo $settings['ftp_pasv'] == 1 ? 'checked="checked"' : 0;?> value="1" class="txt" />
			    </div>
		    </div>
		<?php endif; ?>
	    <?php else:?>
		<h3 class="latest-version"><?php echo __('update:internetissues');?></h3>
	    <?php endif;?>
	</div>
        
	<div id="feeds">
		<div class="row">
			<label for="rss_password"><?php echo __('settings:rss_password') ?></label>
			<?php echo form_input(array(
				'name' => 'rss_password',
				'id'	=> 'rss_password',
				'class'	=> 'txt',
				'value' => set_value('rss_password', $settings['rss_password']),
			)); ?>
		</div>
		<br />
		<h3><?php echo __('settings:default_feeds') ?></h3>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing"><?php echo __('global:paid') ?>:</label>
			<?php echo anchor('feeds/paid/10/'.PAN::setting('rss_password')); ?>
		</div>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing"><?php echo __('global:unpaid') ?>:</label>
			<?php echo anchor('feeds/unpaid/10/'.PAN::setting('rss_password')); ?>
		</div>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing"><?php echo __('global:overdue') ?>:</label>
			<?php echo anchor('feeds/overdue/10/'.PAN::setting('rss_password')); ?>
		</div>
		<div class="row" style="padding-top: 2px;">
			<label for="nothing"><?php echo __('settings:cron_job_feed') ?>:</label>
			<?php echo anchor('cron/invoices/'.PAN::setting('rss_password')); ?>
		</div>
		<br />
		<h3><?php echo __('settings:feed_generator') ?></h3>
		<div id="feed_generator" style="padding-top: 10px;">
			<div class="row">
				<label for="rss_type"><?php echo __('global:type') ?>:</label>
				<div class="sel-item">
				<select name="rss_type" id="rss_type">
					<option value="paid"><?php echo __('global:paid') ?></option>
					<option value="unpaid"><?php echo __('global:unpaid') ?></option>
					<option value="overdue"><?php echo __('global:overdue') ?></option>
				</select>
				</div>
			</div>
			<div class="row">
				<label for="rss_items"><?php echo __('global:items') ?>:</label>
				<input type="text" name="rss_items" id="rss_items" value="10" size="5" class="txt" />
			</div>
			<div class="row">
				<label for="nothing"><?php echo __('settings:your_link') ?>:</label>
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
				<th><?php echo __('settings:api_note') ?></th>
				<th><?php echo __('settings:api_key') ?></th>
				<th><?php echo __('global:created') ?></th>
				<th><?php echo __('global:remove') ?></th>
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
<input type="submit" class="hidden-submit" />
<?php echo form_close(); ?>

</div>
<?php echo asset::js('jquery.history.js'); ?>
<script type="text/javascript">
$(document).ready(function () {
    
    $('#frontend_css, #backend_css').each(function() {
	CodeMirror.fromTextArea(this);
    });
    
    $('.smtp, .gapps, .gmail, .sendmail').hide();
    $('.'+$('[name=email_server]').val()).show();
    
    $('[name=email_server]').change(function() {
	if ($('.smtp:visible, .gapps:visible, .gmail:visible, .sendmail:visible').length > 0) {
	    $('.smtp:visible, .gapps:visible, .gmail:visible, .sendmail:visible').slideUp(function() {
		$('.'+$('[name=email_server]').val()).slideDown();
	    });
	} else {
	    $('.'+$('[name=email_server]').val()).slideDown();
	}
	
    });
    
	$('.form_error').parent().find('input').addClass('error');
	$('.tabs').tabs({
	    select: function(event, ui) {  jQuery.history.load($(ui.panel).attr('id')); }
	});
	
	$.history.init(function(hash){
	    if(hash != "") {
		// Load the requested settings tab.
		$('.tabs').tabs( "select" , hash );
	    }
	},
	{ unescape: ",/" });
	
	$('#add-tax').click(function () {
		$(this).parent().children('table').children('tbody').append('<tr><td><?php echo form_input(array(
				'name' => 'new_tax_name[]',
				'class' => 'txt small'
			)); ?></td><td><?php echo form_input(array(
				'name' => 'new_tax_value[]',
				'class' => 'txt small'
			)); ?></td><td><?php echo form_input(array(
				'name' => 'new_tax_reg[]',
				'class' => 'txt small'
			)); ?></td></tr>');
					
			return false;
	});
	$('#add-currency').click(function () {
		$(this).parent().children('table').children('tbody').append('<tr><td><?php echo form_input(array(
			'name' => 'new_currency_name[]',
			'class' => 'txt small'
		)); ?></td><td><?php echo form_input(array(
			'name' => 'new_currency_code[]',
			'class' => 'txt small currency_code'
		)); ?></td><td><?php echo form_input(array(
			'name' => 'new_currency_rate[]',
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
		)); ?></td>'
		+ '<td>' + key + '<input type="hidden" name="new_key[]" value="' + key + '" /></td>'
		+ '<td><?php echo format_date(now()); ?></td>'
		+ '<td>'
		+ '	<a class="delete-key" href="#">'
		+ '		<img src="<?php echo base_url(); ?>third_party/themes/admin/pancake/img/ui_icons/cancel_24.png">'
		+ '	</a>'
		+ '</td>'
		+ '</tr>');

		return false;
	});
	
	$('.delete-key').live('click', function () {
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