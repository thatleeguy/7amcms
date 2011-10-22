<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $template['title']; ?></title>
	<link rel="shortcut icon" href="<?php echo Asset::get_src('favicon.ico', 'img');?>" />
	<?php asset::css('uniform.aristo.css', array('media' => 'screen'), 'main-css'); ?>
	<?php asset::css('facebox.css', array('media' => 'screen'), 'main-css'); ?>
	<?php asset::css('stacks.css', array('media' => 'all'), 'main-css'); ?>
	<?php asset::css('pancake-ui/jquery-ui-1.8.15.custom.css', array('media' => 'screen'), 'main-css'); ?>
	<?php echo asset::render('main-css'); ?>
	<link href='http://fonts.googleapis.com/css?family=Paytone+One&amp;v1' rel='stylesheet' type='text/css' />
	<!--[if lt IE 7]><?php echo asset::css('lt7.css'); ?> <![endif]-->
	<!--[if !IE 7]><style type="text/css">#wrapper {display:table;height:100%}</style><![endif]-->
	<?php if (Settings::get('backend_css')): ?><style type="text/css"><?php echo Settings::get('backend_css'); ?></style><?php endif; ?>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
	<script>
	    window.jQuery || document.write('<script src="<?php echo asset::get_src('jquery-1.6.2.min.js', 'js');?>">\x3C/script>');
	    refreshTrackedHoursUrl = '<?php echo site_url('ajax/refresh_tracked_hours/');?>';
	    faceboxURL = '<?php echo str_replace('facebox.js', '', asset::get_src('facebox/facebox.js', 'js'));?>';
	    baseURL = '<?php echo substr((substr(site_url('ajax'), -1) == '/') ? site_url('ajax') : site_url('ajax').'/', 0, -5);?>';
	    siteURL = '<?php echo rtrim(site_url(), '/');?>/';
	    datePickerFormat = "<?php echo get_date_picker_format();?>";
	    storeTimeUrl = '<?php echo site_url('ajax/store_time');?>';
	</script>
	<?php asset::js('main.js', array(), 'main-js'); ?>
	<?php asset::js('jquery-ui-1.8.15.custom.min.js', array(), 'main-js'); ?>
	<?php asset::js('plugins.js', array(), 'main-js'); ?>
	
	<?php echo asset::render('main-js'); ?>
</head>
    <body class="<?php echo (isset($iframe)) ? ($iframe ? 'iframe' : '') : '';?>">
<div id="wrapper">
	<div class="w1">
		<div class="w2">
			<div id="header">
				<div class="header-cell">
					<ul class="user-nav">
					    <?php if (defined('TEMPORARY_NO_INTERNET_ACCESS')) :?>
						<li><?php echo __('update:internetissues');?></li>
					    <?php else: ?>
						<?php if (Settings::get('latest_version') != Settings::get('version')) :?>
						    <li><?php echo anchor('admin/settings#update', __('settings:newversionavailable', array(Settings::get('latest_version')))); ?></li>
						<?php endif;?>
					    <?php endif;?>
						<li><?php echo anchor('admin/settings', __('global:settings')); ?></li>
					    <?php if (!PANCAKE_DEMO) :?>
						<li><?php echo anchor('admin/users/change_password', __('global:changepassword')); ?></li>
					    <?php endif; ?>
						<li><?php echo anchor('admin/users/logout', __('global:logout')); ?></li>
					</ul><!-- /user-nav end -->
				</div><!-- /header-cell end -->
				<div class="header-area">
					<?php echo logo();?>
				</div><!-- /header-area end -->
				<div class="header-box">
					<strong><?php echo anchor('admin', __('global:dashboard')); ?></strong>
					<ul id="nav">
						<li class="<?php echo ($module == 'invoices') ? 'active ' : ''; ?>subnav">
							<?php echo anchor('admin/invoices/all', __('global:invoices')); ?>
							<ul class="submenu">
							    <li><?php echo anchor('admin/invoices/create', __('global:createinvoice')); ?></li>
							    <li><?php echo anchor('admin/invoices/paid', __('global:paid')); ?></li>
							    <li><?php echo anchor('admin/invoices/unpaid', __('global:unpaid')); ?></li>
							    <li><?php echo anchor('admin/invoices/overdue', __('global:overdue')); ?></li>
								<li><?php echo anchor('admin/items', __('global:items')); ?></li>
							  </ul>
						</li>
						<li<?php echo ($module == 'estimates') ? ' class="active"' : ''; ?>><?php echo anchor('admin/invoices/estimates', __('global:estimates')); ?></li>
						<li<?php echo ($module == 'projects') ? ' class="active"' : ''; ?>><?php echo anchor('admin/projects', __('global:projects')); ?></li>
                        <li<?php echo ($module == 'proposals') ? ' class="active"' : ''; ?>><?php echo anchor('admin/proposals', __('global:proposals')); ?></li>
						<li<?php echo ($module == 'reports') ? ' class="active"' : ''; ?>><?php echo anchor('admin/reports', __('global:reports')); ?></li>
						<li<?php echo ($module == 'clients') ? ' class="active"' : ''; ?>><?php echo anchor('admin/clients', __('global:clients')); ?></li>
						<li<?php echo ($module == 'users') ? ' class="active"' : ''; ?>><?php echo anchor('admin/users', __('global:users')); ?></li>
					</ul><!-- /nav end -->
				</div><!-- /header-box end -->
			</div><!-- /header end -->
			<div id="main">
				<?php echo $template['partials']['notifications']; ?>
				<?php echo $template['body']; ?>
			</div><!-- /main end -->
		</div>
	</div>
</div><!-- /wrapper end -->
<div id="footer">
	<div class="w1">
		<div class="w2">
			<div class="footer-cell">
				<strong class="f-logo"><a href="http://pancakeapp.com/">Pancake</a></strong>
				<div class="holder">
					<div class="row">
						<strong><?php echo __('global:pancakeby7am', array(Settings::get('version'), '<a href="http://7am.ca/">7am</a>'))?></strong>
					</div>
					<div class="row">
						<strong><?php echo __('global:allrelatedmediacopyright', array(COPYRIGHT_YEAR, '<a href="http://7am.ca/">7am</a>')); ?></strong>
					</div>
				</div>
			</div><!-- /footer-cell end -->
		</div>
	</div>
</div><!-- /footer end -->
<?php print_update_notification();?>
<?php if (PANCAKE_DEMO) :?>
    <?php echo file_get_contents(FCPATH.'DEMO');?>
<?php endif;?>
</body>
</html>