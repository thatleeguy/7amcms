<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $template['title']; ?></title>
	<?php echo Asset::css('login.css', array('media' => 'all')); ?>
	<!--[if lt IE 7]><?php echo asset::css('lt7.css'); ?> <![endif]-->
	<!--[if !IE 7]><style type="text/css">#wrapper {display:table;height:100%}</style><![endif]-->
	<?php if (Settings::get('backend_css')): ?><style type="text/css"><?php echo Settings::get('backend_css'); ?></style><?php endif; ?>
</head>
<body>
<div id="wrapper">
	<?php if (!isset($hide_header)) :?>
	<div class="header-area">
		<?php echo logo();?>
		<h3><?php echo __(random_element(array(__('login:ahoy'), ':D', __('login:readytodothis'), __('login:sup'))))?></h3>
	</div><!-- /header-area end -->
	<?php endif;?>
	<div id="main">
		<?php echo $template['partials']['notifications']; ?>
		<?php echo $template['body']; ?>
	</div><!-- /main end -->
</div><!-- /wrapper end -->
<?php if (PANCAKE_DEMO) :?>
    <?php echo file_get_contents(FCPATH.'DEMO');?>
<?php endif;?>
</body>
</html>