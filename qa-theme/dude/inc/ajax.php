<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Ajax request handler */
	
	
	// will clear all HTML out put
	class qa_html_theme extends qa_html_theme_base{
		function doctype(){			
			if(isset($_REQUEST['ra_ajax_theme'])){
				$action = 'ra_ajax_theme_'.$_REQUEST['action'];
				$this->$action();
			}
		}
		function html(){
			// keep this blank if ajax request
			
		}
		function finish(){}
		
	}
	
	if(!isset($_REQUEST['ra_ajax_theme'])){
		$action = 'ra_ajax_'.$_REQUEST['action'];
		$action();
	}
