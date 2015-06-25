<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	$ra_error ='';

	define('DUDE_THEME_DIR', dirname( __FILE__ ));
	define('DUDE_THEME_URL', get_base_url().'/qa-theme/'.qa_get_site_theme());

	
	require DUDE_THEME_DIR.'/language/default.php';		
	require DUDE_THEME_DIR.'/inc/core_functions.php';		
	require DUDE_THEME_DIR.'/inc/lessc.inc.php';
	require DUDE_THEME_DIR.'/inc/less.php';
	require DUDE_THEME_DIR.'/inc/widgets.php';	
	
	require DUDE_THEME_DIR.'/inc/builder.php';
		
	require DUDE_THEME_DIR.'/functions.php';
	
	if (isset($_FILES['cover']) && qa_check_form_security_code('upload_cover', qa_post_text('code'))){
		ra_upload_cover('cover');
	}
	
	if(isset($_REQUEST['ra_ajax'])){
		require DUDE_THEME_DIR.'/inc/ajax.php';

	}else{
		require DUDE_THEME_DIR.'/inc/blocks.php';
	}
