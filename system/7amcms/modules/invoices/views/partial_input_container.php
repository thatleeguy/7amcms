<?php $parts = isset($parts) ? $parts : array('key' => 1); ?>
<div class="row hide-estimate">
    <label for="nothing"><?php echo lang('partial:partialpayments'); ?></label>
    <div class="partial-labels">
        <label for="partial-amount" class="partial-amount"><?php echo lang('partial:amount'); ?></label>
        <label for="partial-due_date" class="partial-due_date"><?php echo lang('partial:dueon'); ?></label>
        <label for="partial-notes" class="partial-notes"><?php echo lang('global:notes'); ?></label>
    </div>
    <div class="partial-input-container" data-markaspaid="<?php echo lang('partial:markaspaid'); ?>" data-paymentdetails="<?php echo lang('partial:paymentdetails'); ?>">
        <?php if (isset($_POST['partial-amount'])) : ?>
            <?php foreach (array_keys($_POST['partial-amount']) as $key) : ?>
                <?php $this->load->view('invoices/partial_input', array('key' => $key)); ?>
            <?php endforeach; ?> 
        <?php else: ?>
            <?php foreach ($parts as $part) : ?>
                <?php $this->load->view('invoices/partial_input', is_array($part) ? array_merge($part, array('action' => $action)) : array('action' => $action)); ?>
            <?php endforeach; ?> 
        <?php endif; ?>
    </div>
    <div class="partial-addmore">
        <a href="#" class="yellow-btn" data-disabled ="<?php echo lang('partial:disabledforrecurring'); ?>" data-enabled="<?php echo lang('partial:addanother'); ?>"><span><?php echo lang('partial:addanother'); ?></span></a>
    </div>
</div>