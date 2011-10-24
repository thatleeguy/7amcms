<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pancake
 *
 * A simple, fast, self-hosted invoicing application
 *
 * @package		Pancake
 * @author		Pancake Dev Team
 * @copyright	Copyright (c) 2010, Pancake Payments
 * @license		http://pancakeapp.com/license
 * @link		http://pancakeapp.com
 * @since		Version 1.0
 */

// ------------------------------------------------------------------------

$lang = array(

	/** Global Words **/
 	'global:error' => 'Error',
	'global:pancakeby7am' => 'Pancake :1 by :2',
	'global:allrelatedmediacopyright' => 'Pancake and all related media is Copyright :1 :2',
	'global:insecure_action' => 'Insecure action was attempted but caught',
	'global:estimates' => 'Estimates',
	'global:estimate' => 'Estimate',
	'global:projects' => 'Projects',
	'global:clients' => 'Clients',
	'global:users' => 'Users',
	'global:send_to_client'	=>	'Send to client',
	'global:couldnotsendemail' => 'Could not send the email.  Please check your settings.',
	'global:emailsent' => 'Email has been sent!',
	'global:yes' => 'Yes',
	'global:no' => 'No',
	'global:dontshowagain' => "Don't show this again",
	'global:enable' => 'Enable',
	'global:is_enabled' => 'Enabled?',
	'global:is_completed' => 'Completed?',
	'global:Y' => 'Y',
	'global:N' => 'N',
	'global:notes' => 'Notes',
	'global:description' => 'Description',
	'global:backtoadmin' => 'Back to Admin',
	'global:type' => 'Type',
	'global:name'		=>	'Name',
	'global:first_name'		=>	'First Name',
	'global:last_name'		=>	'Last Name',
	'global:company'	=>	'Company',
	'global:phone'		=>	'Phone',
	'global:email'		=>	'Email',
	'global:contacts'	=>	'Contacts',
	'global:fax'		=>	'Fax',
	'global:mobile'		=>	'Mobile',
	'global:address'	=>	'Address',
	'global:api_key' => 'API Key',
	'global:website'	=>	'Website',
	'global:action'		=>	'Action',
	'global:items'		=>	'Items',	
	'global:createinvoice' 	=> 'Create Invoice',
	'global:paid' 			=> 'Paid',
	'global:unpaid'			=> 'Unpaid',
	'global:overdue'		=> 'Overdue',
	'global:invoices'		=> 'Invoices',
	'global:invoice'		=>	'Invoice',
	'global:estimate' 		=> 'Estimate',
	'global:dashboard'		=> 'Dashboard',
	'global:settings'		=> 'Settings',
	'global:changepassword' => 'Change Password',
	'global:logout' 		=> 'Log Out',
	'global:status' => 'Status',
	'global:client' => 'Client',
	'global:title' => 'Title',
	'global:subtitle' => 'Subtitle',
	'global:to' => 'To',
	'global:subject' => 'Subject',
	'global:thanks' => 'Thanks!',
	'global:proposals' => 'Proposals',
	'global:proposal' => 'Proposal',
	'global:reports' => 'Reports',
	'global:items' => 'Items',
	'global:item' => 'Item',
	'global:report' => 'Report',
	'global:saveandinsert' => 'Save and Insert',
	'global:details'	=>	'Details',
	'global:license_key' => 'License Key',
	'global:version' => 'Version',
	'global:content' => 'Content',
	'global:edit'	=>	'Edit',
	'global:resend'	=>	'Resend',
	'global:view'	=>	'View',
	'global:delete'	=>	'Delete',
	'global:remove'	=>	'Remove',
	'global:sort'	=>	'Sort',
	'global:deleteitem' => 'Delete Item',
	'global:dragtoreorder' => 'Drag To Reorder',
	'global:start' => 'Start',
	'global:stop' => 'Stop',
	'global:created'	=>	'Created',
	'global:updated'	=>	'Updated',
	'global:update'	=>	'Update',
	'global:downloadpdf' => 'Download PDF',
	'global:yesdelete' => 'Yes, delete!',
	'global:confirm_emphisised'	=>	'There is no going back!',
	
	'global:optional_increment' => '<strong>Optional</strong> - will auto increment',

	'login:ahoy' => 'Ahoy!',
	'login:readytodothis' => 'Ready to do this?',
	'login:sup' => 'Sup?',
	'global:actions' => 'Actions',


	/** Greetings **/
	'global:greetings'	=>	array('Ahoi!', 'Hello,', 'Hey,', 'Sup,'),
	/** End Greetings **/

	/** Dashboard **/	
	'dashboard:collected'		=>	'Collected',
	'dashboard:outstanding'		=>	'Outstanding',
	'dashboard:latest_activity'	=>	'Latest Activity',
	'dashboard:upcoming_tasks' 	=> 	'Upcoming Tasks',
	/** End Dashboard **/


	/** Users **/

	// ==============================
	// = LOGIN PAGE COMPLETED - Lee =
	// ==============================
	'login:forgotinstructions'	=>	'Please enter your email address so we can send you an email to reset your password.',
	'login:reset'				=>	'Reset &raquo;',
	'login:reset'				=>	'Reset Password',
	'login:username'			=>	'Username',
	'login:password'			=>	'Password',
	'login:changepassword'		=>	'Change Password',
	'login:email'				=>	'Email Address',
	'login:login'				=>	'Login',
	'login:logout'				=>	'Logout',
	'login:remember'			=>	'Remember Me',
	'login:forgot'				=>	'Forgot your password?',
	'login:cancel'				=>	'&laquo; Cancel',
	/** End Users **/

	/** Clients **/
	'clients:title'		=>	'All Clients',
	'clients:filter'	=>	'Client Filter',
	'clients:add'		=>	'Add Client',
	'clients:edit'		=>	'Edit Client',
	'clients:noclienttitle' => 'You need to enter some clients!',
	'clients:noclientbody'	=> 	'So you can send them some invoices. Add one now?',	
	'clients:hasnoinvoicetitle' =>	'Sweet! This client is all set up!',
	'clients:hasnoinvoicebody'	=> 	'Let\'s create an invoice for them and get billing! Add one now?',
	'clients:added' => 'The client has been added!',
	'clients:edited' => 'The client has been updated!',
	'clients:deleted' => 'The client has been deleted!',
	'clients:does_not_exist' => 'That client does not exist!',
	'clients:health_check'	=> 	'Health Check',	
	'clients:delete_title'	=>	'Delete this client?!?!',
	'clients:delete_message'	=>	'Are you sure you want to delete this client?<br />This will also delete all invoices, projects and proposals for this client.',
	'clients:clientaction'		=>	'&nbsp;',
	/** End Clients **/

	/** Invoices **/
	'invoices:all'		=>	'All Invoices',
	'invoices:empty'	=>	'No invoices yet.',
	'invoices:overdue'	=>	'Overdue Invoices',
	'invoices:paid'		=>	'Paid Invoices',
	'invoices:unpaid'	=>	'Unpaid Invoices',
	'invoices:thisinvoicewaspaidon' => 'This invoice was paid on :1. Thank you!',
	'invoices:paidon' => 'Paid on :1',
	'invoices:thisinvoiceisunpaid' => 'This invoice isn\'t paid, yet.',
	'invoices:noinvoicetitle'	=>	'You have no invoices yet!',
	'invoices:noinvoicebody'	=>	'You should create an invoice now for a Client! <br /> Clients love invoices!',
	'invoices:send_now_title' => 'Send invoice now?',
	'invoices:send_now_body' => 'Fill out the form below and we\'ll deliver this invoice for you.',
	'invoices:newinvoice'	=>	'Create Invoice',
	'invoices:send_now' => 'Send invoice',
	'invoices:timequantity' => 'Time/Quantity',
	'invoices:ratewithcurrency' => 'Rate (:1)',
	'invoices:taxable' 	=> 'Taxable',
	'invoices:total' 	=> 'Total',
	'invoices:amount'	=>	'Amount',
	'invoices:due'		=>	'Due',
	'invoices:subtotal' => 'Subtotal',
	'invoices:totaltax' => 'Total Tax',
	'invoices:filestodownload' => 'Files for Download',
	'invoices:fileswillbeavailableafterpay' => 'These files will be available for download once the invoice has been fully paid.',
	'invoices:no_payment_gateways_enabled' => 'No payment gateways have been enabled, which might make it tricky to be paid. Enable in <a href=":1">Settings</a>',
	'invoices:invoicenumber' => 'Invoice #:1',
	'invoices:number' => 'Invoice #',
	'invoices:type'	=>	'Invoice Type',
	'invoices:is_recurring' => 'Recurring?',
	'invoices:delete_title'	=>	'Delete this invoice?',
	'invoices:delete_message'	=>	'Are you sure you want to delete this invoice?',
	'invoices:create' => 'Create Invoice',
	'invoices:edit' => 'Edit Invoice',
	'invoices:amountrequired' => 'The Amount field is required',
	'invoices:currencydoesnotexist' => 'This currency does not exist.',
	'invoices:unique_id' => 'Unique ID',
	'invoices:willreoccurin' => 'This invoice will reoccur on :1.',
	'invoices:willbesentautomatically' => 'The client will be notified by email about this invoice on :1.',
	'invoices:thisisareoccurrence' => 'This invoice is a reoccurrence of Invoice :1.',
	'invoices:clientlastnotifiedon' => 'The client was notified by email about this invoice on :1.',
	'invoices:simple' => 'Simple',
	'invoices:simple_help' => 'A simple invoice has no line items. Simply a total.',
	'invoices:detailed' => 'Detailed',
	'invoices:detailed_help' => 'Detailed invoices allow you to have multiple line items.',
	'invoices:estimate_help' => 'Estimates are detailed invoices that are not billable.',
	'invoices:unpaid_totalamount'	=>	'Unpaid / Total amount',
	'invoices:saveinvoice' => 'Save Invoice',
	'invoices:deleted' => 'The invoice has been deleted!',
	/** End Invoices **/

	/** Estimates **/
	'estimates:alltitle'	=>	'All Estimates',
	'estimates:attachingtoproposal' =>     'Attaching estimate to proposal, please wait...',
	'estimates:delete_title'	=>	'Delete this estimate?',
	'estimates:delete_message'	=>	'Are you sure you want to delete this estimate?',
	'estimates:createnew'	=>	'Create new Estimate',
	'estimates:noestimatetitle'	=>	'You have no estimates',
	'estimates:deleted' => 'The estimate has been deleted!',
	'estimates:noestimatebody'	=>	'You should get on that! Would you like to create one now?',
	/** End Estimates **/

	/** Projects **/
	'projects:navigationitem' => 'Projects',
	'projects:alltitle' =>	'All Projects',
	'projects:noprojecttitle' => 'There are no projects yet!',
	'projects:noprojecttext' => 'Would you like to add one now? ',
	'projects:add'	=> 'Create Project',
	'projects:edit'	=> 'Edit Project',
	'projects:delete'	=> 'Delete Project',	
	'projects:project' => 'Project',
	'projects:due_date' => 'Due Date',
	'projects:areyousuredeletetask' => 'Are you sure you want to delete this task?',
	'projects:add_time' => 'Add Time',
	'projects:generate_invoice' => 'Generate Invoice',
	/** End Projects **/


	/** Proposals **/
	'proposal:outline'	=>	'Proposal Outline',


	/** End Proposals **/

	/** Reports **/
	'reports:perclient' => 'per client',
	'reports:datefrom' => 'From',
	'reports:allclients' => 'All clients',
	'reports:dateto' => 'To',
	'reports:byclient' => 'Client',

	'reports:view' => 'View Report',
	'reports:show_all' => 'Show Reports',
	'reports:nodata' => 'No :1.',
	/** End Reports **/

	/** Currencies **/
	'currencies:default' => '[Default] :1',
	'currencies:cad' => 'Canadian Dollar',
	'currencies:eur' => 'Euro',
	'currencies:usd' => 'U.S. Dollar',
	'currencies:gbp' => 'Pound Sterling',
	'currencies:hkd' => 'Hong Kong Dollar',
	'currencies:php' => 'Philippine Peso',
	'currencies:zar' => 'South Africa, Rand',
	/** End Currencies **/

	/** Proposals **/
	'proposals:newproposal' => 'New Proposal',
	'proposals:estimatexfory' => 'Estimate #:1 - :2',
	'proposals:number' => 'Proposal #',
	'proposals:all' => 'All Proposals',
	'proposals:noproposaltitle' => "There are no proposals!",
	'proposals:noproposalbody' => "You should create a proposal now. Definitely.",
	'proposals:rejected' => 'Rejected on :1',
	'proposals:accepted' => 'Accepted on :1',
	'proposals:lastviewed' => 'Last viewed by the client on :1, at :2',
	'proposals:neverviewed' => 'not viewed by the client',
	'proposals:noanswer' => 'No answer',
	'proposals:createproposal' => 'Create Proposal',
	'proposals:editproposal' => 'Edit Proposal',
	'proposals:createdsuccessfully' => 'Proposal created!',
	'proposals:sections' => 'Sections',
	'proposals:section' => 'Section',
	'proposals:createsection' => 'Add New Section',
	'proposals:createpage' => 'Add Page',
	'proposals:emptysection' => '(no title)',
	'proposals:emptycontents' => '(no contents)',
	'proposals:emptysubtitle' => '(no subtitle)',
	'proposals:for' => 'Proposal for:',
	'proposals:pagexofcount' => 'Page :1 of :2',
	'proposals:saving' => 'Saving...',
	'proposals:save' => 'Save Proposal',
	'proposals:savepremade' => 'Save as Section Template',
	'proposals:addestimate' => 'Add Estimate',
	'proposals:saved' => 'Saved!',
	'proposals:createandedit' => 'Next: Edit proposal contents',
	'proposals:delete_message' => 'Are you sure you want to delete this proposal?',
	'proposals:createpremadesection' => 'Add from Section Template',
	'proposals:selected_attachments' => 'Select Estimate',
	'proposals:attach_selected_estimate' => 'Attach Selected Estimate',
	/** End Proposals **/

	/** Tasks **/
	'tasks:task' => 'Task',
	'tasks:timer' => 'Timer',
	'tasks:hours' => 'Hours',
	'tasks:rate' => 'Rate',
	'tasks:default_rate' => 'Default Rate',
	'tasks:view_entries' => 'View Entries',
	'tasks:create' => "Create Task",
	'tasks:create_succeeded' => "The task has been created!",
	'tasks:no_task_title' => 'Hmm, no tasks yet...',
	'tasks:no_task_message' => 'You should create some!',
	
	/** Items **/
	'items:name' => 'Item Name',
	'items:description' => 'Item Description',
	'items:qty_hrs' => 'Qty / Hrs',
	'items:quantity' => 'Quantity',
	'items:rate' => 'Rate',
	'items:tax_rate' => 'Tax Rate',
	'items:cost' => 'Cost',
	'items:line_items' => 'Line Items',
	'items:add' => 'Add Item',
	'items:edit' => 'Edit Item',
	'items:noitemtitle'	=>	'You have no items yet!',
	'items:noitembody'	=>	'You should add some items now, it makes creating invoices loads easier!',
	'items:delete_title'	=>	'Delete this item?',
	'items:delete_message'	=>	'Are you sure you want to delete item ":1"?',

	/** Transactions **/
	'transactions:paymentcancelled' => 'Payment Cancelled',
	'transactions:extrapaymentcancelled' => 'Your payment has been cancelled.',
	'transactions:paymentreceived' => 'Payment Received!',
	'transactions:orderbeingprocessed' => 'Please wait, your order is being processed and you will be redirected to the :1 website.',
	'transactions:ifyouarenotredirected' => 'If you are not automatically redirected to :1 within 5 seconds...',
	'transactions:thankyouforyourpayment' => 'Thank you for your payment. You should be receiving a receipt via email shortly.',
	'transactions:ifyouhavefilesyouwillgetanemail' => 'If you have files awaiting delivery you will receive an email with a link to download them shortly.',
	'transactions:ifyoudonotreceiveemail' => 'If you do not receive an email within an hour please contact :1',
	/** End Transactions **/

	/** Timesheets **/
	'timesheet:taskname' => 'Task Name',
	'timesheet:starttime' => 'Start Time',
	'timesheet:endtime' => 'End Time',
	'timesheet:timeframe' => 'Timeframe',
	'timesheet:duration' => 'Duration',
	'timesheet:date' => 'Date',
	'timesheet:forproject' => 'Timesheet for Project ":1"',
	'timesheet:timesheet' => 'Timesheet',
	'timesheet:for' => 'Timesheet for:',
	'timesheet:totalbillable' => 'Total Billable Hours',
	'timesheet:view_pdf' => 'View Timesheet (PDF)',
	/** End Timesheets **/

	/** Frontend **/
	'frontend:hithere' => 'Hi There!',
	'frontend:followthemaillinkdude' => 'In order to view your invoice you must click the entire link sent in the email you received. Eg :1.',
	'frontend:contactadminforassistance' => 'Please do so or contact :1 @ :2 for assistance',

	/** End Frontend **/



	/** Settings **/
	'settings:removelogo' => 'Remove Logo',
	'settings:wrong_license_key' => 'The license key you have entered is not valid.',
	'settings:noopenssl' => 'Your PHP server does not have OpenSSL configured, which means you can\'t use Gmail or Google Apps for sending email. Please contact your host and let them know you need OpenSSL.',
	'settings:logoremoved' => 'Logo removed successfully!',
	'settings:save' => 'Save Settings',
	'settings:logodimensions' => 'The logo should be 240 pixels wide and 106 pixels tall.',
	'settings:logoformatsallowed' => 'BMP, PNG, JPG (JPEG) and GIF are allowed.',
	'settings:ftp_user' => 'FTP User',
	'settings:ftp_pass' => 'FTP Password',
	'settings:ftp_path' => 'FTP Path',
	'settings:ftp_port' => 'FTP Port',
	'settings:ftp_pasv' => 'Passive Mode?',
	'settings:nophpupdates' => "Because of the way your server is configured, you need to enter your FTP details so that Pancake can update itself. These details are used internally by Pancake and are never transmitted to anyone.",
	'settings:ftp_host' => 'FTP Host',
	'settings:uptodate' => 'Pancake is up to date (:1)',
	'settings:newversionavailable' => 'There is a new version of Pancake available (:1)!',
	'settings:updatenow' => 'Update now!',
	'settings:youneedtoconfigurefirst' => 'Your Pancake is not yet configured to update itself. Please enter your FTP details below, then press "Save Settings".<br /> Pancake will then let you update.',
	'settings:general' => 'General',
	'settings:email_templates' => 'Email Templates',
	'settings:taxes' => 'Taxes',
	'settings:currencies' => 'Currencies',
	'settings:branding' => 'Branding',
	'settings:payment_methods' => 'Payment Methods',
	'settings:feeds' => 'Feeds',
	'settings:api_keys' => 'API Keys',
	
	'settings:site_name' => 'Site name',
	'settings:language' => 'Language',
	'settings:timezone' => 'Timezone',
	'settings:notify_email' => 'Notify email',
	'settings:currency' => 'Currency',
	'settings:theme' => 'Frontend Theme',
	'settings:admin_theme' => 'Admin Theme',
	'settings:admin_name' => 'Admin name',
	'settings:date_format' => 'Date format',
	'settings:task_time_interval' => 'Task Time Interval',
	'settings:mailing_address' => 'Mailing Address',
	
	'settings:new_invoice' => 'New Invoice',
	'settings:new_proposal' => 'New Proposal',
	'settings:paid_notification' => 'Paid Notification',
	'settings:payment_receipt' => 'Payment Receipt',
	
	'settings:logo' => 'Your logo',
	'settings:frontend_css' => 'Frontend Custom CSS',
	'settings:backend_css' => 'Backend Custom CSS',
	
	'settings:rss_password' => 'RSS Password',
	'settings:default_feeds' => 'Default Feeds',
	'settings:cron_job_feed' => 'Cron Job',
	'settings:feed_generator' => 'Feed Generator',
	'settings:your_link' => 'Your Link',
	'settings:bcc' => 'BCC',
	'settings:automaticallybccclientemail' => 'Automatically send a copy of all client emails to the notify email (defined above)',
	'settings:api_note' => 'Name / Note',
	'settings:api_key' => 'Key',
	
	'settings:tax_name' => 'Tax Name',
	'settings:tax_value' => 'Value',
	'settings:tax_reg' => 'Registration / Code',
	'settings:add_tax' => 'Add Another Tax',
	
	'settings:currency_name' => 'Currency Name',
	'settings:currency_code' => 'Currency Code',
	'settings:exchange_rate' => 'Exchange Rate',
	'settings:add_currency' => 'Add Another Currency',
	/** End Settings **/	

	'update:ifyourenotsurecontactus' => "If you're not sure what to do, please <a href='http://pancakeapp.com/forums/newtopic/2/'>start a new tech support topic in the forums</a>.",
	'update:youmodified' => 'You modified',
	'update:youdeleted' => 'You deleted',
	'update:whatschanged' => "What's new in :1",
	'update:ftp_conn' => 'Pancake could not connect to the FTP host.',
	'update:ftp_login' => 'Pancake could not login via FTP (wrong FTP username/password?).',
	'update:ftp_chdir' => 'Pancake could not set the FTP path (path probably does not exist).',
	'update:ftp_no_uploads' => 'Pancake could not obtain permission to upload files via FTP.',
	'update:ftp_indexwrong' => 'The FTP Path you entered is incorrect. It should be the path to Pancake\'s directory.',
	'update:ftp_indexnotfound' => 'The FTP Path you entered is incorrect. It should be the path to Pancake\'s directory.',
	'update:update_conflict' => 'You modified some files since the last update. In order to safeguard your customizations, here is a list of files that you have modified, that conflict with the latest upgrade.',
	'update:update_no_perms' => 'Pancake does not have enough permissions to update itself, nor does it have access to an FTP account from which it can update itself. Update cannot continue.',
	'update:review_files' => 'Please review these files and make backups of them before proceeding. When the upgrade is finished, you will have to re-integrate your modifications back into them. Please do not just replace the updated files with your outdated modified copies, as that may break Pancake.',
	'update:internetissues' => "Pancake is unable to connect to the Internet.",
	'update:pancakeneedsinternet' => 'For Pancake to function correctly, your server must allow it to fetch some information from the Internet.',
	'update:maybefirewall' => "It appears that your server is blocking Pancake from accessing the Internet. This could be a firewall issue in your server. Please contact your host for help. Ask them to allow PHP to access manage.pancakeapp.com.",
	'update:nointernetaccess' => 'No Internet Access',
	'update:pancakeupdated' => 'Pancake was upgraded from :1 to :2',
	/** Action Logger  **/

	/** End Action Logger **/

	/** Partial Payments **/
	'partial:partialpayments' => 'Payment Plan', 
	'partial:amount'          => 'Amount',
	'partial:dueon'           => 'Due on',
	'partial:addanother'      => 'Add another part to this payment',
	'partial:disabledforrecurring' => 'Recurring invoices are limited to one part payments',
	'partial:paymentdetails' => 'Payment Details',
	'partial:wrongtotal' => 'The sum of all the parts of your payment plan does not match the total amount you are invoicing.',
	'partial:problemsaving' => 'A problem occurred while saving the payment plan. Please try again.',
	'partial:wrongtotalbutsaved' => 'The sum of all the parts of your payment plan does not match the total amount you are invoicing.<br />The changes to your invoice were saved, but you need to fix your payment plan.',
	'partial:problemsavingbutsaved' => 'A problem occurred while saving the payment plan. Please try again.<br />The changes to your invoice were saved, it is only the payment plan that wasn\'t.',
	'partial:savepaymentdetails' => 'Save payment details',
	'partial:partpaidthanks' => "This part of your invoice's payment has been paid. Thank You.",
	'partial:proceedtopayment' => 'Proceed to payment',
	'partial:topaynowgoto' => 'To pay now, please go to :1',
	'partial:dueondate' => 'due on :1',
	'partial:paymentmethod' => 'Payment Method',
	'partial:paymentdate' => 'Payment Date',
	'partial:paymentstatus' => 'Payment Status',
	'partial:transactionid' => 'Transaction ID',
	'partial:markaspaid' => 'Mark as Paid',
	'partial:transactionfee' => 'Transaction Fee',
	/** End Partial Payments **/

	/** Payment Gateways **/
	'paypal:clickhere' => 'Click Here',
	'authorize:transaction_key' => 'Transaction Key',
	'paypal:email'     => 'PayPal Email',
	'cash:cash' => 'Cash',
	'gateways:errorupdating' => 'There was an error updating your payment method settings.  Please contact support.',
	'gateways:paymentmethods' => 'Payment Methods',
	'gateways:selectpaymentmethod' => 'Select Payment Method',
	'gateways:nogatewayused' => 'No method used: Part is unpaid',
	'gateways:completed' => 'Completed',
	'gateways:refunded' => 'Refunded',
	'gateways:unpaid' => 'Unpaid',
	'gateways:pending' => 'Pending',
	'gateways:returntowebsite' => 'Return to :1',
	/** End Payment Gateways **/

);

/** End of file: pancake_lang.php **/