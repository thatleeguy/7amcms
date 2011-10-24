<h2 class="ttl ttl3"><?php echo __('tasks:view_entries') ?></h2>

<table id="view-entries" class="listtable pc-table table-activity" style="margin: 10px 0 0 0">
	<thead>
		<th class="cell1"><?php echo __('timesheet:date') ?></th>
		<th class="cell2"><?php echo __('timesheet:starttime') ?></th>
		<th class="cell3"><?php echo __('timesheet:endtime') ?></th>
		<th class="cell4"><?php echo __('timesheet:duration') ?></th>
		<th class="cell5"><?php echo __('global:remove') ?></th>
	</thead>
	
	<tbody>
<?php foreach ($entries as $entry): ?>
		<tr data-id="<?php echo $entry->id ?>">
			<td class="cell1 date">
				<span><?php echo format_date($entry->date); ?></span>
				<?php echo form_input('date', format_date($entry->date), 'id="date-'.$entry->id.'" class="datePicker txt" style="display:none; width:100px;"') ?>
			</td>
			<td class="cell2 start_time">
				<span><?php echo $entry->start_time; ?></span>
				<?php echo form_input('start_time', $entry->start_time, 'style="display:none; width:50px;"') ?>
			</td>
			<td class="cell3 end_time">
				<span><?php echo $entry->end_time; ?></span>
				<?php echo form_input('end_time', $entry->end_time, 'style="display:none; width:50px;"') ?>
			</td>
			<td class="cell4 duration"><?php echo format_seconds($entry->minutes * 60); ?></td>
			<td>
				<a href="#" class="delete-entry"><img src="<?php echo base_url(); ?>third_party/themes/admin/pancake/img/ui_icons/cancel_24.png" /></a>
			</td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<script>
jQuery(function($) {

	$('.start_time span, .end_time span, .date span').live('click', function() {		
		$(this).hide().siblings('input').show();
	});
	
	$('.start_time input, .end_time input, .date input').live('blur change', function(e) {
  
		var input = this;
		var row = $(this).closest('tr');

		$.post('<?php echo base_url() ?>admin/projects/times/ajax_set_entry', {
			'id' : row.data('id'),
			'start_time' : $('.start_time input', row).val(),
			'end_time' : $('.end_time input', row).val(),
			'date' : $('.date input', row).datepicker( "getDate" ).getTime()
		}, function(data) {
			
			$(input).hide().siblings('span').text(input.value).show();

			$('.duration', row).text(data.new_duration);
			
		}, 'json');
	
	});
	
	$('.delete-entry').click(function() {
		
		var row = $(this).closest('tr');
		var id = row.data('id');
		
		$.post(baseURL +'admin/projects/times/ajax_delete_entry', {
			'id' : row.data('id'),
		}, function() {
			row.slideUp('slow');
		});
	});
})
</script>

<style>
table#view-entries {
	width: 450px;
}
	
	table#view-entries td {
		width: 20%;
	}
</style>