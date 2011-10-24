<div class="invoice-block">

    <ul class="btns-list">
		<li><a href="<?php echo site_url('admin/projects/tasks/create/'.$project->id); ?>" class="yellow-btn fire-ajax"><span><?php echo __('tasks:create') ?></span></a></li>	
		<li><a href="<?php echo site_url('admin/projects/times/create/'.$project->id); ?>" class="yellow-btn fire-ajax"><span><?php echo __('projects:add_time') ?></span></a></li>	
		<?php if (count($tasks)): ?>
		<li><a href="<?php echo site_url('admin/invoices/create/' . $project->id); ?>" class="yellow-btn"><span><?php echo __('projects:generate_invoice') ?></span></a></li>
                <li><a href="<?php echo site_url('timesheet/' . $project->unique_id); ?>" class="yellow-btn"><span><?php echo __('timesheet:view_pdf') ?></span></a></li>
		<?php endif; ?>
		<li><a href="<?php echo site_url('admin/projects/edit/'.$project->id); ?>" class="yellow-btn fire-ajax"><span><?php echo __('projects:edit') ?></span></a></li>
		<li><a href="<?php echo site_url('admin/projects/delete/'.$project->id); ?>" class="yellow-btn fire-ajax"><span><?php echo __('projects:delete') ?></span></a></li>
    </ul><!-- /btns-list end -->

	<br style="clear: both;" />
	
    <div id="ajax_container"></div>

    <div class="head-box">
	   <h3 class="ttl ttl3"><?php echo __('projects:project') ?>: <?php echo $project->name; ?></h3>
		<p class="details"><?php echo __('global:client') ?>: <?php echo $project->first_name; ?> <?php echo $project->last_name; ?> - <?php echo $project->company; ?> | <?php echo __('invoices:due') ?>: <?php echo format_date($project->due_date); ?> | <?php echo __('tasks:default_rate') ?>: <?php echo Currency::format($project->rate); ?></p>
    </div>
    
</div>

<?php if (count($tasks)): ?>

<div class="table-area">

    <table id="paidRequestTable" class="listtable pc-table table-activity" cellspacing="0">
    	<thead>
    		<tr>
    		    <th class="cell1"><?php echo __('tasks:task') ?></th>
				<th class="cel12"><?php echo __('tasks:timer') ?></th>
    		    <th class="cel12 centered-th"><?php echo __('tasks:hours') ?></th>
    		    <th class="cel12 centered-th"><?php echo __('tasks:rate') ?></th>
                <th class="cell4 centered-th"><?php echo __('projects:due_date') ?></th>
    			<th class="cell5"><?php echo __('global:actions') ?></th>
    		</tr>
    	</thead>
    	<tbody>
    	
    	<?php foreach ($tasks as $task): ?>
    	<tr id="task-row-<?php echo $task['id']; ?>">
            <?php echo $this->load->view('_task_row', array('task' => $task)); ?>
    	</tr>
    	<?php endforeach; ?>
    	</tbody>
    </table>
</div>

<div class="pagination">
	<?php echo $this->pagination->create_links(); ?>
</div>

<?php else: ?>

<div class="invoice-block">
	<div class="reminder_notification">
		<h4><?php echo __('tasks:no_task_title') ?></h4>
		<p><?php echo __('tasks:no_task_message') ?></p>
	</div>
</div>    	

<?php endif; ?>

<?php if ($project->description): ?>

<div class="invoice-block">
	<div id="project-notes">
		<div class="head-box">
		   <h3 class="ttl ttl3"><?php echo __('global:notes') ?></h3>
	    </div>
		<?php echo auto_typography($project->description); ?>
	</div><!-- /project-notes -->
</div>
<?php endif; ?>

<script type="text/javascript">
	$(".fire-ajax").click(function (e) {
		$('#ajax_container').hide();
		e.preventDefault();
		$.get($(this).attr('href'), function (data) {
			$('#ajax_container').html(data).slideDown();
		});
	});
</script>