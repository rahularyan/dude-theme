<?php 
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	//load dynamic page layout

	$context->ra_dynamic_layout($context->template);
