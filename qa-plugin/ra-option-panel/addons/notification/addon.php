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

global $qa_modules;
//unset Q2A Event Notify so that we can override it
unset($qa_modules['event']['Q2A Event Notify']);

//if this is set to true , the email will be written to the log file 
define('QW_SEND_EMAIL_DEBUG_MODE', FALSE );

qa_register_plugin_overrides('addons/notification/overrides.php');
$qw_notification_addon = new Qw_Notification_Addon;

qa_register_plugin_layer('addons/notification/notification-layer.php', 'QW Notification Layer');
qa_register_plugin_module('page', 'addons/notification/notification-page.php', 'qw_notification_page', 'QW Notification Page');
qa_register_plugin_module('page', 'addons/notification/notification-settings.php', 'qw_notification_setting_page', 'QW Notification Settings');

include_once QW_CONTROL_DIR. '/addons/notification/default-email-notify.php';
include_once QW_CONTROL_DIR. '/addons/notification/email-events.php';
include_once QW_CONTROL_DIR. '/addons/notification/default-email-notify.php';
require_once QW_CONTROL_DIR .'/addons/notification/functions.php';

function qw_set_notification_as_read($id){
	if(qa_is_logged_in())
		qa_db_query_sub(
			'UPDATE ^ra_userevent SET `read` = 1 WHERE id=# AND effecteduserid=#',
			(int)$id, qa_get_logged_in_userid()
		);
}

function qw_set_all_activity_as_read($uid){
	qa_db_query_sub(
		'UPDATE ^ra_userevent SET `read` = 1 WHERE effecteduserid=# AND event NOT IN ("u_wall_post", "u_message")',
		$uid
	);
}
function qw_set_all_messages_as_read($uid){
	qa_db_query_sub(
		'UPDATE ^ra_userevent SET `read` = 1 WHERE effecteduserid=# AND event IN ("u_wall_post", "u_message")',
		$uid
	);
}
function qw_get_total_activity($uid){
	return qa_db_read_one_value(qa_db_query_sub(
		'SELECT COUNT(*) FROM ^ra_userevent WHERE `read` = 0 AND effecteduserid=#  AND event NOT IN ("u_wall_post", "u_message")',
		$uid
	), true);
}
function qw_get_total_messages($uid){
	return qa_db_read_one_value(qa_db_query_sub(
		'SELECT COUNT(*) FROM ^ra_userevent WHERE `read` = 0 AND effecteduserid=#  AND event IN ("u_wall_post", "u_message")',
		$uid
	), true);
}

class Qw_Notification_Addon{
	function __construct(){
		qw_add_filter('init_queries', array($this, 'init_queries'));
		qw_event_hook('enqueue_css', NULL, array($this, 'css'));
		qw_event_hook('enqueue_scripts', NULL, array($this, 'scripts'));
		qw_event_hook('qw_ajax_activitylist', NULL, array($this, 'activitylist'));
		qw_event_hook('qw_ajax_messagelist', NULL, array($this, 'messagelist'));
		qw_event_hook('qw_ajax_mark_all_activity', NULL, array($this, 'mark_all_activity'));
		qw_event_hook('qw_ajax_mark_all_messages', NULL, array($this, 'mark_all_messages'));
		qw_event_hook('qw_ajax_activity_count', NULL, array($this, 'activity_count'));
		qw_event_hook('qw_ajax_messages_count', NULL, array($this, 'messages_count'));
		// qw_event_hook('language', NULL, array($this, 'language'));
        qw_event_hook('register_language', NULL, array($this, 'language'));
		
		// added hooks for options and option tabs 
		qw_add_action('qw_theme_option_tab', array($this, 'option_tab'));
        qw_add_action('qw_theme_option_tab_content', array($this, 'option_tab_content'));
        qw_add_action('qw_reset_theme_options', array($this, 'reset_theme_options'));
        qw_event_hook('doctype', NULL, array($this, 'navigation'));
	}
	
	public function init_queries($queries, $tableslc){
		
		$tablename=qa_db_add_table_prefix('ra_email_queue');			
		if (!in_array($tablename, $tableslc)) {

			$queries[] ='
				CREATE TABLE IF NOT EXISTS ^ra_email_queue (
				  id int(6) NOT NULL AUTO_INCREMENT,
				  event varchar(250) NOT NULL,
				  body text NOT NULL,
				  created_by varchar(250) NOT NULL,
				  created_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  status tinyint(1) DEFAULT "0",
				  sent_on timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
			';			
		}
		
		$tablename=qa_db_add_table_prefix('ra_email_queue_receiver');	

		if (!in_array($tablename, $tableslc)) {

			$queries[] ='
				CREATE TABLE IF NOT EXISTS ^ra_email_queue_receiver (
				  id int(6) NOT NULL AUTO_INCREMENT,
				  userid int(10) NOT NULL,
				  email varchar(250) NOT NULL,
				  name varchar(250) NOT NULL,
				  handle varchar(20) NULL,
				  queue_id int(6) NOT NULL,
				  PRIMARY KEY (id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			';			
		}
		
		return $queries;
	}
	
	public function css($css_src){
		
		$css_src['qw_notification'] = array('file' => QW_CONTROL_URL . '/addons/notification/styles.css');
		return  $css_src;
	}
	
	public function scripts($src){		
		$src['qw_notification'] = array('file' => QW_CONTROL_URL . '/addons/notification/script.js', 'footer' => true);

		return  $src;
	}

	public function navigation($themeclass) {
			$link = array(
					'label' => qa_lang_html('notification/notf_settings_user_nav'),
					'url'   => qa_path_html('notification-settings'),
					'icon'  => 'icon-cog' ,
				);
			if(qa_is_logged_in())	{
				$themeclass['navigation']['user']['notification-settings'] = $link ;
				if(qa_request_part(0) == 'notification-settings') {
					$themeclass['navigation']['user']['notification-settings']['selected'] = true;
				}
			}
			
			$handle = qa_request_part(1) ;
			
			if (qa_is_logged_in() && ($handle === qa_get_logged_in_handle() || qa_request_part(0) == 'notification-settings') ) {
				$themeclass['navigation']['sub']['notification-settings'] = $link ;
				if(qa_request_part(0) == 'notification-settings') {
					$themeclass['navigation']['sub']['notification-settings']['selected'] = true;
				}
			}
			return $themeclass;
    }

	public function activitylist(){
		
		$offset = (int)qa_get('offset');
		$offset = isset($offset) ? ($offset*15) : 0;
		
		// get points for each activity
		require_once QA_INCLUDE_DIR.'qa-db-points.php';
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		$optionnames=qa_db_points_option_names();
		$options=qa_get_options($optionnames);
		$multi = (int)$options['points_multiple'];
		$upvote = '';
		$downvote = '';
		if(@$options['points_per_q_voted_up']) {
			$upvote = '_up';
			$downvote = '_down';
		}
		$event_point['in_q_vote_up']     = (int)$options['points_per_q_voted'.$upvote]*$multi;
		$event_point['in_q_vote_down']   = (int)$options['points_per_q_voted'.$downvote]*$multi*(-1);
		$event_point['in_q_unvote_up']   = (int)$options['points_per_q_voted'.$upvote]*$multi*(-1);
		$event_point['in_q_unvote_down'] = (int)$options['points_per_q_voted'.$downvote]*$multi;
		$event_point['a_vote_up']        = (int)$options['points_per_a_voted'.$upvote]*$multi;
		$event_point['in_a_vote_down']   = (int)$options['points_per_a_voted'.$downvote]*$multi*(-1);
		$event_point['in_a_unvote_up']   = (int)$options['points_per_a_voted'.$upvote]*$multi*(-1);
		$event_point['in_a_unvote_down'] = (int)$options['points_per_a_voted'.$downvote]*$multi;
		$event_point['in_a_select']      = (int)$options['points_a_selected']*$multi;
		$event_point['in_a_unselect']    = (int)$options['points_a_selected']*$multi*(-1);
		$event_point['q_post']           = (int)$options['points_post_q']*$multi;
		$event_point['a_post']           = (int)$options['points_post_a']*$multi;
		$event_point['a_select']         = (int)$options['points_select_a']*$multi;
		$event_point['q_vote_up']        = (int)$options['points_vote_up_q']*$multi;
		$event_point['q_vote_down']      = (int)$options['points_vote_down_q']*$multi;
		$event_point['a_vote_up']        = (int)$options['points_vote_up_a']*$multi;
		$event_point['a_vote_down']      = (int)$options['points_vote_down_a']*$multi;
		
		// Get Events
		$userid = qa_get_logged_in_userid();
		$eventslist = qa_db_read_all_assoc(
			qa_db_query_sub( 
				'SELECT id, UNIX_TIMESTAMP(datetime) AS datetime, userid, postid, effecteduserid, event, params, `read` FROM ^ra_userevent WHERE effecteduserid=# AND `read`=0 AND event NOT IN ("u_wall_post", "u_message") ORDER BY datetime DESC LIMIT 15 OFFSET #',
				$userid, $offset 
			)
		);
		
		if(count($eventslist) > 0){
			$event = array();
			$output='';
			$i=0;
			//
			$userids = array();
			foreach ($eventslist as $event){
				$userids[$event['userid']]         =$event['userid'];
				$userids[$event['effecteduserid']] =$event['effecteduserid'];
			}
			if (QA_FINAL_EXTERNAL_USERS)
				$handles=qa_get_public_from_userids($userids);
			else 
				$handles = qa_db_user_get_userid_handles($userids);
			
			// get event's: time, type, parameters
			// get post id of questions
			
			foreach ($eventslist as $event){
				$title       ='';
				$link        ='';
				$vote_status = '';
				$handle      = isset($handles[$event['userid']]) ? $handles[$event['userid']] : qa_lang('main/anonymous') ;
				
				$datetime        = $event['datetime'];
				$event['date']   = qa_html(qa_time_to_string(qa_opt('db_time')-$datetime));
				$event['params'] = json_decode($event['params'],true);
				$id              = ' data-id="'.$event['id'].'"';
				$read            = $event['read'] ? ' read' : ' unread';
				
				$url_param = array('ra_notification' => $event['id']);
				$user_link = qa_path_html('user/'.$handle, $url_param, QW_BASE_URL);
				
				switch($event['event']){
					case 'related': // related question to an answer
						$url = qa_path_html(qa_q_request($event['postid'], $event['params']['title']), $url_param, QW_BASE_URL,null,null);
									
						echo '<div class="event-content clearfix'.$read.''.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/asked_question_related_to_your').'</span>
											<strong class="where">'.qa_lang_html('dude/answer').'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-link"></span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
												
						break;
					case 'a_post': // user's question had been answered
						$anchor = qa_anchor('A', $event['postid']);
						$url    = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						
						$title  = qw_truncate($event['params']['qtitle'], 60);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/answered_your').'</span>
											<strong class="where">'.qa_lang_html('dude/question').'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-answer"></span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';

						break;
					case 'c_post': // user's question had been commented
						$anchor = qa_anchor('C', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						
						if($event['params']['parenttype'] == 'Q')
							$type =	qa_lang_html('dude/question');
						elseif($event['params']['parenttype'] == 'A')
							$type =	qa_lang_html('dude/answer');
						else
							$type =	qa_lang_html('dude/comment');
							
						if(isset($event['params']['parent_uid']) && $event['params']['parent_uid'] != $userid){
							$what =	qa_lang_html('dude/followup_comment');
							$type =	qa_lang_html('dude/comment');
						}else
							$what = qa_lang_html('dude/replied_to_your');
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.$what.'</span>
											<strong class="where">'.$type.'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-arrow-back"></span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';

						break;
					case 'q_reshow': 
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,null);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-eye" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<span>'.qa_lang_html('dude/your').'</span>
											<strong>'.qa_lang_html('dude/question').'</strong>
											<span class="what">'.qa_lang_html('dude/is_visible').'</span>
										</div>
										<div class="footer">
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';

						break;
					case 'a_reshow': // user's question had been answered
						$anchor = qa_anchor('A', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-eye" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<span>'.qa_lang_html('dude/your').'</span>
											<strong>'.qa_lang_html('dude/answer').'</strong>
											<span class="what">'.qa_lang_html('dude/is_visible').'</span>
										</div>
										<div class="footer">
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';

						break;
					case 'c_reshow': // user's question had been answered
						$anchor = qa_anchor('C', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-eye" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<span>'.qa_lang_html('dude/your').'</span>
											<strong>'.qa_lang_html('dude/comment').'</strong>
											<span class="what">'.qa_lang_html('dude/is_visible').'</span>
										</div>
										<div class="footer">
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						
						break;
					case 'a_select':
						$anchor = qa_anchor('A', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/selected_as_best').'</span>
										</div>
										<div class="footer">
											<span class="event-icon icon-award"></span>
											<span class="points">'.qa_lang_sub('dude/you_have_earned_x_points', $event_point['a_post']).'</span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
			
						break;
					case 'q_vote_up': 
						
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null);
						
						$title = qw_truncate($event['params']['qtitle'], 60);
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/upvoted_on_your').'</span>
											<strong class="where">'.qa_lang_html('dude/question').'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-thumb-up"></span>
											<span class="points">'.qa_lang_sub('dude/you_have_earned_x_points', $event_point['a_vote_up']).'</span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						
						break;
					case 'a_vote_up': 
						$anchor = qa_anchor('A', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
					
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/upvoted_on_your').'</span>
											<strong class="where">'.qa_lang_html('dude/answer').'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-thumb-up"></span>
											<span class="points">'.qa_lang_sub('dude/you_have_earned_x_points', $event_point['a_vote_up']).'</span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						
						break;
					case 'q_approve':
						
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-input-checked" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/approved_your').'</span>
											<strong class="where">'.qa_lang_html('dude/question').'</strong>
										</div>
										<div class="footer">
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
					
						break;
					case 'a_approve':
						$anchor = qa_anchor('A', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-input-checked" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/approved_your').'</span>
											<strong class="where">'.qa_lang_html('dude/answer').'</strong>
										</div>
										<div class="footer">
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						
						break;
					case 'u_favorite': 
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$user_link.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/added_you_to').'</span>
											<strong class="where">'.qa_lang_html('dude/favourite').'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-heart"></span>									
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					
					case 'q_favorite': 
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$user_link.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/added_your_question_to').'</span>
											<strong class="where">'.qa_lang_html('dude/favourite').'</strong>
										</div>
										<div class="footer">
											<span class="event-icon icon-heart"></span>									
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					case 'q_vote_down': 
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null);
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-thumb-down" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<span class="what">'.qa_lang_html('dude/you_have_received_down_vote').'</span>
											<strong class="where">'.qa_lang_html('dude/question').'</strong>
										</div>
										<div class="footer">
											<span class="points">'.qa_lang_sub('dude/you_have_lost_x_points', $event_point['q_vote_down']).'</span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					case 'c_approve':
						$anchor = qa_anchor('C', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null,$anchor);
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-input-checked" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/approved_your').'</span>
											<strong class="where">'.qa_lang_html('dude/comment').'</strong>
										</div>
										<div class="footer">									
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					case 'q_reject':
			
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null);
			
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-times" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/your_question_is_rejected').'</span>
										</div>
										<div class="footer">
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
				
						break;
					case 'a_reject':
						$anchor = qa_anchor('A', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null, $anchor);
						
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-times" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/your_answer_is_rejected').'</span>
										</div>
										<div class="footer">									
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					case 'c_reject':
						$anchor = qa_anchor('C', $event['postid']);
						$url = qa_path_html(qa_q_request($event['params']['qid'], $event['params']['qtitle']), $url_param, QW_BASE_URL,null, $anchor);
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-times" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/your_comment_is_rejected').'</span>
										</div>
										<div class="footer">									
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					case 'u_level':
						$url       = qa_path_absolute('user/' . $event['params']['handle']);
                        $old_level = $event['params']['oldlevel'];
                        $new_level = $event['params']['level'];
                        if ($new_level < $old_level) {
                              break ; 
                        }

                        $approved_only = "" ;
                        if (($new_level == QA_USER_LEVEL_APPROVED) && ($old_level < QA_USER_LEVEL_APPROVED)) {
                              $approved_only = true;
                        } else  {
                              $approved_only = false;
                        } 

                        if ($approved_only === false ) {
                              $new_designation = qw_get_user_desg($new_level);
                        }

                        $content = strtr(qa_lang($approved_only ? 'notification/u_level_approved_notf' : 'notification/u_level_improved_notf'), array(
                            '^new_designation' => @$new_designation,
                        )); 
                        
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a class="icon icon-user" href="'.$url.'"></a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.$content.'</span>
										</div>
										<div class="footer">
											<span class="points">'.qa_lang_sub('dude/you_have_earned_x_points', $event_point['a_vote_up']).'</span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';
						break;
					
				}
			}
		}else{
			echo '<div class="no-more-activity">'. qa_lang_html('dude/no_more_activity') .'</div>';
		}

		die();
	}
	public function messagelist(){
		$offset = (int)qa_get('offset');
		$offset = isset($offset) ? ($offset*15) : 0;
		
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		// Get Events
		$message_events = array(
			'u_message',
			'u_wall_post',
		);
		$events = "'".implode("','",$message_events)."'";
		$userid = qa_get_logged_in_userid();
		$eventslist = qa_db_read_all_assoc(
			qa_db_query_sub(
				'SELECT id, UNIX_TIMESTAMP(datetime) AS datetime, userid, postid, effecteduserid, event, params, `read` FROM ^ra_userevent WHERE effecteduserid=# AND `read` = 0 AND event IN (' . $events .') ORDER BY id DESC LIMIT 15 OFFSET #',
				$userid, $offset
			)
		);
		if(count($eventslist) > 0){
			$event = array();

			$userids = array();
			foreach ($eventslist as $event){
				$userids[$event['userid']]=$event['userid'];
				$userids[$event['effecteduserid']]=$event['effecteduserid'];
			}
			if (QA_FINAL_EXTERNAL_USERS)
				$handles=qa_get_public_from_userids($userids);
			else 
				$handles = qa_db_user_get_userid_handles($userids);
			// get event's: time, type, parameters
			// get post id of questions
			foreach ($eventslist as $event){
				$title='';
				$link='';
				$handle = $handles[$event['userid']];
				
				$reciever_handle = $handles[$event['effecteduserid']];
				$reciever_link = qa_path('user/'.$reciever_handle);
				$datetime = $event['datetime'];
				$event['date'] = qa_html(qa_time_to_string(qa_opt('db_time')-$datetime));
				$event['params'] = json_decode($event['params'],true);
				$message = substr($event['params']['message'], 0, 30).'..';
				$id = ' data-id="'.$event['id'].'"';
				$read = $event['read'] ? ' read' : ' unread';
				$url_param = array('ra_notification' => $event['id']);
				$user_link = qa_path_html('user/'.$handle, $url_param);
				
				switch($event['event']){
					case 'u_message': // related question to an answer
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.qa_path_html('message/'.$handle, $url_param, QW_BASE_URL).'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/sent_you_a_private_message').'</span>
											<span class="message">'.$message.'</span>
										</div>
										<div class="footer">
											<span class="event-icon icon-email"></span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';						
						break;
					case 'u_wall_post': // user's question had been answered
						$url = qa_path_html('user/'.$reciever_handle.'/wall', $url_param, QW_BASE_URL);
						echo '<div class="event-content clearfix'.$read.'"'.$id.'>
								<div class="avatar"><a href="'.$user_link.'">'.ra_get_avatar($handle, 32, true).'</a></div>
								<div class="event-right">
									<a href="'.$url.'">
										<div class="head">
											<strong class="user">'.$handle.'</strong>
											<span class="what">'.qa_lang_html('dude/posted_on_your_wall').'</span>
											<span class="message">'.$message.'</span>
										</div>
										<div class="footer">
											<span class="event-icon icon-pin"></span>
											<span class="date">'.qa_lang_sub('dude/x_ago', $event['date']).'</span>
										</div>
									</a>
								</div>
							</div>';						
						break;
				}

			}
		}else{
			echo '<div class="no-more-activity">'. qa_lang_html('dude/no_more_messages') .'</div>';
		}

		die();
	} 

	
	public function mark_all_activity(){
		if(qa_is_logged_in())
			qw_set_all_activity_as_read(qa_get_logged_in_userid());
		
		die();
	}
	public function mark_all_messages(){
		if(qa_is_logged_in())
			qw_set_all_messages_as_read(qa_get_logged_in_userid());
		
		die();
	}

	public function activity_count(){
		echo qw_get_total_activity(qa_get_logged_in_userid());
		// adding the feature of scheduler check here to make sure the sending email called on time 
		$time_out = qa_opt('qw_process_emails_from_db_time_out');
		
		if(QW_SEND_EMAIL_DEBUG_MODE){
			qw_process_emails_from_db();
		}elseif (!$time_out) {
			// if the scheduler is not set set it for 15 mins 
			qw_scheduler_set('qw_process_emails_from_db' , 6*60*60 /*4 times a day by default*/ );
		}	
		
		qw_check_scheduler('qw_process_emails_from_db');
		die();
	}

	public function messages_count(){
		echo qw_get_total_messages(qa_get_logged_in_userid());
		
		die();
	}
	public function language($lang_arr){
		$lang_arr['notification'] = QW_CONTROL_DIR .'/addons/notification/language-*.php';
		return $lang_arr;
	}
	// adding options and option tab 
	public function option_tab(){
		$saved = false;
			
            if (qa_clicked('qw_save_button')) {
                  $enable_plugin = !!qa_post_text('qw_enable_email_notfn_field');
                  qa_opt('qw_enable_email_notfn', $enable_plugin);
                  if (!$enable_plugin) {
                        //if the plugin is disabled then turn off all features 
                        reset_all_notification_options();
                  } else {
                        $response = set_all_notification_options();
                        //$error will be false if the 
                        $error = (isset($response) && is_array($response) && !empty($response)) ? true : false;
                  }
                  $notification_time_out = qa_post_text('qw_process_emails_from_db_time_out');
                  if ($notification_time_out > 0) {	
                  		qa_opt('qw_process_emails_from_db_time_out' , $notification_time_out );
                  }
                  $saved = true;
            }

		return '<li>
				<a href="#" data-toggle=".qa-part-form-tc-notify">Notification</a>
			</li>
			<li>
				<a href="#" data-toggle=".qa-part-form-tc-email">Email templates</a>
			</li>';
	  }
	 public function option_tab_content(){
		$all_options = array(
				'qw_enable_email_notfn' ,
				'qw_notify_tag_followers' ,
				'qw_notify_cat_followers' ,
				'qw_notify_user_followers' ,
				'qw_notify_min_points_opt' ,
			);
		$output = '<div class="qa-part-form-tc-notify">
			<h3>Email Notification Settings</h3>
			<table class="qa-form-tall-table options-table">';
		
				
				foreach ($all_options as $option) {
					$output .= '<tbody>' ;
					$output .= '<tr>
									<th class="qa-form-tall-label">' . qa_lang("notification/".$option."_lang") .'</th>
									<td class="qa-form-tall-data">
										<input type="checkbox"' . (qa_opt($option) ? ' checked="checked"' : '') . ' id="qw_styling_rtl" name="'.$option.'_field" data-opts=".'.$option.'_fields">
									</td>
								</tr>' ;
								if ($option == 'qw_notify_min_points_opt') {
									$output .= '<tr class="qw_notify_min_points_opt_fields' . (qa_opt('qw_notify_min_points_opt') ? ' csshow' : ' cshide') . '" >
													<th class="qa-form-tall-label">' . qa_lang("notification/qw_notify_min_points_val_lang") .'</th>
														<td class="qa-form-tall-data">
															<input type="text" value="' . qa_opt('qw_notify_min_points_val') . '" id="qw_styling_rtl" name="qw_notify_min_points_val_field" data-opts="qw_notify_min_points_val_fields">
														</td>
												</tr>' ;
								}
					$output .= '</tbody>' ;

				}
				
				$output .= '<tbody>' ;
					$output .= '<tr>
									<th class="qa-form-tall-label">' . qa_lang("notification/qw_notify_enable_async_lang") .'</th>
									<td class="qa-form-tall-data">
										<input type="checkbox" checked="true" id="qw_styling_rtl" name="qw_notify_enable_async_field" data-opts="qw_notify_enable_async_fields">
									</td>
								</tr>' ;
				$output .= '</tbody>' ;

				$output .= '<tbody>' ;
					$output .= '<tr>
									<th class="qa-form-tall-label">' . qa_lang("notification/qw_notify_enable_summerize_email_lang") .'</th>
									<td class="qa-form-tall-data">
										<input type="checkbox" checked="true" id="qw_styling_rtl" name="qw_notify_enable_summerize_email_field" data-opts="qw_notify_enable_summerize_email_fields">
									</td>
								</tr>' ;
				$output .= '</tbody>' ;

				$output .= '<tbody>' ;
				$option_value = array(
							"choose_one"              => -1 ,
							"once_a_day"              => 24*60*60 ,
							"twice_a_day"             => 12*60*60 ,
							"four_times_a_day"        => 6*60*60 , /*this is the default value*/
							"six_times_a_day"         => 4*60*60 ,
							"eight_times_a_day"       => 3*60*60 ,
							"twelve_times_a_day"      => 2*60*60 ,
							"sixteen_times_a_day"     => 1.5*60*60 ,
							"twenty_times_a_day"      => 1.2*60*60 ,
							"twenty_four_times_a_day" => 1*60*60 ,
					);

				$selected_frequency = qa_opt('qw_process_emails_from_db_time_out');

				if (!$selected_frequency) {
					$selected_frequency = 6*60*60 ;
					qa_opt('qw_process_emails_from_db_time_out' , $selected_frequency );
				}
				
				$select_string = "" ;
				foreach ($option_value as $key => $value) {
					$selected = ($value == $selected_frequency) ? " selected " : "" ;
					$select_string .= '<option value="'.$value.'"'. $selected.'>'.qa_lang("notification/{$key}_lang").'</option>' ;
				}
				$output .= '<tr>
									<th class="qa-form-tall-label">' . qa_lang("notification/qw_notify_freq_per_day_opt_lang") .'</th>
									<td class="qa-form-tall-data">
										<select id="qw_styling_rtl" name="qw_process_emails_from_db_time_out" data-opts="qw_process_emails_from_db_time_out_fields"> 
											'.$select_string.'
										</select>
									</td>
								</tr>' ;
				$output .= '</tbody>' ;

				$output .= '<tbody>' ;
					$output .= '<tr class="" >
									<th class="qa-form-tall-label">' . qa_lang("notification/qw_all_notification_page_size_lang") .'</th>
										<td class="qa-form-tall-data">
											<input type="text" value="' . qa_opt('qw_all_notification_page_size') . '" id="qw_styling_rtl" name="qw_all_notification_page_size_field" data-opts="qw_all_notification_page_size_fields">
										</td>
								</tr>' ;
				$output .= '</tbody>' ;
				
			$output .= '</table></div>';

			$output .= '<div class="qa-part-form-tc-email">
						<h3>Email templates</h3>
						<table class="qa-form-tall-table options-table">';
			$output .= '<tbody>' ;
			$output .= '<tr class="" >
							<th class="qa-form-tall-label">
								Email header
								<span class="description">Set table based header for email</span>
							</th>
							<td class="qa-form-tall-data">
								<textarea id="qw_email_head" name="qw_email_head_field" rows="10">' . qa_opt('qw_email_head') . '</textarea>
							</td>
						</tr>' ;

			$output .= '<tr class="" >
							<th class="qa-form-tall-label">
								Email Body
								<span class="description">Set table based Body for email (must contain {body} which will be replaced by the email content )</span>
							</th>
							<td class="qa-form-tall-data">
								<textarea id="qw_email_body" name="qw_email_body_field" rows="10">' . qa_opt('qw_email_body') . '</textarea>
							</td>
						</tr>' ;

			$output .= '<tr class="" >
							<th class="qa-form-tall-label">
								Email footer
								<span class="description">Set table based footer for email</span>
							</th>
							<td class="qa-form-tall-data">
								<textarea id="qw_email_footer" name="qw_email_footer_field" rows="10">' . qa_opt('qw_email_footer') . '</textarea>
							</td>
						</tr>' ;

			$output .= '</tbody>' ;
			
			$output .= '</table></div>';
			return $output;
	  }

	  public function reset_theme_options() {
	  		reset_all_notification_options();
	  }

}

