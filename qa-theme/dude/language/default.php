<?php 
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Language file */
	
	
	global $qa_content;
	$qa_content['lang'] = array(

	/* 	'ask questions' => 						'Hacer Pregunta', */


	);
	