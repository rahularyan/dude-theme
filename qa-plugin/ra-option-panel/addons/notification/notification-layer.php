<?php

/*
	Name:QW Notification
	Type:layer
	Class:qw_notification_layer
	Version:1.0
	Author: Rahul Aryan
	Description:For showing ajax users notification
*/	

/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
class qa_html_theme_layer extends qa_html_theme_base {
	function doctype(){
		qa_html_theme_base::doctype();
		$qw_notification_id = qa_get('ra_notification');
		
		if(isset($qw_notification_id))
			qw_set_notification_as_read($qw_notification_id);
	}
	function qw_notification_btn(){
		//if (true){ // check options
			$userid = qa_get_logged_in_userid();
			if (isset( $userid )){
				$handle = qa_get_logged_in_handle();
				$this->output('
					<div class="user-actions pull-right">
						<div class="activity-bar">
							<div class="button dropdown">
								<a href="' . qa_path_html('user/' . $handle . '/activity') . '" class=" icon-bullhorn dropdown-toggle activitylist" data-toggle="dropdown" id="activitylist"></a>
								<div class="dropdown-menu activity-dropdown-list pull-right" id="activity-dropdown-list">
									<div class="bar">
										<span>'.qa_lang_html('dude/notifications').'</span>
										<a class="mark-activity" href="#" data-id="'.qa_get_logged_in_userid().'">'.qa_lang('dude/mark_all_as_read').'</a>
									</div>
									<div class="append">
										<div class="ajax-list"></div>
										<span class="loading"></span>
										<div class="no-activity icon-chart-bar">'.qa_lang('dude/no-activity').'</div>
									</div>
									
									<a class="event-footer" href="'.qa_path_html('notifications', null, QW_BASE_URL).'">'.qa_lang('dude/see_all').'</a>
									
								</div>
							</div>
						</div>
						
						<div class="message-bar">
							<div class="button dropdown">
								<a href="' . qa_path_html('user/' . $handle . '/message') . '" class=" icon-envelope-alt dropdown-toggle messagelist" data-toggle="dropdown" id="messagelist"></a>
								<div class="dropdown-menu message-dropdown-list pull-right" id="message-dropdown-list">
									<div class="bar">
										<span>'.qa_lang_html('dude/messages').'</span>
										<a class="mark-messages" href="#">'.qa_lang('dude/mark_all_as_read').'</a>
									</div>
									<div class="append">
										<div class="ajax-list"></div>
										<span class="loading"></span>
										<div class="no-activity icon-chart-bar">'.qa_lang('dude/no-activity').'</div>
									</div>
									
									<a class="event-footer" href="'.qa_path_html('user/'.$handle.'/wall', null, QW_BASE_URL).'">'.qa_lang('dude/see_all').'</a>
									
								</div>
							</div>
						</div>
					</div>
				');
			}
		//}
	}
	
	
}
