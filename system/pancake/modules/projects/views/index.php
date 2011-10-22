<?php if (!IS_AJAX): ?>
	
<div class="invoice-block">
	
	<?php if ($projects): ?>
    <ul class="btns-list">
    	<li><a class="yellow-btn fire-ajax" id="create_project" href="<?php echo site_url('admin/projects/create'); ?>"><span><?php echo lang('projects:add'); ?></span></a></li>
    </ul><!-- /btns-list end -->
	<?php endif; ?>
	<br style="clear: both;" />
    <div id="ajax_container"></div>
    
    <div class="head-box">
    	<h3 class="ttl ttl3"><?php echo lang('projects:alltitle'); ?></h3>
    </div>

</div>
    
<?php endif; ?>

<?php if (!$projects): ?>
	<div class="no_object_notification">
    <h4><?php echo lang('projects:noprojecttitle'); ?></h4>
	<p><?php echo lang('projects:noprojecttext'); ?></p>
	<p class="call_to_action"><a class="yellow-btn fire-ajax" id="create_project" href="<?php echo site_url('admin/projects/create'); ?>"><span><?php echo lang('projects:add'); ?></span></a></p>
	</div>
<?php else: ?>
    <div id="project_container">

    <div class="table-area">
        
        <table id="project_list" class="pc-table" cellspacing="0">
            <thead>
                <tr>
                    <th class="cell1"><?php echo __('global:name') ?></th>
					<th><?php echo __('global:client') ?></th>
                    <th><?php echo __('tasks:hours') ?></th>
                    <th class="cell2"><?php echo __('projects:due_date') ?></th>
                    <th class="cell3"><?php echo __('global:actions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($projects as $row): ?>
                <tr id="project-<?php echo $row->id; ?>">
                    <td class="cell1"><?php echo anchor('admin/projects/view/'. $row->id, $row->name); ?> 
                        <?php if ($row->total_tasks > 0): ?>
                        (<?php echo number_format(($row->complete_tasks / $row->total_tasks) * 100, 1); ?>% Complete)
                        <?php else: ?>
                        (No Tasks)
                        <?php endif; ?>
                    </td>
					<td><a href="<?php echo site_url('admin/clients/view/'.$row->client_id); ?>"><?php echo $row->first_name.' '.$row->last_name.($row->company ? ' - '.$row->company : ''); ?></a></td>
                    <td><?php echo $this->project_m->getTotalHoursForProject($row->id, true);?></td>
                    <td class="cell1"><?php echo $row->due_date ? format_date($row->due_date) : 'n/a'; ?></td>
                    <td class="cell1">
						<a href="<?php echo site_url('admin/invoices/create/' . $row->id); ?>">+ <?php echo lang('invoices:newinvoice') ?></a>
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
<?php if ( ! IS_AJAX): ?>
</div>
<?php endif; ?>
<?php echo asset::js('jquery.history.js'); ?>
<script type="text/javascript">
	$(".fire-ajax").click(function (e) {
		$('#ajax_container').hide();
		e.preventDefault();
		$.get($(this).attr('href'), function (data) {
			$('#ajax_container').html(data).slideDown();
		});
		return false;
	});
</script>