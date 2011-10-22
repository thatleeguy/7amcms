<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>

<title>Estimate #<?php echo $invoice['invoice_number'];?> | <?php echo Settings::get('site_name'); ?></title>

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

<body class="estimate <?php echo is_admin() ? 'admin' : 'not-admin';?> <?php if ($pdf_mode): ?>pdf_mode pdf<?php else: ?>not-pdf<?php endif;?>">
<?php if( ! $pdf_mode): ?>
	<div id="buttonBar">

		<div id="buttonHolders">
		<?php if (is_admin()): ?>
			<?php echo anchor('admin/invoices/estimates', 'Go to Admin &rarr;', 'class="button"'); ?>
		<?php endif; ?>
		<div id="pdf">
			<a href="<?php echo site_url('pdf/'.$invoice['unique_id']); ?>" title="Download PDF" id="download_pdf" class="button">Download PDF</a>
		</div><!-- /pdf -->

		</div><!-- /buttonHolders -->

	</div><!-- /buttonBar -->
<?php endif; ?>
	<div id="wrapper">

		<div id="header">
        
        
        
        <div id="envelope">
				
<table border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td width="150px"><h2>Estimate</h2>
							 
              </td>
 						<td width="450px" style="text-align:right">
							<h1 id="pancake_payments"><?php echo Settings::get('site_name'); ?></h1>
							<p><?php echo Settings::get('site_name'); ?><br /><?php echo nl2br(Settings::get('mailing_address')); ?></p>
						</td>
						
					</tr>
				</table>
				

		  </div>
   
			<div id="clientInfo">
            <div id="envelope2">
              <table border="0" cellspacing="5" cellpadding="5">
                <tr>
                  <td width="250px"><h2>Bill to: <?php echo $invoice['company'];?></h2>
                    <p><?php echo $invoice['company'];?> - <?php echo $invoice['first_name'].' '.$invoice['last_name'];?><br />
                  <?php echo nl2br($invoice['address']);?></p></td>
                  <td width="350px" style="text-align:right"><p><span id="invoice_number2"><strong>Estimate #:</strong> <?php echo $invoice['invoice_number'];?><br /></span></p></td>
                </tr>
              </table>
              <br />
            </div>
		  </div><!-- /clientInfo -->


    <br />



		</div><!-- /header -->
<?php echo $template['body']; ?>
		<div id="footer">

		</div><!-- /footer -->

	</div><!-- /wrapper -->

</body>
</html>