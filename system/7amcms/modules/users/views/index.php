<div class="invoice-block">
<ul class="btns-list">
	<li><a href="<?php echo site_url('admin/users/create'); ?>" title="Create User" class="yellow-btn"><span>Create User</span></a></li>
</ul><!-- /btns-list end -->

<div id="ajax_container"></div>

<div class="head-box">
	<h3 class="ttl ttl3">Users</h3>
</div><!-- /head-box end -->
</div>

<div id="project_container">

	    <div class="table-area">
<table cellspacing="0" class="pc-table">
	<thead>
	<tr>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Email</th>
		<th>Group</th>
		<th>Status</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($users as $user):?>
		<tr>
			<td><?php echo $user['first_name']?></td>
			<td><?php echo $user['last_name']?></td>
			<td><?php echo $user['email'];?></td>
			<td><?php echo $user['group_description'];?></td>
			<td><?php echo ($user['active']) ? anchor("admin/users/deactivate/".$user['id'], 'Active') : anchor("admin/users/activate/". $user['id'], 'Inactive');?></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
</div>
</div>