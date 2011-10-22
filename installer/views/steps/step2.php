<h2>Step 2: Mix our dry ingredients.</h2>
<?php if (isset($error)): ?>
<p class="notification error"><?php echo $error; ?></p>
<?php endif; ?>
<?php echo form_open('wizard/step2', 'id="form" name="form"'); ?>
	<p>Enter your database connection settings.</p>
	<table style="width: 100%;">
		<tr>
	    	<th class="col1"><label for="hostname">Database Host:</label></th>
			<td class="col2"><input name="hostname" type="text" size="20" value="<?php echo $hostname; ?>" /></td>
	    	<td class="col3">localhost or an ip (192.168.0.1)</td>
	    </tr>
	    <tr>
	    	<th class="col1"><label for="database">Database Name:</label></th>
	    	<td class="col2"><input name="database" type="text" size="20" value="<?php echo $database; ?>"/></td>
	    	<td class="col3">The name of the database to use.</td>
	    </tr>
	    <tr>
	    	<th class="col1"><label for="dbprefix">Table Prefix:</label></th>
	    	<td class="col2"><input name="dbprefix" type="text" size="20" value="<?php echo $dbprefix; ?>"/></td>
	    	<td class="col3">The table prefix to use.</td>
	    </tr>
	    <tr>
	    	<th class="col1"><label for="username">Username:</label></th>
	    	<td class="col2"><input name="username" type="text" size="20" value="<?php echo $username; ?>" /></td>
	    	<td class="col3">Your MySQL username.</td>
	    </tr>
	    <tr>
	    	<th class="col1"><label for="password">Password:</label></th>
	    	<td class="col2"><input name="password" type="password" size="20" value="<?php echo $password; ?>" /></td>
	    	<td class="col3">Your MySQL password.</td>
	    </tr>
	</table><br />

	<p class="center"><a href="#" onclick="document['form'].submit()" class="button">Step 3</a></p>
<?php echo form_close(); ?>