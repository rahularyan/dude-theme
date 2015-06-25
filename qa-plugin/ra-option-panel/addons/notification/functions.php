<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}
function qw_get_avatar($handle, $size = 40, $html =true){
	$userid = qa_handle_to_userid($handle);
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		$img_html = get_avatar( qa_get_user_email($userid), $size);
	}else if(QA_FINAL_EXTERNAL_USERS){
		$img_html = qa_get_external_avatar_html($userid, $size, false);
	}else{
		if (!isset($handle)){

			if ( qa_opt('avatar_allow_upload') && qa_opt('avatar_default_show') && strlen(qa_opt('avatar_default_blobid')) )
				$img = qa_opt('avatar_default_blobid');
			else
				$img = '';
		}else{
			$f = ra_user_data($handle);
			if(empty($f['account']['avatarblobid'])){
				if ( qa_opt('avatar_allow_upload') && qa_opt('avatar_default_show') && strlen(qa_opt('avatar_default_blobid')) )
					$img = qa_opt('avatar_default_blobid');
				else
					$img = '';
			} else
				$img = $f['account']['avatarblobid'];
		}
	}
	if (empty($img))
		return;

	if($html)
		return '<a href="'.qa_path_absolute('user/'.$handle).'"><img src="'.qa_path_absolute('', array('qa' => 'image', 'qa_blobid' => $img, 'qa_size' => $size)).'" /></a>';		
	elseif(!empty($img))
		return qa_path_absolute('', array('qa' => 'image', 'qa_blobid' => $img, 'qa_size' => $size));
}
function qw_save_notification_settings($data , $userid)
{
    require_once QA_INCLUDE_DIR.'qa-db-users.php';
    $key = 'qw_notification_settings' ;
    qa_db_user_profile_set($userid, $key, $data);
    
}

function qw_get_notification_settings($userid)
{
   $key = 'qw_notification_settings' ;
   $value = qa_db_read_one_value(qa_db_query_sub("SELECT ^userprofile.content from  ^userprofile WHERE ^userprofile.title = # AND ^userprofile.userid = # " , $key , $userid ), true );
   return json_decode($value, true);
}


function reset_all_notification_options() {
      qa_opt('qw_notify_cat_followers', false);
      qa_opt('qw_notify_tag_followers', false);
      qa_opt('qw_notify_user_followers', false);
      qa_opt('qw_notify_min_points_opt', false);
      qa_opt('qw_notify_min_points_val', 0);
      qa_opt('qw_all_notification_page_size', 15 );
      qa_opt('qw_email_notf_enable', false);
}

function reset_all_notification_points_options() {
      qa_opt('qw_notify_min_points_opt', false);
      qa_opt('qw_notify_min_points_val', 0);
}

function set_all_notification_options() {

      $error = array();
      //if plugin is enabled then atlest one option has to be enabled 
      if (options_selected()) {
            qa_opt('qw_email_notf_debug_mode', !!qa_post_text('qw_email_notf_debug_mode_field'));
            qa_opt('qw_notify_cat_followers', !!qa_post_text('qw_notify_cat_followers_field'));
            qa_opt('qw_notify_tag_followers', !!qa_post_text('qw_notify_tag_followers_field'));
            qa_opt('qw_notify_user_followers', !!qa_post_text('qw_notify_user_followers_field'));
            
			$minimum_user_point_option = !!qa_post_text('qw_notify_min_points_opt_field');
            if ($minimum_user_point_option) { //if minimum point option is checked 
                  $minimum_user_point_value = qa_post_text('qw_notify_min_points_val_field');
                  if (!!$minimum_user_point_value && is_numeric($minimum_user_point_value) && $minimum_user_point_value > 0) { //if the minimum point value is provided then only set else reset
                        qa_opt('qw_notify_min_points_opt', $minimum_user_point_option);
                        qa_opt('qw_notify_min_points_val', (int) $minimum_user_point_value);
                  } else if (!is_numeric($minimum_user_point_value) || $minimum_user_point_value <= 0) {
                        reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = "The points value should be a numeric and non-zero positive integer ";
                  } else {
                        reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = "The points value is required to enable the option ";
                  }
            } else {
                  reset_all_notification_points_options();
            }
      } else {
            //if none of the elements are selected disable the plugin and send a error message UI 
            qa_opt('qw_email_notf_enable', false);
            reset_all_notification_options();
            $error['no_options_selected'] = "Please choose atleast follower option to enable this plugin ";
      }
      // set the notifications page size 
      $page_size = qa_post_text('qw_all_notification_page_size_field') ;
      if (!$page_size || $page_size < 15 || $page_size > 200 ) {
           $page_size = 15 ; /*15 set to default */
      }
      qa_opt('qw_all_notification_page_size' , $page_size ) ;
      return $error;
}

function options_selected() {
      return ((!!qa_post_text('qw_notify_cat_followers_field')) ||
              (!!qa_post_text('qw_notify_tag_followers_field')) ||
              (!!qa_post_text('qw_notify_user_followers_field')) );
}

function qw_get_notification_count($userid = ""){
      if (!$userid) {
            $userid = qa_get_logged_in_userid();
      }

      return qa_db_read_one_value (qa_db_query_sub(
                        'SELECT count(*) FROM ^ra_userevent WHERE effecteduserid=# AND event NOT IN ("u_wall_post", "u_message") ',
                        $userid ) );
}

function qw_activitylist($limit)
{
            $offset = (int)qa_get('start');
            
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
                        'SELECT id, UNIX_TIMESTAMP(datetime) AS datetime, userid, postid, effecteduserid, event, params, `read` FROM ^ra_userevent WHERE effecteduserid=# AND event NOT IN ("u_wall_post", "u_message") ORDER BY datetime DESC LIMIT # OFFSET #',
                        $userid, $limit , $offset 
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
                        $mark_as_read    = (!$event['read']) ? '<span class="icon icon-tick"></span>' : '';
                        
                        $url_param = array('ra_notification' => $event['id']);
                        $user_link = qa_path_html('user/'.$handle, $url_param, QW_BASE_URL);
                        
                        switch($event['event']){
                              case 'related': // related question to an answer
                                    $url = qa_path_html(qa_q_request($event['postid'], $event['params']['title']), $url_param, QW_BASE_URL,null,null);
                                                      
                                    echo '<div class="event-content clearfix'.$read.''.$read.'"'.$id.'>
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                          $type =     qa_lang_html('dude/question');
                                    elseif($event['params']['parenttype'] == 'A')
                                          $type =     qa_lang_html('dude/answer');
                                    else
                                          $type =     qa_lang_html('dude/comment');
                                          
                                    if(isset($event['params']['parent_uid']) && $event['params']['parent_uid'] != $userid){
                                          $what =     qa_lang_html('dude/followup_comment');
                                          $type =     qa_lang_html('dude/comment');
                                    }else
                                          $what = qa_lang_html('dude/replied_to_your');
                                    
                                    echo '<div class="event-content clearfix'.$read.'"'.$id.'>
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                <div class="avatar"><a href="'.$user_link.'">'.qw_get_avatar($handle, 32, true).'</a></div>
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
                                                                  <span class="what">'.@$content.'</span>
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
                  //code for pagination 
                  
            }else{
                  echo '<div class="no-more-activity">'. qa_lang_html('dude/no_more_activity') .'</div>';
            }
      
}


function qw_get_email_headers($event = "") {
      if (!!$event) {
            switch ($event) {
              case 'a_post':
                        $value = qa_lang("notification/a_post_email_header");
                        break;
              case 'c_post':
                        $value = qa_lang("notification/c_post_email_header");
                        break;
              case 'q_reshow':
                        $value = qa_lang("notification/q_reshow_email_header");
                        break;
              case 'a_reshow':
                        $value = qa_lang("notification/a_reshow_email_header");
                        break;
              case 'c_reshow':
                        $value = qa_lang("notification/c_reshow_email_header");
                        break;
              case 'a_select':
                        $value = qa_lang("notification/a_select_email_header");
                        break;
              case 'q_vote_up':
                        $value = qa_lang("notification/q_vote_up_email_header");
                        break;
              case 'a_vote_up':
                        $value = qa_lang("notification/a_vote_up_email_header");
                        break;
              case 'q_vote_down':
                        $value = qa_lang("notification/q_vote_down_email_header");
                        break;
              case 'a_vote_down':
                        $value = qa_lang("notification/a_vote_down_email_header");
                        break;
              case 'q_vote_nil':
                        $value = qa_lang("notification/q_vote_nil_email_header");
                        break;
              case 'a_vote_nil':
                        $value = qa_lang("notification/a_vote_nil_email_header");
                        break;
              case 'q_approve':
                        $value = qa_lang("notification/q_approve_email_header");
                        break;
              case 'a_approve':
                        $value = qa_lang("notification/a_approve_email_header");
                        break;
              case 'c_approve':
                        $value = qa_lang("notification/c_approve_email_header");
                        break;
              case 'q_reject':
                        $value = qa_lang("notification/q_reject_email_header");
                        break;
              case 'a_reject':
                        $value = qa_lang("notification/a_reject_email_header");
                        break;
              case 'c_reject':
                        $value = qa_lang("notification/c_reject_email_header");
                        break;
              case 'q_favorite':
                        $value = qa_lang("notification/q_favorite_email_header");
                        break;
              case 'q_post':
                        $value = qa_lang("notification/q_post_email_header");
                        break;
              case 'q_post_user_fl':
                        $value = qa_lang("notification/q_post_user_fl_email_header");
                        break;
              case 'q_post_cat_fl':
                        $value = qa_lang("notification/q_post_cat_fl_email_header");
                        break;
              case 'q_post_tag_fl':
                        $value = qa_lang("notification/q_post_tag_fl_email_header");
                        break;

              case 'u_favorite':
                        $value = qa_lang("notification/u_favorite_email_header");
                        break;
              case 'u_message':
                        $value = qa_lang("notification/u_message_email_header");
                        break;
              case 'u_wall_post':
                        $value = qa_lang("notification/u_wall_post_email_header");
                        break;
              case 'u_level':
                        $value = qa_lang("notification/u_level_email_header");
                        break;
              case 'related':
                        $value = qa_lang("notification/related_email_header");
                        break;
              default:
                        break;
            }
            return $value;
      }
}

function qw_get_email_body($event = "") {
      if (!!$event) {
            switch ($event) {
              case 'a_post':
                        $value = qa_lang("notification/a_post_body_email");
                        break;
              case 'c_post':
                        $value = qa_lang("notification/c_post_body_email");
                        break;
              case 'q_reshow':
                        $value = qa_lang("notification/q_reshow_body_email");
                        break;
              case 'a_reshow':
                        $value = qa_lang("notification/a_reshow_body_email");
                        break;
              case 'c_reshow':
                        $value = qa_lang("notification/c_reshow_body_email");
                        break;
              case 'a_select':
                        $value = qa_lang("notification/a_select_body_email");
                        break;
              case 'q_vote_up':
                        $value = qa_lang("notification/q_vote_up_body_email");
                        break;
              case 'a_vote_up':
                        $value = qa_lang("notification/a_vote_up_body_email");
                        break;
              case 'q_vote_down':
                        $value = qa_lang("notification/q_vote_down_body_email");
                        break;
              case 'a_vote_down':
                        $value = qa_lang("notification/a_vote_down_body_email");
                        break;
              case 'q_vote_nil':
                        $value = qa_lang("notification/q_vote_nil_body_email");
                        break;
              case 'a_vote_nil':
                        $value = qa_lang("notification/a_vote_nil_body_email");
                        break;
              case 'q_approve':
                        $value = qa_lang("notification/q_approve_body_email");
                        break;
              case 'a_approve':
                        $value = qa_lang("notification/a_approve_body_email");
                        break;
              case 'c_approve':
                        $value = qa_lang("notification/c_approve_body_email");
                        break;
              case 'q_reject':
                        $value = qa_lang("notification/q_reject_body_email");
                        break;
              case 'a_reject':
                        $value = qa_lang("notification/a_reject_body_email");
                        break;
              case 'c_reject':
                        $value = qa_lang("notification/c_reject_body_email");
                        break;
              case 'q_favorite':
                        $value = qa_lang("notification/q_favorite_body_email");
                        break;
              case 'q_post':
              case 'q_post_user_fl':
              case 'q_post_tag_fl':
              case 'q_post_cat_fl':
                        $value = qa_lang("notification/q_post_body_email");
                        break;
              case 'u_favorite':
                        $value = qa_lang("notification/u_favorite_body_email");
                        break;
              case 'u_message':
                        $body = qa_lang("notification/u_message_body_email");
                        $canreply = !(qa_get_logged_in_flags() & QA_USER_FLAGS_NO_MESSAGES);
                        $more = qa_lang($canreply ? 'notification/u_message_reply_email' : 'notification/u_message_info');
                        return $body . $more;
                        break;
              case 'u_wall_post':
                        $value = qa_lang("notification/u_wall_post_body_email");
                        break;
              case 'u_level':
                        $value = qa_lang("notification/u_level_body_email");
                        break;
              case 'related':
                        $value = qa_lang("notification/related_body_email");
                        break;
              default:
                        break;
            }
            return $value;
      }
}


function qw_get_user_desg($level) {
      switch ($level) {
            case QA_USER_LEVEL_BASIC :
                  $value = qa_lang("notification/basic_desg");
                  break;
            case QA_USER_LEVEL_APPROVED :
                  $value = qa_lang("notification/approved_desg");
                  break;
            case QA_USER_LEVEL_EXPERT :
                  $value = qa_lang("notification/expert_desg");
                  break;
            case QA_USER_LEVEL_EDITOR :
                  $value = qa_lang("notification/editor_desg");
                  break;
            case QA_USER_LEVEL_MODERATOR :
                  $value = qa_lang("notification/moderator_desg");
                  break;
            case QA_USER_LEVEL_ADMIN :
                  $value = qa_lang("notification/admin_desg");
                  break;
            case QA_USER_LEVEL_SUPER :
                  $value = qa_lang("notification/super_admin_desg");
                  break;
            default:
                  break;
      }
      return $value;
}

function qw_shrink_email_body($email_body, $max_body_length = 50) {
      if (!!$email_body) {
            $email_body = substr($email_body, 0, $max_body_length);
            $email_body .= "....";
      }
      return $email_body;
}

