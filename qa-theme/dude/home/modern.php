<?php 
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Home page template */
	$context->ra_dynamic_layout('home'); 