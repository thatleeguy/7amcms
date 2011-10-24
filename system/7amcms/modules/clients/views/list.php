<div class="invoice-block">
	
<?php if ($clients): ?>
<ul class="btns-list">
	<li><a href="<?php echo site_url('admin/clients/create'); ?>" title="<?php echo lang('clients:add') ?>" class="yellow-btn"><span><?php echo lang('clients:add') ?></span></a></li>
</ul><!-- /btns-list end -->
<?php endif; ?>

<br style="clear: both;" />
<div id="ajax_container"></div>

<div class="head-box">
	<h3 class="ttl ttl3"><?php echo lang('clients:title') ?></h3>
</div><!-- /head-box end -->
</div>
<?php if (empty($clients)): ?>
	
<div class="no_object_notification">
	<h4><?php echo lang('clients:noclienttitle') ?></h4>
	<p><?php echo lang('clients:noclientbody') ?></p>
	<p class="call_to_action"><a href="<?php echo site_url('admin/clients/create'); ?>" title="<?php echo lang('clients:add') ?>" class="yellow-btn"><span><?php echo lang('clients:add') ?></span></a></p>
</div><!-- /no_object_notification -->

<?php else: ?>

<div id="project_container">

	<div class="table-area">
	<table class="pc-table" cellspacing="0">
		<thead>
			<tr>
				<th><?php echo lang('global:name') ?></th>
				<th><?php echo lang('global:contacts') ?></th>
				<th><?php echo lang('global:unpaid') ?></th>
				<th><?php echo lang('global:paid') ?></th>
                <th><?php echo lang('clients:health_check')?></th>
				<th><?php echo lang('global:actions') ?></th>
			</tr>
		</thead>
	<tbody>

	<?php foreach($clients as $row): ?>
		<tr>
			<td>
				<a href="<?php echo site_url('admin/clients/view/'.$row->id); ?>"><?php echo $row->first_name;?> <?php echo $row->last_name;?></a><br />
				<?php echo $row->company;?>
			</td>
			<td><a href="mailto:<?php echo $row->email;?>">e: <?php echo $row->email;?></a><br />p: <?php echo $row->phone;?></td>
			<td><?php echo Currency::format($row->unpaid_total); ?></td>
			<td><?php echo Currency::format($row->paid_total); ?></td>
                        <td><div class="client-health-list">
	<div class="healthCheck">
		<span class="healthBar"><span class="paid" style="width:<?php echo $row->health['overall'];?>%"></span></span>
	</div><!-- /healthCheck -->
</div><!-- /invoice-block --></td>
			<td class="actions">
			<?php echo anchor('admin/clients/edit/'.$row->id, __('global:edit'), array('class' => 'icon edit', 'title' => __('global:edit'))); ?> 
			<?php echo anchor('admin/clients/delete/'.$row->id, lang('global:delete'), array('class' => 'icon delete', 'title' => __('global:delete'))); ?>
			</td>
		</tr>
	<?php endforeach; ?>

	</tbody>
	</table>
</div>
</div>

<div class="pagination">
	<?php echo $this->pagination->create_links(); ?>
	</div>

<?php endif; ?>
