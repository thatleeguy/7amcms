<div id="partial-payment-details">
    <form class="invoice-block partial-payment-details">
        <div class="row"><label><?php echo __('partial:paymentmethod');?></label><div class="sel-item"><?php echo form_dropdown('payment-gateway', Gateway::get_enabled_gateway_select_array(), $gateway, 'class="not-uniform"'); ?></div></div>
        <div class="row"><label><?php echo __('partial:paymentstatus');?></label><div class="sel-item"><?php echo form_dropdown('payment-status', array('Completed' => __('gateways:completed'), 'Pending' => __('gateways:pending'), 'Refunded' => __('gateways:refunded'), '' => __('gateways:unpaid')), $status, 'class="not-uniform"'); ?></div></div>
	<div class="row"><label><?php echo __('partial:paymentdate');?></label><input type="text" class="text txt datePicker" name="payment-date" value="<?php echo $date;?>"></div>
	<div class="row"><label><?php echo __('partial:transactionfee');?></label><label for="fee" class="use-label"><?php echo $currency;?></label><input type="text" class="text txt" id="fee" name="transaction-fee" value="<?php echo $fee;?>"></div>
        <div class="row"><label><?php echo __('partial:transactionid');?></label><input type="text" name="payment-tid" class="text txt" value="<?php echo $tid;?>"></div>
        <div class="row"><label></label><a href="#" class="yellow-btn savepaymentdetails"><span><?php echo __('partial:savepaymentdetails');?></span></a></div>  
        <input type="submit" class="hidden-submit" />
    </form>
</div>