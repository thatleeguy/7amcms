<?php echo form_open('wizard/step3'); ?>

  <input type="hidden" name="theme" value="pancake" />

  <h2>Last step!</h2>
<p>Additional Pancake configuration settings</p>
  <table style="width: 100%;">
	<tr>
	  <th class="col1"><label for="site_name">Site Name</label></th>
	  <td class="col2"><input name="site_name" type="text" size="20" value="<?php echo html_entity_decode(set_value('site_name')); ?>" /></td>
	  <td class="col3">Name of this payment app for title bar</td>
	</tr>
	<tr>
	  <th class="col1"><label for="license_key">Pancake License</label></th>
	  <td class="col2"><input name="license_key" type="text" size="20" value="<?php echo set_value('license_key'); ?>" /></td>
	  <td class="col3">License provided in the email with this app.</td>
	</tr>
	<tr>
	  <th class="col1"><label for="notify_email">Notify Email</label></th>
	  <td class="col2"><input name="notify_email" type="text" size="20" value="<?php echo set_value('notify_email'); ?>" /></td>
	  <td class="col3">Email to receive system notices.</td>
	</tr>
        
        <input name="paypal_email" type="hidden" value="" />

	<tr>
	  <th class="col1"><label for="username">Admin Username</label></th>
	  <td class="col2"><input name="username" type="text" size="20" value="<?php echo set_value('username', 'admin'); ?>" /></td>
	  <td class="col3">What you want to login with.</td>
	</tr>

	<tr>
	  <th class="col1"><label for="password">Password</label></th>
	  <td class="col2"><input name="password" type="password" size="20" /></td>
	  <td class="col3">Choose a password.</td>
	</tr>

	<tr>
	  <th class="col1"><label for="password_confirm">Confirm Password</label></th>
	  <td class="col2"><input name="password_confirm" type="password" size="20" /></td>
	  <td class="col3">Confirm the password.</td>
	</tr>

	<tr>
	  <th class="col1"><label for="first_name">First Name</label></th>
	  <td class="col2"><input name="first_name" type="text" size="20" value="<?php echo set_value('first_name'); ?>" /></td>
	  <td class="col3">IE: Karen</td>
	</tr>

	<tr>
	  <th class="col1"><label for="last_name">Last Name</label></th>
	  <td class="col2"><input name="last_name" type="text" size="20" value="<?php echo set_value('last_name'); ?>" /></td>
	  <td class="col3">IE: Smith</td>
	</tr>

	<tr>
	  <th class="col1"><label for="mailing_address">Mailing Address</label></th>
	  <td class="col2"><textarea name="mailing_address"><?php echo set_value('mailing_address'); ?></textarea></td>
	  <td class="col3">The address things should be mailed to.</td>
	</tr>

	<tr>
	  <th class="col1"><label for="tax_rate">Tax Rate</label></th>
	  <td class="col2"><input name="tax_rate" type="text" size="5" value="<?php echo set_value('tax_rate'); ?>" /></td>
	  <td class="col3">Expressed as a number eg: 5</td>
	</tr>

	<tr>
	  <th class="col1"><label for="currency">Currency</label></th>
	  <td class="col2"><?php echo form_dropdown('currency', $currencies, set_value('currency')); ?></td>
	  <td class="col3">Currency to be used throughout the system.</td>
	</tr>

  </table><br />
<p class="center"><button type="submit" class="button">Mmmm... Let's Eat!</button></p>

<?php echo form_close(); ?>