<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_notification_setting_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{		
		if ($request=='notification-settings')
			return true;

		return false;
	}
	
	function process_request($request)
	{
		$start=qa_get_start();
		$userid=qa_get_logged_in_userid();
		//	Prepare content for theme
		
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		require_once QW_CONTROL_DIR.'/addons/social-login/cs-social-login-utils.php';

		if (QA_FINAL_EXTERNAL_USERS)
			qa_fatal_error('User accounts are handled by external code');
		
		if (!isset($userid))
			qa_redirect('login');
		
		$qa_content=qa_content_prepare();
		$qa_content['title']=qa_lang_html('notification/my_notification_settings');
		$qa_content['site_title']= qa_opt('site_title') ;
    	
		if (qa_clicked('save_notf_user_settings')) {
			$data_to_save = array(
					'qw_mail_when_a_post'         => !!qa_post_text('qw_mail_when_a_post') ,
					'qw_mail_when_related'        => !!qa_post_text('qw_mail_when_related') ,
					'qw_mail_when_c_post'         => !!qa_post_text('qw_mail_when_c_post') ,
					'qw_mail_when_q_reshow'       => !!qa_post_text('qw_mail_when_q_reshow') ,
					'qw_mail_when_c_reshow'       => !!qa_post_text('qw_mail_when_c_reshow') ,
					'qw_mail_when_a_select'       => !!qa_post_text('qw_mail_when_a_select') ,
					'qw_mail_when_q_vote_up'      => !!qa_post_text('qw_mail_when_q_vote_up') ,
					'qw_mail_when_q_vote_down'    => !!qa_post_text('qw_mail_when_q_vote_down') ,
					'qw_mail_when_a_vote_up'      => !!qa_post_text('qw_mail_when_a_vote_up') ,
					'qw_mail_when_a_vote_down'    => !!qa_post_text('qw_mail_when_a_vote_down') ,
					'qw_mail_when_q_favorite'     => !!qa_post_text('qw_mail_when_q_favorite') ,
					'qw_mail_when_u_favorite'     => !!qa_post_text('qw_mail_when_u_favorite') ,
					'qw_mail_when_u_message'      => !!qa_post_text('qw_mail_when_u_message') ,
					'qw_mail_when_u_wall_post'    => !!qa_post_text('qw_mail_when_u_wall_post') ,
					'qw_mail_when_u_level'        => !!qa_post_text('qw_mail_when_u_level') ,
					'qw_mail_when_q_post_user_fl' => !!qa_post_text('qw_mail_when_q_post_user_fl') ,
					'qw_mail_when_q_post_tag_fl'  => !!qa_post_text('qw_mail_when_q_post_tag_fl') ,
					'qw_mail_when_q_post_cat_fl'  => !!qa_post_text('qw_mail_when_q_post_cat_fl') ,
					'qw_mail_when_q_approve'      => !!qa_post_text('qw_mail_when_q_approve') ,
					'qw_mail_when_q_reject'       => !!qa_post_text('qw_mail_when_q_reject') ,
					'qw_mail_when_a_approve'      => !!qa_post_text('qw_mail_when_a_approve') ,
					'qw_mail_when_a_reject'       => !!qa_post_text('qw_mail_when_a_reject') ,
					'qw_mail_when_c_approve'      => !!qa_post_text('qw_mail_when_c_approve') ,
					'qw_mail_when_c_reject'       => !!qa_post_text('qw_mail_when_c_reject') ,
				);
			qw_save_notification_settings(json_encode($data_to_save) , $userid);
			qa_redirect('notification-settings', array('state' => 'settings-saved'));
		}

		$disp_conf = qa_get('confirm');
		
		$preferences = qw_get_notification_settings($userid);
		// qw_log(print_r(qw_check_pref_for_event($userid , 'a_post') , true )) ;

		if(!$disp_conf) {
			// display some summary about the user
			$qa_content['form_profile']=array(
				'title' => qa_lang_html('notification/my_notification_settings'),
				'tags'  => 'METHOD="POST" ACTION="'.qa_self_html().'" CLASS="social-login-settings"',
				'style' => 'wide',
				'buttons' => array(
					'check_all' => array(
						'type' => 'button' ,
						'tags'  => 'name="check_all_notf_fields" id="check_all_notf_fields" ',
						'label' => qa_lang_html('notification/check_all'),
					),
					'uncheck_all' => array(
						'type' => 'button' ,
						'tags'  => 'name="un_check_all_notf_fields" id="un_check_all_notf_fields" ',
						'label' => qa_lang_html('notification/uncheck_all'),
					),
					'save' => array(
						'tags'  => 'onClick="qa_show_waiting_after(this, false);"',
						'label' => qa_lang_html('notification/save_settings'),
					),
				),
				'fields' =>array(
						'qw_mail_when_a_post' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_a_post_lable'),
								'tags'  => 'NAME="qw_mail_when_a_post"',
								'value' => @$preferences['qw_mail_when_a_post'] ? true : false,
							),
						'qw_mail_when_related' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_related_lable'),
								'tags'  => 'NAME="qw_mail_when_related"',
								'value' => @$preferences['qw_mail_when_related'] ? true : false,
							),
						'qw_mail_when_c_post' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_c_post_lable'),
								'tags'  => 'NAME="qw_mail_when_c_post"',
								'value' => @$preferences['qw_mail_when_c_post'] ? true : false,
							),
						'qw_mail_when_q_reshow' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_reshow_lable'),
								'tags'  => 'NAME="qw_mail_when_q_reshow"',
								'value' => @$preferences['qw_mail_when_q_reshow'] ? true : false,
							),
						'qw_mail_when_c_reshow' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_c_reshow_lable'),
								'tags'  => 'NAME="qw_mail_when_c_reshow"',
								'value' => @$preferences['qw_mail_when_c_reshow'] ? true : false,
							),
						'qw_mail_when_a_select' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_a_select_lable'),
								'tags'  => 'NAME="qw_mail_when_a_select"',
								'value' => @$preferences['qw_mail_when_a_select'] ? true : false,
							),
						'qw_mail_when_q_vote_up' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_vote_up_lable'),
								'tags'  => 'NAME="qw_mail_when_q_vote_up"',
								'value' => @$preferences['qw_mail_when_q_vote_up'] ? true : false,
							),
						'qw_mail_when_q_vote_down' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_vote_down_lable'),
								'tags'  => 'NAME="qw_mail_when_q_vote_down"',
								'value' => @$preferences['qw_mail_when_q_vote_down'] ? true : false,
							),
						'qw_mail_when_a_vote_up' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_a_vote_up_lable'),
								'tags'  => 'NAME="qw_mail_when_a_vote_up"',
								'value' => @$preferences['qw_mail_when_a_vote_up'] ? true : false,
							),
						'qw_mail_when_a_vote_down' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_a_vote_down_lable'),
								'tags'  => 'NAME="qw_mail_when_a_vote_down"',
								'value' => @$preferences['qw_mail_when_a_vote_down'] ? true : false,
							),
						'qw_mail_when_q_favorite' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_favorite_lable'),
								'tags'  => 'NAME="qw_mail_when_q_favorite"',
								'value' => @$preferences['qw_mail_when_q_favorite'] ? true : false,
							),
						'qw_mail_when_u_favorite' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_u_favorite_lable'),
								'tags'  => 'NAME="qw_mail_when_u_favorite"',
								'value' => @$preferences['qw_mail_when_u_favorite'] ? true : false,
							),
						'qw_mail_when_u_message' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_u_message_lable'),
								'tags'  => 'NAME="qw_mail_when_u_message"',
								'value' => @$preferences['qw_mail_when_u_message'] ? true : false,
							),
						'qw_mail_when_u_wall_post' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_u_wall_post_lable'),
								'tags'  => 'NAME="qw_mail_when_u_wall_post"',
								'value' => @$preferences['qw_mail_when_u_wall_post'] ? true : false,
							),
						'qw_mail_when_u_level' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_u_level_lable'),
								'tags'  => 'NAME="qw_mail_when_u_level"',
								'value' => @$preferences['qw_mail_when_u_level'] ? true : false,
							),
						'qw_mail_when_q_post_user_fl' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_post_user_fl_lable'),
								'tags'  => 'NAME="qw_mail_when_q_post_user_fl"',
								'value' => @$preferences['qw_mail_when_q_post_user_fl'] ? true : false,
							),
						'qw_mail_when_q_post_tag_fl' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_post_tag_fl_lable'),
								'tags'  => 'NAME="qw_mail_when_q_post_tag_fl"',
								'value' => @$preferences['qw_mail_when_q_post_tag_fl'] ? true : false,
							),
						'qw_mail_when_q_post_cat_fl' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_post_cat_fl_lable'),
								'tags'  => 'NAME="qw_mail_when_q_post_cat_fl"',
								'value' => @$preferences['qw_mail_when_q_post_cat_fl'] ? true : false,
							),
						'qw_mail_when_q_approve' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_approve_lable'),
								'tags'  => 'NAME="qw_mail_when_q_approve"',
								'value' => @$preferences['qw_mail_when_q_approve'] ? true : false,
							),
						'qw_mail_when_q_reject' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_q_reject_lable'),
								'tags'  => 'NAME="qw_mail_when_q_reject"',
								'value' => @$preferences['qw_mail_when_q_reject'] ? true : false,
							),
						'qw_mail_when_a_approve' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_a_approve_lable'),
								'tags'  => 'NAME="qw_mail_when_a_approve"',
								'value' => @$preferences['qw_mail_when_a_approve'] ? true : false,
							),
						'qw_mail_when_a_reject' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_a_reject_lable'),
								'tags'  => 'NAME="qw_mail_when_a_reject"',
								'value' => @$preferences['qw_mail_when_a_reject'] ? true : false,
							),
						'qw_mail_when_c_approve' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_c_approve_lable'),
								'tags'  => 'NAME="qw_mail_when_c_approve"',
								'value' => @$preferences['qw_mail_when_c_approve'] ? true : false,
							),
						'qw_mail_when_c_reject' => array(
								'type'  => 'checkbox',
								'label' => qa_lang_html('notification/mail_when_c_reject_lable'),
								'tags'  => 'NAME="qw_mail_when_c_reject"',
								'value' => @$preferences['qw_mail_when_c_reject'] ? true : false,
							),

					) ,
				'hidden' => array(
					'save_notf_user_settings' => '1'
				),

			);

			if (qa_get_state()=='settings-saved') {
				$qa_content['form_profile']['ok']=qa_lang_html('notification/settings_saved');
			}
			
		}
		
		$qa_content['navigation']['sub']=qa_account_sub_navigation();

		return $qa_content;	
	}
}

