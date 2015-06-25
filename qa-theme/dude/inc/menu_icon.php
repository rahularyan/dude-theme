<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Menu icons */
	
	// set icon for main
	/* $this->content['navigation']['main']['questions']['icon'] 			= 'icon-question-2';
	$this->content['navigation']['main']['unanswered']['icon'] 		= 'icon-notification';
	$this->content['navigation']['main']['tag']['icon'] 				= 'icon-tag';
	$this->content['navigation']['main']['user']['icon'] 				= 'icon-locked';
	$this->content['navigation']['main']['categories']['icon'] 			= 'icon-folder-close';
	$this->content['navigation']['main']['ask']['icon'] 				= 'icon-chat-3'; */
	
	if(qa_get_logged_in_level()>QA_USER_LEVEL_ADMIN){
		$this->content['navigation']['main']['admin']['icon']			= 'icon-wrench';	
	
		//set icon for admin
		$this->content['navigation']['ra_nav']['admin']['admin/general']['icon'] 			= 'icon-cog';
		$this->content['navigation']['ra_nav']['admin']['admin/emails']['icon'] 			= 'icon-envelope';
		$this->content['navigation']['ra_nav']['admin']['admin/user']['icon'] 				= 'icon-group';
		$this->content['navigation']['ra_nav']['admin']['admin/layout']['icon'] 			= 'icon-layout-10';
		$this->content['navigation']['ra_nav']['admin']['admin/posting']['icon'] 			= 'icon-plus';
		$this->content['navigation']['ra_nav']['admin']['admin/viewing']['icon'] 			= 'icon-monitor';
		$this->content['navigation']['ra_nav']['admin']['admin/lists']['icon'] 			= 'icon-reorder';
		$this->content['navigation']['ra_nav']['admin']['admin/categories']['icon'] 		= 'icon-folder-close';
		$this->content['navigation']['ra_nav']['admin']['admin/permissions']['icon'] 		= 'icon-locked';
		$this->content['navigation']['ra_nav']['admin']['admin/pages']['icon'] 			= 'icon-file';
		$this->content['navigation']['ra_nav']['admin']['admin/feeds']['icon'] 			= 'icon-rss';
		$this->content['navigation']['ra_nav']['admin']['admin/points']['icon'] 			= 'icon-medal';
		$this->content['navigation']['ra_nav']['admin']['admin/spam']['icon'] 				= 'icon-warning';
		$this->content['navigation']['ra_nav']['admin']['admin/stats']['icon'] 			= 'icon-chart-2';
		$this->content['navigation']['ra_nav']['admin']['admin/mailing']['icon'] 			= 'icon-envelope-alt';
		$this->content['navigation']['ra_nav']['admin']['admin/plugins']['icon'] 			= 'icon-cord';
		$this->content['navigation']['ra_nav']['admin']['admin/moderate']['icon'] 			= 'icon-edit';
		//$this->content['navigation']['ra_nav']['admin']['admin/flagged']['icon'] 		= 'icon-edit';
		//$this->content['navigation']['ra_nav']['admin']['admin/hidden']['icon'] 			= 'icon-edit';	
		
		
		

	}
	if (qa_is_logged_in()) {
		//user menu icon
		$this->content['navigation']['user']['account']['icon']		= 'icon-user-2';
		$this->content['navigation']['user']['updates']['icon']		= 'icon-bullhorn';
		$this->content['navigation']['user']['logout']['icon']			= 'icon-switch';
	}