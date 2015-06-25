<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	//load dynamic page layout
	if(isset($_REQUEST['state']) && $_REQUEST['state'] == 'edit'){
		$context->output('<div class="edit-user container">');
		$context->sc_main_parts();
		$context->output('</div>');
	}elseif($context->template == 'user' && qa_get('tab')=='history' && qa_opt('event_logger_to_database') && qa_opt('user_act_list_active')) {
		$context->form($context->user_activity_form());
	}else{
		$context->ra_dynamic_layout('user');	
	}
