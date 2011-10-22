<div id="form_container">
	<div class="invoice-block">
		<div class="head-box">
			<h3 class="ttl ttl3">Add Task</h3>
		</div><!-- /head-box end -->
		<div class="form-holder">

			<?php echo form_open('admin/projects/tasks/create/' . $project->id, array('id' => 'create_form')); ?>
			<fieldset>

				<div id="invoice-type-block" class="row">

					<div class="row">
						<label for="name"><?php echo __('global:name') ?></label>
						<?php echo form_input('name', set_value('name'), 'class="txt"'); ?>
					</div>
					<div class="row">
						<label for="rate">Hourly Rate (<?php echo $project->currency_code ? $project->currency_code : Currency::symbol(); ?>)</label>
						<?php echo form_input('rate', set_value('rate', isset($project) ? $project->rate : ''), 'class="txt"'); ?>
					</div>
					<div class="row">
						<label for="due_date">Due Date</label>
						<?php echo form_input('due_date', set_value('due_date') ? format_date(set_value('due_date')) : '', 'id="due_date" class="datePicker txt"'); ?>
					</div>
					<div class="row">
						<label for="notes"><?php echo __('global:notes') ?></label>
						<?php echo form_textarea('notes', set_value('notes'), 'id="notes" class="txt"'); ?>
					</div>
					<div class="row">
						<input type="hidden" name="project_id" value="<?php echo $project->id; ?>" />
						<a href="#" class="yellow-btn" onclick="$('#create_form').submit(); return false;"><span>Add Task</span></a>
					</div>
				</div>
			</fieldset>
                    <input type="submit" class="hidden-submit" />
			<?php echo form_close(); ?>
			
		</div>
	</div>
</div>

<br style="clear: both;" />
<?php echo asset::js('jquery.ajaxform.js'); ?>
<script type="text/javascript">
	$('#create_form').ajaxForm({
		dataType: 'json',
		success: showResponse
	});
	
	function showResponse(data)  {

		$('.notification').remove();

	    if (typeof(data.error) != 'undefined')
		{
			$('#form_container').before('<div class="notification error">'+data.error+'</div>');
		}
		else
		{
			$('#form_container').html('<div class="notification success">'+data.success+'</div>');
			setTimeout("window.location.reload()", 2000);
		}
	}
</script>