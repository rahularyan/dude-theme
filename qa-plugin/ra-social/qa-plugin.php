<?php


/*
	Plugin Name: RA Social
	Plugin URI: http://www.rahularyan.com
	Plugin Description: Shows social links
	Plugin Version: 1.1
	Plugin Date: 2013-10-25
	Plugin Author: Rahul Aryan
	Plugin Author URI: http://www.rahularyan.com/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: 
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}
	
	qa_register_plugin_layer('ra-social-layer.php', 'RA Social Layer');
	qa_register_plugin_module('widget', 'ra-social.php', 'ra_social', 'RA Social');
	