<div id="content">
	<table id="<?php echo (isset($is_estimate) and $is_estimate) ? 'estimate_table' : ''; ?>" cellspacing="0">
		<thead>
		<tr>
			<th class="column_1" style="text-align:left"><?php echo __('global:description'); ?> </th>
			<th class="column_2"><?php echo __('invoices:timequantity');?></th>
			<th class="column_3"><?php echo __('invoices:ratewithcurrency', array($invoice['currency_code'] ? $invoice['currency_code'] : Currency::code()));?></th>
			<th class="column_4"><?php echo __('invoices:taxable');?></th>
			<th class="column_5"><?php echo __('invoices:total');?></th>
		</tr>
		</thead>

		<tbody>
			<?php
            if ( ! empty($invoice['items'])):
			$class = '';
			foreach( $invoice['items'] as $item ):
			?>
				<tr class="<?php echo $class; ?>">
					<td class="column_1">
						<strong><?php echo $item['name']; ?></strong>
						<?php if ($item['description']): ?>
							<p><?php echo $item['description']; ?></p>
						<?php endif; ?>
					</td>
					<td class="column_2"><?php echo $item['qty']; ?></td>
					<td class="column_3"><?php echo Currency::format($item['rate'], $invoice['currency_code']); ?></td>
					<td class="column_4"><?php echo $item['tax_id'] ? __('global:Y') : __('global:N'); ?></td>
					<td class="column_5"><?php echo Currency::format($item['total'], $invoice['currency_code']); ?></td>
				</tr>
			<?php
			$class = ($class == '' ? 'alt' : '');
			endforeach;
                        endif;
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3" rowspan="4" class="invoice-description">
				
				<?php if ( ! empty($invoice['description'])): ?>
				<h3><?php echo __('global:description');?>:</h3>
				<p><?php echo auto_typography($invoice['description']);?></p>
				<?php endif; ?>
	
				<?php if ($invoice['has_tax_reg']): ?>
				<h3><?php echo __('settings:taxes') ?></h3>
				<ul id="taxes">
					<?php foreach ($invoice['taxes'] as $id => $total ):
						$tax = Settings::tax($id);
						if (empty($tax['reg'])) continue;
					?>
						<li class="<?php echo underscore($tax['name']) ?>">
							<span class="name"><?php echo $tax['name'] ?>:</span>
							<span class="reg"><?php echo $tax['reg'] ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
	
				</td>
				<td class="total-heading"><?php echo __('invoices:subtotal');?>:</td>
				<td class="column_5"><?php echo Currency::format($invoice['sub_total'], $invoice['currency_code']); ?></td>
			</tr>
		<?php foreach( $invoice['taxes'] as $id => $total ):
			$tax = Settings::tax($id);
		?>
			<tr>
				<td class="total-heading"><?php echo $tax['name'].' ('.$tax['value'].'%):'; ?></td>
				<td class="column_5"><?php echo Currency::format($total, $invoice['currency_code']); ?></td>
			</tr>
		<?php endforeach; ?>
			<tr>
				<td class="total-heading"><?php echo __('invoices:totaltax');?>:</td>
				<td class="column_5"><?php echo Currency::format($invoice['tax_total'], $invoice['currency_code']); ?></td>
			</tr>
			<tr>
				<td class="total-heading"><strong><?php echo __('invoices:total');?>:</strong></td>
				<td class="column_5"><strong><?php echo Currency::format($invoice['total'], $invoice['currency_code']); ?></strong></td>
			</tr>

		</tfoot>
	</table>
<?php if (!isset($is_estimate)) : ?>    
    <?php if (count(Gateway::get_frontend_gateways($invoice['real_invoice_id'])) > 0) : ?>
        <?php if (count($invoice['partial_payments']) > 1) : ?>
        <h3><?php echo __('partial:partialpayments');?></h3>
        <div class="payment-plan">
                    <ol>
                        <?php foreach ($invoice['partial_payments'] as $part) : ?>
                            <li>
                                <p>
                                    <span class="amount"><?php echo Currency::format($part['billableAmount'], $invoice['currency_code']); ?></span> <?php if ($part['due_date'] != 0) : ?><?php echo __('partial:dueondate', array('<span class="dueon">'.format_date($part['due_date']).'</span>'));?><?php endif; ?> <?php echo (empty($part['notes'])) ? '' : '- '.$part['notes']; ?> &raquo;
                                    <?php if (!$part['is_paid']) : ?>
                                    <?php if ($pdf_mode) : ?>
                                        <?php echo __('partial:topaynowgoto', array('<a href="'.$part['payment_url'].'">'.$part['payment_url'].'</a>'));?>
                                    <?php else: ?>
                                        <?php echo anchor($part['payment_url'], __('partial:proceedtopayment'), 'class="simple-button"'); ?>
                                    <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo __('partial:partpaidthanks');?>
                                    <?php endif; ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ol>
            </div>
        <?php endif;?>
    <?php endif;?>
<?php endif ; ?>    
	<?php if ( ! empty($invoice['notes'])): ?>
	<h3><?php echo __('global:notes');?>:</h3>
	<p><?php echo auto_typography($invoice['notes']);?></p>
	<?php endif; ?>
<?php if (!isset($is_estimate)) : ?> 
<?php if ($files): ?>
<div id="files">
	<h3><?php echo __('invoices:filestodownload'); ?></h3>
<?php if ( ! $is_paid): ?>
	<p><?php echo __('invoices:fileswillbeavailableafterpay');?></p>
<?php endif; ?>
	<ul>
	<?php foreach ($files as $file): ?>
            <?php $ext = explode('.', $file['orig_filename']); end($ext); $ext = current($ext); ?>
            <?php $bg = $pdf_mode ? '' : asset::get_src($ext.'.png', 'img'); ?>
            <?php $style = empty($bg) ? '' : 'style="background-image: url('.$bg.')"'; ?>
	<?php if ($is_paid): ?>
		<li><a class="file-to-download" <?php echo $style;?> href="<?php echo site_url('files/download/'.$invoice['unique_id'].'/'.$file['id']);?>"><?php echo $file['orig_filename'];?></a></li>
	<?php else: ?>
                <li class="file-to-download" <?php echo $style;?> ><?php echo $file['orig_filename']; ?></li>
	<?php endif; ?>
	<?php endforeach; ?>
	</ul>
        </div><!-- /notes -->
<?php endif; ?>
        <?php endif; ?>
</div><!-- /content -->