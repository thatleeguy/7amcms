<div class="estimate-selector" data-inserting="<?php echo __('estimates:attachingtoproposal');?>">
    <h2><?php echo __('proposals:selected_attachments') ?></h2>
    <?php if (count($estimates) > 0) :?>
        <?php echo form_dropdown('estimate', $estimates, $client_id, 'id="estimate-picker"'); ?>
    <?php else: ?>
    <p><?php echo __('estimates:noestimatetitle') ?></p>
    <?php endif;?>
    <div class="estimate-link-container">
        <?php if (count($estimates) > 0) :?>
        <a class="pickEstimate" href="#"><?php echo __('proposals:attach_selected_estimate');?></a> or 
        <?php endif;?>
        
		<?php echo anchor('admin/invoices/create_estimate/iframe/'.$client_id, lang('estimates:createnew'), array('class'=>'createEstimate', 'rev'=>'iframe|700')) ?>
    </div>
</div>
<script>$('#estimate-picker').change(function() {
    $('.createEstimate').attr('href', '<?php echo site_url('admin/invoices/create_estimate/iframe');?>/'+$(this).val()).facebox();
}); $('.createEstimate').facebox();</script>