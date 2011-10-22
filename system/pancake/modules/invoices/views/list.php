<div class="invoice-block">
	<ul class="btns-list">
		<li><a href="<?php echo site_url('admin/invoices/create_estimate'); ?>" class="yellow-btn"><span><?php echo lang('estimates:createnew') ?></span></a></li>
	</ul><!-- /btns-list end -->
	<div class="head-box">
		<h3 class="ttl ttl3"><?php echo $list_title; ?></h3>
	</div><!-- /head-box end -->
	<div class="form-holder">
		<?php echo form_open(uri_string()); ?>
		<fieldset>
		<div class="row">
			<label for="client_id"><?php echo lang('clients:filter') ?></label>
			<div class="sel-item"><?php echo form_dropdown('client_id', $clients_dropdown, $client_id, 'onchange="this.form.submit()"'); ?></div>
		</div>
		</fieldset>
		<?php echo form_close(); ?>
	</div>
<br />
</div>
<?php if (empty($invoices)): ?>

	<div class="invoice-block">
		<div  class="no_object_notification">
		<h4><?php echo lang('invoices:noinvoicetitle') ?></h4>
		<p><?php echo lang('invoices:noinvoicebody') ?></p>
		</div><!-- /no_object_notification -->
	</div>

<?php else: ?>

	<div class="table-area thirty-days">
		<?php $this->load->view('reports/table', array('rows' => $invoices)); ?>
	</div>

	<div class="pagination">
		<?php echo $this->pagination->create_links(); ?>
	</div>

<?php endif; ?>