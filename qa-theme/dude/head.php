<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Head of the template */
	$current_cat = ra_get_cat_desc(qa_request(2));
?>
<!DOCTYPE html>
<html lang="<?php echo qa_opt('site_language'); ?>">
	<head>	
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php if(is_array($current_cat)): ?>
			<meta name="description" content="<?php echo substr($current_cat['content'], 0, 25); ?>">
			<meta name="keywords" content="<?php echo $current_cat['title']; ?>">
		<?php endif; ?>
		<?php 
			$context->head(); 
			//custom head meta			
			echo qa_opt('custom_head_meta');
			
			less_css('bootstrap');
			less_css('dude');
			$context->ra_builder_css();
			
			$css 	= qw_get_all_styles($context->template);

				if (!empty($css))
				foreach ($css as $css_src){
					if(isset($css_src['file']) && filter_var($css_src['file'], FILTER_VALIDATE_URL) !== FALSE)
						echo '<link rel="stylesheet" type="text/css" href="'.$css_src['file'].'"/>';
				}
					
				if (!empty($context->content['notices']))
					echo
						'<style><!--',
						'.qa-body-js-on .qa-notice {display:none;}',
						'//--></style>'
					;
			
			
			//register a hook
			if(qw_hook_exist('head_script'))
				echo qw_do_action('head_script', $context);
				
			$scripts = qw_get_all_scripts($context->template);

			echo '<script> ajax_url = "' . QW_CONTROL_URL . '/ajax.php"; theme_url = "' . Q_THEME_URL . '"; site_url = "' . QW_BASE_URL . '";</script>';

				if (!empty($scripts))
					foreach ($scripts as $script_src){
						if(filter_var($script_src['file'], FILTER_VALIDATE_URL) !== FALSE)
							echo '<script type="text/javascript" src="'.$script_src['file'].'"></script>';
					}
			
			
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo DUDE_THEME_URL; ?>/font/style.css">
		<link rel="stylesheet" type="text/css" href="<?php echo DUDE_THEME_URL; ?>/css/dlmenu.css">		
		<link rel="shortcut icon" href="<?php echo DUDE_THEME_URL; ?>/images/ico/favicon.png">
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="<?php echo DUDE_THEME_URL; ?>/js/respond.min.js"></script>
		<![endif]-->
		<script src="<?php echo DUDE_THEME_URL; ?>/js/modernizr.custom.js"></script>
		<script>
			var ra_nav_fixed = <?php echo qa_opt('ra_nav_fixed') ? 'true' : 'false'; ?>;
				
		</script>
	</head>	
	<body <?php echo $context->body_tags(); ?>>
	<!-- Navbar -->
    <div class="navbar navbar-default<?php echo qa_opt('ra_nav_fixed') ? ' navbar-fixed-top' : ''; ?>">
        <div class="container">
			<div class="navbar-header">	
				<div id="dl-menu" class="dl-menuwrapper">
					<button class="dl-menu-btn">Open Menu</button>
					<?php echo ra_nav($context->content, 'dl-menu', false, true); ?>
				</div>			
				<?php echo ra_logo(); ?>
			</div>
			<?php $context->ra_user_box(); ?>
			<a class="ask-btn btn btn-danger btn-sm" href="<?php echo $context->content['navigation']['main']['ask']['url'];?>"><?php ra_lang('Ask Questions'); ?></a>			
			
			<?php echo ra_nav($context->content, 'main-menu nav navbar-nav', true); ?>			
			
      </div>
    </div>
	

	<?php 
		if(ra_edit_mode() && ra_is_admin()){
			include( DUDE_THEME_DIR.'/inc/builder_elm.php');
		}
	?>

	<section id="main" <?php echo qa_opt('ra_nav_fixed') ? ' class="has-fixed-top"' : ''; ?>>
		<?php 
			global $ra_error;
			if ((isset($context->content['error']) && strlen($context->content['error']) > 0) || isset($ra_error)){ ?>
			<div id="ra-alert" class="alert fade in">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>	
				<?php echo @$context->content['error']; ?>
				<?php echo @$ra_error; ?>
			</div>
		<?php } ?>
		
		
		<?php $context->widgets('full', 'top'); // widget top ?>
	

	
		<header id="mastheader">
			<div class="page-header">
				<div class="container">
						<div class="page-title pull-left">
							<h1><?php $context->title(); ?></h1>
						</div>
					
					<?php 						
						$context->search(); 
						if($context->template != 'question')$context->favorite(); 
						echo $context->do_shortcode('[widget name="RA Social"]');						
					?>
				</div>	
			</div>
		</header>
		
		<?php /* if (isset($context->content['error'])){ ?>
			<div id="error-message">	
				<div class="container">
					<i class="icon-warning"></i>
					<?php echo @$context->content['error']; ?>
				</div>
			</div>
		<?php }  */?>
	<?php $context->widgets('full', 'high'); // widget position after header ?>