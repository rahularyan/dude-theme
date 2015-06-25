<?php

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'qa-app-emails.php';
	require_once QA_INCLUDE_DIR.'qa-app-format.php';
	require_once QA_INCLUDE_DIR.'qa-util-string.php';

	class qw_default_notify {

		function __construct(){
			qw_add_action('qw_event_q_queue',   array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_q_requeue', array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_a_queue',   array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_a_requeue', array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_c_queue',   array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_c_requeue', array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_q_flag',    array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_a_flag',    array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_c_flag',    array($this, 'send_default_emails')) ;
			qw_add_action('qw_event_u_register',array($this, 'send_default_emails')) ;
		}

		function send_default_emails($event, $userid, $handle, $cookieid, $params)
		{

			switch ($event) {
				case 'q_queue':
				case 'q_requeue':
					if (qa_opt('moderate_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null,
							($event=='q_requeue') ? qa_lang('emails/remoderate_subject') : qa_lang('emails/moderate_subject'),
							($event=='q_requeue') ? nl2br(qa_lang('emails/remoderate_body')) : nl2br(qa_lang('emails/moderate_body')),
							array(
								'^p_handle' => isset($handle) ? $handle : (strlen($params['name']) ? $params['name'] :
									(strlen(@$oldquestion['name']) ? $oldquestion['name'] : qa_lang('main/anonymous'))),
								'^p_context' => trim(@$params['title']."\n\n".$params['text']), // don't censor for admin
								'^url' => qa_q_path($params['postid'], $params['title'], true),
								'^a_url' => qa_path_absolute('admin/moderate'),
							)
						);
					break;
					

				case 'a_queue':
				case 'a_requeue':
					if (qa_opt('moderate_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null,
							($event=='a_requeue') ? qa_lang('emails/remoderate_subject') : qa_lang('emails/moderate_subject'),
							($event=='a_requeue') ? nl2br(qa_lang('emails/remoderate_body')) : nl2br(qa_lang('emails/moderate_body')),
							array(
								'^p_handle' => isset($handle) ? $handle : (strlen($params['name']) ? $params['name'] :
									(strlen(@$oldanswer['name']) ? $oldanswer['name'] : qa_lang('main/anonymous'))),
								'^p_context' => $params['text'], // don't censor for admin
								'^url' => qa_q_path($params['parentid'], $params['parent']['title'], true, 'A', $params['postid']),
								'^a_url' => qa_path_absolute('admin/moderate'),
							)
						);
					break;
					

				case 'c_queue':
				case 'c_requeue':
					if (qa_opt('moderate_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null,
							($event=='c_requeue') ? qa_lang('emails/remoderate_subject') : qa_lang('emails/moderate_subject'),
							($event=='c_requeue') ? nl2br(qa_lang('emails/remoderate_body')) : nl2br(qa_lang('emails/moderate_body')),
							array(
								'^p_handle' => isset($handle) ? $handle : (strlen($params['name']) ? $params['name'] :
									(strlen(@$oldcomment['name']) ? $oldcomment['name'] : // could also be after answer converted to comment
									(strlen(@$oldanswer['name']) ? $oldanswer['name'] : qa_lang('main/anonymous')))),
								'^p_context' => $params['text'], // don't censor for admin
								'^url' => qa_q_path($params['questionid'], $params['question']['title'], true, 'C', $params['postid']),
								'^a_url' => qa_path_absolute('admin/moderate'),
							)
						);
					break;

					
				case 'q_flag':
				case 'a_flag':
				case 'c_flag':
					$flagcount=$params['flagcount'];
					$oldpost=$params['oldpost'];
					$notifycount=$flagcount-qa_opt('flagging_notify_first');
					
					if ( ($notifycount>=0) && (($notifycount % qa_opt('flagging_notify_every'))==0) )
						qw_send_notification(null, qa_opt('feedback_email'), null, qa_lang('emails/flagged_subject'), nl2br(qa_lang('emails/flagged_body')), array(
							'^p_handle' => isset($oldpost['handle']) ? $oldpost['handle'] :
								(strlen($oldpost['name']) ? $oldpost['name'] : qa_lang('main/anonymous')),
							'^flags' => ($flagcount==1) ? qa_lang_html_sub('main/1_flag', '1', '1') : qa_lang_html_sub('main/x_flags', $flagcount),
							'^p_context' => trim(@$oldpost['title']."\n\n".qa_viewer_text($oldpost['content'], $oldpost['format'])), // don't censor for admin
							'^url' => qa_q_path($params['questionid'], $params['question']['title'], true, $oldpost['basetype'], $oldpost['postid']),
							'^a_url' => qa_path_absolute('admin/flagged'),
						));
					break;
				case 'u_register':
					if (qa_opt('register_notify_admin'))
						qw_send_notification(null, qa_opt('feedback_email'), null, qa_lang('emails/u_registered_subject'),
							qa_opt('moderate_users') ? nl2br(qa_lang('emails/u_to_approve_body')) : nl2br(qa_lang('emails/u_registered_body')), array(
							'^u_handle' => $handle,
							'^url' => qa_path_absolute('user/'.$handle),
							'^a_url' => qa_path_absolute('admin/approve'),
						));
					break;

			}
		}
	
	}
	
$qw_default_notify_obj = new qw_default_notify ;
/*
	Omit PHP closing tag to help avoid accidental output
*/