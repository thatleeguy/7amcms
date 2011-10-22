<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>

<title><?php echo __('invoices:invoicenumber', array($invoice['invoice_number']));?> | <?php echo Settings::get('site_name'); ?></title>

<!--favicon-->
<link rel="shortcut icon" href="" />

<!--metatags-->
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />

<!-- CSS -->
<?php echo asset::css('invoice_style.css', array('media' => 'all'), NULL, $pdf_mode); ?>

<?php if (Settings::get('frontend_css')): ?>
	<style type="text/css"><?php echo Settings::get('frontend_css'); ?></style>
<?php endif; ?>

</head>

<body class="invoice <?php echo is_admin() ? 'admin' : 'not-admin';?> <?php if ($pdf_mode): ?>pdf_mode pdf<?php else: ?>not-pdf<?php endif;?>">
<?php if( ! $pdf_mode): ?>
	<div id="buttonBar">

		<div id="buttonHolders">
		<?php if (is_admin()): ?>
			<?php echo anchor('admin/invoices/'.((isset($is_estimate) and $is_estimate) ? 'estimates' : 'all'), 'Go to Admin &rarr;', 'class="button"'); ?>
		<?php endif; ?>
		<div id="pdf">
			<a href="<?php echo site_url('pdf/'.$invoice['unique_id']); ?>" title="Download PDF" id="download_pdf" class="button">Download PDF</a>
		</div><!-- /pdf -->

		<?php if( ! $is_paid and (count(Gateway::get_frontend_gateways($invoice['real_invoice_id'])) > 0)){ ?>
		<div id="paypal">
        	<a href="<?php echo $invoice['partial_payments'][$invoice['next_part_to_pay']]['payment_url']; ?>" class="button"><?php if (count($invoice['partial_payments']) > 1) : ?>Pay part #<?php echo $invoice['next_part_to_pay']; ?> of your invoice now<?php else: ?>Proceed to payment<?php endif;?></a>
		</div><!-- /paypal -->
		<?php }?>
		<?php if ($is_paid) :?>
		    <span class="paidon"><?php echo __('invoices:thisinvoicewaspaidon', array(format_date($invoice['payment_date'])));?></span>
		<?php elseif (!isset($is_estimate)) :?>
		    <span class="paidon"><?php echo __('invoices:thisinvoiceisunpaid');?></span>
		<?php endif;?>
		</div><!-- /buttonHolders -->

	</div><!-- /buttonBar -->
<?php endif; ?>
	<div id="wrapper">
		<div id="header">
			<div id="envelope" <?php if (!$pdf_mode):?> style="padding:60px 0 0 0" <?php endif; ?>>
				<table cellspacing="5" cellpadding="5" style="padding: 0 20px;">
					<tr>
						<td width="310px"><h2>Invoice</h2>
              			</td>
 						<td width="310px" style="text-align:right">
							<p><strong>Invoice #:</strong> <?php echo $invoice['invoice_number'];?><br /></p> 
							<p><strong>Due: </strong><?php echo $invoice['due_date'] ? format_date($invoice['due_date']) : '<em>n/a</em>';?></p>
		                    <?php if($invoice['is_paid'] == 1): ?>
		                    <br />
		                    <span><strong>Paid </strong>on <?php echo format_date($invoice['payment_date']);?></span>
		                      <?php endif; ?>
		                  </p>
							
						</td>
					</tr>
				</table>
			</div><!-- /envelope -->


			<div id="clientInfo">
            <div id="envelope2">
              <table cellspacing="5" cellpadding="5">
                <tr>
                  <td width="310px" style="vertical-align:top;"><h2>Bill to: <?php echo $invoice['company'];?></h2>
                    <p><?php echo $invoice['company'];?> - <?php echo $invoice['first_name'].' '.$invoice['last_name'];?><br />
                  <?php echo nl2br($invoice['address']);?></p></td>
                  <td width="310px" style="text-align:right;vertical-align:top;">
						<?php echo logo(false, false, 2);?>
						<p><?php echo nl2br(Settings::get('mailing_address')); ?></p>
					</td>
                </tr>
              </table>
              <br /> <br />
            </div>
		  </div><!-- /clientInfo -->



		</div><!-- /header -->
<?php echo $template['body']; ?>
		<div id="footer">

		</div><!-- /footer -->
</div><!-- /wrapper -->


<?php

// ====================
// = Remittence slips =
// ====================

/*
	If you wish to remove this option delete everyting between
	
	=== PAYMENT SLIP ====
	
	=== END PAYMENT SLIP ===
	
*/


?>



<?php // 	=== PAYMENT SLIP ====	 ?>

<?php if($pdf_mode): ?>
<div style="page-break-before: always;"></div>
<div id="wrapper">
 <div id="header">
  <div id="envelope">
   <table border="0" cellspacing="5" cellpadding="5">
    <tr>
     <td width="400px">
      <h2>How to Pay</h2>
      <p>View invoice online at <?php echo anchor($invoice['unique_id']); ?></p>
      <p>You may pay in person, online, or by mail using this payment voucher. Please provide your payment information below.<br/>
<br/><br/>
Enclosed Amount: __________________________________
      </p>
     </td>
     <td width="200px" style="text-align:right">
      <p>
      <strong>Invoice #:</strong> <?php echo $invoice['invoice_number'];?><br/>
      <strong>Total:</strong> <?php echo Currency::format($invoice['total'], $invoice['currency_code']); ?><br/>
      <strong>Due:</strong> <?php echo $invoice['due_date'] ? format_date($invoice['due_date']) : '<em>n/a</em>';?>
      </p>
      
      <h3>Mail To:</h3>      
      <p><?php echo Settings::get('site_name'); ?><br /><?php echo nl2br(Settings::get('mailing_address')); ?></p>
     </td>
     
    </tr>
   </table>
  </div>
 </div>
</div>
<?php endif; ?>
<?php // === END PAYMENT SLIP === ?>

</body>
</html>