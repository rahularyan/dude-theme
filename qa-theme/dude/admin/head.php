<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Admin head */
?>
<!DOCTYPE html>
<html lang="<?php echo qa_opt('site_language'); ?>">
	<head>	
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php $context->head(); ?>
		
		<link rel="stylesheet" type="text/css" href="<?php echo DUDE_THEME_URL; ?>/font/style.css">
		<link rel="stylesheet" type="text/css" href="<?php echo DUDE_THEME_URL; ?>/css/dlmenu.css">
		<link rel="stylesheet" type="text/css" href="<?php echo DUDE_THEME_URL; ?>/css/colorpicker.css">
		<?php			
			less_css('bootstrap');
			less_css('dude');
			less_css('dude_admin');
			$context->ra_builder_css();
		?>		
		<link rel="shortcut icon" href="<?php echo DUDE_THEME_URL; ?>/images/ico/favicon.png">
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="<?php echo DUDE_THEME_URL; ?>/js/respond.min.js"></script>
		<![endif]-->
		<script src="<?php echo DUDE_THEME_URL; ?>/js/modernizr.custom.js"></script>	
	</head>
	
	<body <?php echo $context->body_tags(); ?>>
	
<!-- /dl-menuwrapper -->
	<!-- Navbar -->
    <div class="navbar navbar-default<?php echo qa_opt('ra_nav_fixed') ? ' navbar-fixed-top' : ''; ?>">

			<div class="navbar-header">			
				<?php echo ra_logo(); ?>
			</div>
			<?php $context->ra_user_box(); ?>
	

    </div>
	<?php 
		if(ra_edit_mode() && ra_is_admin()){
			include( DUDE_THEME_DIR.'/inc/builder_elm.php');
		}
	?>

	<section id="main" <?php echo qa_opt('ra_nav_fixed') ? ' class="has-fixed-top"' : ''; ?>>
		<?php if (isset($context->content['error'])){ ?>
			<div id="ra-alert" class="alert fade in">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>	
				<?php echo @$context->content['error']; ?>
			</div>
		<?php } ?>
		
		
		<?php $context->widgets('full', 'top'); // widget top ?>

	<?php $context->widgets('full', 'high'); // widget position after header ?>