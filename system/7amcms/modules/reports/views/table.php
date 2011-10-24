<table class="pc-table report-html-table">
    <thead>
        <tr>
            <th class="cell1"><?php echo __('global:status'); ?></th>
            <th class="cell3"><?php echo __('global:details'); ?></th>
            <th class="cell5"><?php echo __('global:client'); ?></th>
            <th class="cell5"><?php echo __('global:actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php if (isset($row->proposal_number)) : ?>
                    <td class="cell1">
                        <span class="status-icon <?php echo ($row->status == 'ACCEPTED' ? 'green' : 'red'); ?>">
                            <?php echo __('proposals:' . (!empty($row->status) ? strtolower($row->status) : 'noanswer'), array(format_date($row->last_status_change))); ?>
                        </span>
                    </td>
                    <td class="cell3">
                        <?php $amount = ', estimated at <span class="'.($row->status == 'ACCEPTED' ? 'paid' : 'unpaid').'-amount">'.Currency::format($row->amount).'</span>';?>
                        <span><?php echo __('global:proposal'); ?> <?php echo anchor('admin/proposals/edit/' . $row->unique_id, '#' . $row->proposal_number); ?><?php echo $amount;?></span>
                        <span><?php echo $row->title; ?></span>
                        <span><?php echo ucfirst(($row->last_viewed > 0) ? (__('proposals:lastviewed', array(format_date($row->last_viewed), format_time($row->last_viewed)))) : __('proposals:neverviewed')) ?>.</span>
                    </td>
                <?php else: ?>
                    <td class="cell1">
                        <span class="status-icon <?php echo ((isset($row->overdue) and $row->overdue) ? 'red' : (($row->type == 'ESTIMATE' || (isset($row->paid) and $row->paid)) ? 'green' : 'red')); ?>">
                            <?php echo ((isset($row->overdue) and $row->overdue) ? __('global:overdue') : ($row->type == 'ESTIMATE' ? 'Estimate' : ((isset($row->paid) and $row->paid) ? __('invoices:paidon', array(format_date($row->payment_date))) : __('global:unpaid')))); ?>
                        </span>
                    </td>
                    <td class="cell3">
                        <span><?php echo $row->type == 'ESTIMATE' ? __('global:estimate') : __('global:invoice'); ?> <?php echo anchor('admin/invoices/edit/' . $row->unique_id, '#' . $row->invoice_number); ?> (<?php echo Currency::format($row->amount, $row->currency_symbol); ?>)</span>
                        <span><?php echo __('invoices:due') ?>: <?php echo ($row->due_date > 0) ? format_date($row->due_date) : 'n/a'; ?>
                            <?php $row->unpaid_amount = $this->ppm->getInvoiceUnpaidAmount($row->unique_id);
                            if ($row->unpaid_amount > 0) : ?>| <span class="unpaid-amount"><?php echo __('global:unpaid') ?>: <?php echo Currency::format($row->unpaid_amount, $row->currency_symbol); ?></span><?php endif; ?>
                            <?php $row->paid_amount = $this->ppm->getInvoicePaidAmount($row->unique_id);
                            if ($row->paid_amount > 0) : ?> | <span class="paid-amount"><?php echo __('global:paid') ?>: <?php echo Currency::format($row->paid_amount, $row->currency_symbol); ?></span><?php endif; ?>
                        </span>
                        <?php if ($row->is_recurring) :?>
                            <span>
                                <?php if ($row->id == $row->recur_id) :?>
                                <?php echo __('invoices:willreoccurin', array(format_date($this->invoice_m->getNextInvoiceReoccurrenceDate($row->id))))?>
                                <?php else: ?>
                                <?php echo __('invoices:thisisareoccurrence', array(anchor('admin/invoices/edit/' . $row->recur_id, '#' . $this->invoice_m->getInvoiceNumberById($row->recur_id))));?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($row->last_sent > 0) :?>
                            <span><?php echo __('invoices:clientlastnotifiedon', array(format_date($row->last_sent)));?></span>
                        <?php elseif ($row->is_recurring): ?>
			    <span><?php echo $row->auto_send ? __('invoices:willbesentautomatically', array(format_date($row->date_to_automatically_notify))) : '';?></span>
			<?php endif; ?>
			<?php if ($row->last_viewed > 0) :?>    
			<span><?php echo ucfirst(__('proposals:lastviewed', array(format_date($row->last_viewed), format_time($row->last_viewed)))); ?>.</span>
			<?php endif; ?>
                    </td>
                <?php endif; ?>

                <td class="cell5">
                    <a href="<?php echo site_url('admin/clients/view/' . $row->client_id); ?>">
                        <span><?php echo isset($row->proposal_number) ? $row->client_name : $row->first_name . ' ' . $row->last_name; ?></span>
                        <span><?php echo isset($row->proposal_number) ? $row->client_company : $row->company; ?></span>
                    </a>
                </td>
                <td class="cell5 actions">
                    <?php echo anchor((isset($row->proposal_number) ? 'proposal/' : '') . $row->unique_id, __('global:view'), array('class' => 'icon view', 'title' => __('global:view')) ); ?>
                    <?php echo anchor('admin/'.(isset($row->proposal_number) ? 'proposals/send' : (($row->type == 'ESTIMATE') ? 'estimates' : 'invoices').'/created').'/' . $row->unique_id, __('global:send_to_client'), array('class' => 'icon mail', 'title' => __('global:send_to_client'))); ?>
                    <?php echo anchor('admin/'.(isset($row->proposal_number) ? 'proposals' : (($row->type == 'ESTIMATE') ? 'estimates' : 'invoices')).'/edit/' . $row->unique_id, __('global:edit'), array('class' => 'icon edit', 'title' => __('global:edit'))); ?>
                    <?php echo anchor('admin/'.(isset($row->proposal_number) ? 'proposals' : (($row->type == 'ESTIMATE') ? 'estimates' : 'invoices')).'/delete/' . $row->unique_id, __('global:delete'), array('class' => 'icon delete', 'title' => __('global:delete'))); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>