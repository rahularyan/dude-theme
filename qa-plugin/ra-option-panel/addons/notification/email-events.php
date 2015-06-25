<?php
/* don't allow this page to be requested directly from browser */ 
if (!defined('QA_VERSION')) {
            header('Location: /');
            exit;
}

//define the event hook event handlers 
qw_add_action('user_event_a_post','qw_notification_event');
qw_add_action('user_event_c_post','qw_notification_event');
qw_add_action('user_event_q_reshow','qw_notification_event');
qw_add_action('user_event_a_reshow','qw_notification_event');
qw_add_action('user_event_c_reshow','qw_notification_event');
qw_add_action('user_event_a_select','qw_notification_event');
qw_add_action('user_event_q_vote_up','qw_notification_event');
qw_add_action('user_event_a_vote_up','qw_notification_event');
qw_add_action('user_event_q_vote_down','qw_notification_event');
qw_add_action('user_event_a_vote_down','qw_notification_event');
qw_add_action('user_event_q_vote_nil','qw_notification_event');
qw_add_action('user_event_a_vote_nil','qw_notification_event');
qw_add_action('user_event_q_approve','qw_notification_event');
qw_add_action('user_event_a_approve','qw_notification_event');
qw_add_action('user_event_c_approve','qw_notification_event');
qw_add_action('user_event_q_reject','qw_notification_event');
qw_add_action('user_event_a_reject','qw_notification_event');
qw_add_action('user_event_c_reject','qw_notification_event');
qw_add_action('user_event_q_favorite','qw_notification_event');
qw_add_action('user_event_q_post','qw_notification_event');
qw_add_action('user_event_u_favorite','qw_notification_event');
qw_add_action('user_event_u_message','qw_notification_event');
qw_add_action('user_event_u_wall_post','qw_notification_event');
qw_add_action('user_event_u_level','qw_notification_event');
//added for related questions 
qw_add_action('user_event_related','qw_notification_event');
qw_add_action('user_event_q_post_user_fl','qw_notification_event');
qw_add_action('user_event_q_post_tag_fl','qw_notification_event');
qw_add_action('user_event_q_post_cat_fl','qw_notification_event');

function qw_notification_event($postid,$userid, $effecteduserid, $params, $event) {	
      $loggeduserid   = isset($userid) ? $userid : qa_get_logged_in_userid();
      if (!!$effecteduserid) {
            qw_notify_users_by_email($event, $postid, $loggeduserid, $effecteduserid, $params);
      }
}

function qw_process_emails_from_db() {
      require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
      require_once QA_INCLUDE_DIR . 'qa-util-string.php';
      //here extract all the email contents from database and perform the email sending operation 
      $email_queue_data    = qw_get_email_queue();
      $email_list          = qw_get_email_list($email_queue_data);
      $subs                = array();
      $subs['^site_title'] = qa_opt('site_title');
      $greeting            = qa_lang("notification/greeting");
      $thank_you_message   = qa_lang("notification/thank_you_message");
      $subject             = strtr(qa_lang("notification/notification_email_subject"), $subs);
      $processed_queue_ids = array() ; 

      foreach ($email_list as $email_data) {
            $b                  = "" ; /*reset the body content */
            $email              = $email_data['email'];
            $name               = $email_data['name'];
            $handle             = $email_data['handle'];
            $created_by         = $email_data['created_by'];
            $subs['^user_name'] = $name;
            $email_body         = qw_prepare_email_body($email_queue_data, $email);
            $email_body         = $email_body . $thank_you_message;
			
		$b .='<div class="content user-greet" >
				<table><tr><td>
					<strong>'.$greeting.'</strong>
					<i>'.$subject.'</i>
				</td></tr></table>
			</div>
			';
		$b .='<div class="content email-body"><table><tr><td>';
		$b .= strtr($email_body, $subs);
		$b .='</td></tr></table></div>';
            
			$notification_sent  = qw_send_email_notification(null, $email, $handle, $created_by, $name, $subject, $b, $subs);
            if (!!$notification_sent) {
                  // if the notification is sent then 
                  $processed_queue_ids = array_merge($processed_queue_ids , qw_get_queue_ids_from_queue_data($email_queue_data, $email) ) ;
            }
            
      }
      if (!empty($processed_queue_ids)) {
            //update the queue status 
            $processed_queue_ids = array_unique($processed_queue_ids) ;
            qw_update_email_queue_status($processed_queue_ids);
      }

}

function qw_get_queue_ids_from_queue_data($email_queue_data, $email) {
      $queue_ids = array();
      if (!!$email_queue_data && is_array($email_queue_data) && !!$email) {
            foreach ($email_queue_data as $email_queue) {
                  if (isset($email_queue['email'])) {
                        if ($email_queue['email'] === $email) {
                              $queue_ids[] = $email_queue['queue_id'];
                        }
                  }
            }
      }
      return $queue_ids;
}

function qw_prepare_email_body($email_queue_data, $email) {
     
      $email_body_arr = array();
      $summerized_email_body = array();
      $email_body = "";

      if (is_array($email_queue_data)) {
            foreach ($email_queue_data as $queue_data) {
                  if ($queue_data['email'] === $email) {
                        $event = $queue_data['event'];
                        $body_subs = json_decode($queue_data['body'], true) ;
                        $body_subs['^author_link'] = qa_path_absolute('user/'.$body_subs['^done_by']);
                        $body = strtr(qw_get_email_body($event) , $body_subs );
                        
                        if (!!$body) {
                              $header = "" ;
                              if (!isset($email_body_arr[$event]['body']) || empty($email_body_arr[$event]['body'])){
                                    // Now attach the headers 
                                    $header  ='<div class="content event-item"><table><tr>'; 
                                    $header .='<td style="vertical-align: top;">';
                                    $header .= qw_get_email_headers($event);
                                    $header .='</td>';
                                    $header .='</tr></table></div>';
                              }
                              
                              $email_body_arr[$event]['body'] = (isset($email_body_arr[$event]['body']) && !empty($email_body_arr[$event]['body']) ) ? $email_body_arr[$event]['body'] . "" : $header ;
                              $event_body  ='<div class="content event-item"><table><tr>'; 
                              $event_body .='<td class="small" width="60px" style="vertical-align: top; padding-right:10px;">'.qw_get_avatar($body_subs['^done_by'], 40).'</td>';
                              $event_body .='<td style="vertical-align: top;">';
                              $event_body .=  $body; 
                              $event_body .='</td>';
                              $event_body .='</tr></table></div>';
                              $email_body_arr[$event]['body'] .= $event_body ;
                        }
                  } //outer if 
            } //foreach
		
            foreach ($email_body_arr as $event => $email_body_for_event) {
                        if (isset($email_body_for_event['body']) && !empty($email_body_for_event['body'])) {
                              if (!isset($summerized_email_body[$event]) || empty($summerized_email_body[$event])) {
                                    $summerized_email_body[$event] = $email_body_for_event['body'] ;
                              }else {
                                    $summerized_email_body[$event] .= $email_body_for_event['body'] ;
                              }
                        }
            }//foreach 
			
            foreach ($summerized_email_body as $event => $email_body_chunk) {
                  if (!!$email_body_chunk) {
                        $email_body .= $email_body_chunk;						
                  }
            }//foreach 			
			
      } //if 
      return $email_body;
}

function qw_get_email_list($email_queue_data) {
      $email_list = array();
      $unique_email_list = array();
      if (is_array($email_queue_data)) {
            foreach ($email_queue_data as $queue_data) {
                  if (isset($queue_data['email']) && !empty($queue_data['email'])) {
                        $email = $queue_data['email'];
                        if (!in_array($email, $unique_email_list)) {
                              $unique_email_list[] = $email;
                              $data = array('email' => $email);
                              
							  if (!empty($queue_data['name'])) 
                                    $data['name'] = $queue_data['name'];
									
							 if (!empty($queue_data['handle'])) 
                                    $data['handle'] = $queue_data['handle'];
							
							if (!empty($queue_data['created_by'])) 
                                    $data['created_by'] = $queue_data['created_by'];
                              
                              $email_list[] = $data;
                        }
                  }
            }
      }
      return $email_list;
}

function qw_get_email_queue() {
      return qa_db_read_all_assoc(qa_db_query_sub("SELECT * from ^ra_email_queue queue join ^ra_email_queue_receiver rcv on queue.id = rcv.queue_id WHERE queue.status = 0 "));
}

function qw_get_name_from_userid($userid) {
      return qa_db_read_one_value(qa_db_query_sub("SELECT ^userprofile.content AS name from  ^userprofile WHERE ^userprofile.title = 'name' AND ^userprofile.userid =# ", $userid), true);
}

function qw_get_user_details_from_userid($userid) {
      return qa_db_read_one_assoc(qa_db_query_sub("SELECT ^users.email AS email , ^users.handle AS handle from ^users WHERE ^users.userid = #", $userid), true);
}

function qw_update_email_queue_status($queue_ids) {
      return qa_db_query_sub("UPDATE ^ra_email_queue SET status = '1', sent_on = CURRENT_TIMESTAMP() WHERE ^ra_email_queue.id IN (#)", $queue_ids);
}
      
function qw_notify_users_by_email($event, $postid, $userid, $effecteduserid, $params) {
      if (!!$effecteduserid) {
            //get the working user data  
            $logged_in_handle    = qa_get_logged_in_handle();
            $logged_in_user_name = qw_get_name_from_userid($userid);
            $logged_in_user_name = (!!$logged_in_user_name) ? $logged_in_user_name : $logged_in_handle;

            $name = qw_get_name_from_userid($effecteduserid);

            switch ($event) {

                  case 'a_post':
                  case 'related':
                        $parent = isset($params['parent']) ? $params['parent'] : "";
                        if (!!$parent) {
                              $name  = (!!$name) ? $name : $parent['handle'];
                              $email = $parent['email'];
                              $handle= $parent['handle'] ;
                        } else {
                              //seems proper values are not available 
                              return;
                        }
                        break;
                  case 'c_post':
                  case 'q_reshow':
                  case 'a_reshow':
                  case 'c_reshow':
                  case 'a_select':
                  case 'q_vote_up':
                  case 'q_vote_down':
                  case 'a_vote_up':
                  case 'a_vote_down':
                  case 'q_favorite':
                  case 'u_favorite':
                  case 'u_message':
                  case 'u_wall_post':
                  case 'u_level':
                  case 'q_post_user_fl':
                  case 'q_post_tag_fl':
                  case 'q_post_cat_fl':
                        //this is because we wont have the $parent['email'] for each effected userids when a these selected events occurs 
                        $user_details = qw_get_user_details_from_userid($effecteduserid);
                        $handle       = $user_details['handle'];
                        $name         = (!!$name) ? $name : $user_details['handle'];
                        $email        = $user_details['email'];
                        break;
                  case 'q_approve':
                  case 'q_reject':
                        $oldquestion = $params['oldquestion'];
				$handle      = $oldquestion['handle'];
                        $name        = (!!$name) ? $name : $oldquestion['handle'];
                        $email       = $oldquestion['email'];
                        break;
                  case 'a_approve':
                  case 'a_reject':
                        $oldanswer = $params['oldanswer'];
				$handle    = $oldquestion['handle'];
                        $name      = (!!$name) ? $name : $oldanswer['handle'];
                        $email     = $oldanswer['email'];
                        break;
                  case 'c_approve':
                  case 'c_reject':
                        $oldcomment = $params['oldcomment'];
				$handle      = $oldcomment['handle'];
                        $name       = (!!$name) ? $name : $oldcomment['handle'];
                        $email      = $oldcomment['email'];
                        break;
                  default:
                        break;
            }
			
		include_once QA_INCLUDE_DIR.'qa-util-string.php';
			
            $notifying_user['userid'] = $effecteduserid;
            $notifying_user['name']   = $name;
            $notifying_user['email']  = $email;
            $notifying_user['handle'] = isset($handle) ? $handle : qa_lang('main/anonymous');
            //consider only first 50 characters for saving notification 
            if ($event === 'u_message') {
                  $content  = (isset($params['message']) && !empty($params['message'])) ? $params['message'] : "";
                  $title    = "";
                  $canreply = !(qa_get_logged_in_flags() & QA_USER_FLAGS_NO_MESSAGES);
                  $url      = qa_path_absolute($canreply ? ('message/' . $logged_in_handle) : ('user/' . $logged_in_handle));
            } else if ($event === 'u_wall_post') {
                  $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
                  if (!!$content) {
                        $blockwordspreg = qa_get_block_words_preg();
                        $content        = qa_block_words_replace($content, $blockwordspreg);
                  }
                  $title = "";
                  $url   = qa_path_absolute('user/' . $params['handle'] . '/wall', null, null);
            } else if ($event === 'u_level') {
                  $title     = "";
                  $url       = qa_path_absolute('user/' . $params['handle']);
                  $old_level = $params['oldlevel'];
                  $new_level = $params['level'];
                  if ($new_level < $old_level) {
                        return ; 
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

                  $content = strtr(qa_lang($approved_only ? 'notification/u_level_approved_body_email' : 'notification/u_level_improved_body_email'), array(
                      '^done_by'         => isset($logged_in_user_name) ? $logged_in_user_name : isset($logged_in_handle) ? $logged_in_handle : qa_lang('main/anonymous'),
                      '^new_designation' => @$new_designation,
                  )); 
            } else if($event === "q_post_user_fl" || $event === "q_post_tag_fl" || $event === "q_post_cat_fl" ){
                  $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
                   //shrink the email body content 
                  if (!!$content && (strlen($content) > 50)) $content = qw_shrink_email_body($content, 50);

                  $title = (isset($params['title']) && !empty($params['title'])) ? $params['title'] : "";
                  $url = qa_q_path($params['postid'], $title , true);
                  
            } else {
                  $content = (isset($params['text']) && !empty($params['text'])) ? $params['text'] : "";
                   //shrink the email body content 
                  if (!!$content && (strlen($content) > 50)) $content = qw_shrink_email_body($content, 50);
                  
                  $title = (isset($params['qtitle']) && !empty($params['qtitle'])) ? $params['qtitle'] : "";
                  $url = qa_q_path($params['qid'], $title , true);
            }
            $q_handle = isset($logged_in_user_name) ? $logged_in_user_name : isset($logged_in_handle) ? $logged_in_handle : qa_lang('main/anonymous') ;
            qw_save_email_notification(null, $notifying_user, $logged_in_handle, $event, array(
                '^q_handle'    => $q_handle,
                '^q_title'     => $title,
                '^q_content'   => $content,
                '^url'         => (!!$url) ? $url : "",
                '^done_by'     => $q_handle,
                '^author_link' => qa_path_absolute('user/'.$q_handle),
                '^author_pic' => qa_path_absolute('user/'.$q_handle),
                '^handle'      => $handle,
                    )
            );
      }
}

function qw_save_email_notification($bcclist, $notifying_user, $handle, $event, $subs) {
      require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
      require_once QA_INCLUDE_DIR . 'qa-util-string.php';
      $handle = isset($handle) ? $handle : qa_lang('main/anonymous') ;
      $body   = qw_get_email_body($event);
      $id = qw_dump_email_content_to_db(array(
          'event' => $event,
          'body'  => json_encode($subs),
          'by'    => $handle,
      ));
      qw_dump_email_to_db($notifying_user, $id);
}

function qw_dump_email_content_to_db($param) {
      qa_db_query_sub(
              'INSERT INTO ^ra_email_queue (event, body , created_by ) ' .
              'VALUES ($, $ , $ )', $param['event'], $param['body'], $param['by']
      );

      return qa_db_last_insert_id();
}

function qw_dump_email_to_db($notifying_user, $queue_id) {
      qa_db_query_sub(
              'INSERT INTO ^ra_email_queue_receiver (userid, handle, email , name , queue_id ) ' .
              'VALUES (#, $, $ , $ , # )', $notifying_user['userid'], $notifying_user['handle'], $notifying_user['email'], $notifying_user['name'], $queue_id
      );

      return qa_db_last_insert_id();
}

function qw_send_email_notification($bcclist, $email, $handle, $created_by , $name, $subject, $body, $subs) {

      global $qa_notifications_suspended;

      if ($qa_notifications_suspended > 0) return false;

      require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
      require_once QA_INCLUDE_DIR . 'qa-util-string.php';

      $subs['^site_title'] = qa_opt('site_title');
      $subs['^handle']     = $handle;
      $subs['^open']       = "\n";
      $subs['^close']      = "\n";

      $email_param = array(
          'fromemail' => qa_opt('from_email'),
          'fromname' => qa_opt('site_title'),
          'mail_list' => $email,
          'toname' => $name,
          'handle' => $handle,
          'created_by' => $created_by,
          'bcclist' => $bcclist,
          'subject' => strtr($subject, $subs),
          'body' => strtr($body, $subs),
          'html' => true ,
      );
	 
	 $email_param['body'] = qw_get_email_template($email_param);
	 
      if (QW_SEND_EMAIL_DEBUG_MODE) {
            //this will write to the log file 
            return qw_send_email_fake($email_param);
      }
      return qw_send_email($email_param);
}

function qw_send_email($params) {
      require_once QA_INCLUDE_DIR . 'qa-class.phpmailer.php';
      $mailer           = new PHPMailer();
      $mailer->CharSet  = 'utf-8';
      $mailer->From     = $params['fromemail'];
      $mailer->Sender   = $params['fromemail'];
      $mailer->FromName = $params['fromname'];
      if (isset($params['mail_list'])) {
            if (is_array($params['mail_list'])) {
                  foreach ($params['mail_list'] as $email) {
                        $mailer->AddAddress($email['toemail'], $email['toname']);
                  }
            } else {
                  $mailer->AddAddress($params['mail_list'], $params['toname']);
            }
      }
      $mailer->Subject = $params['subject'];
      $mailer->Body    = $params['body'];
      if (isset($params['bcclist'])) {
            foreach ($params['bcclist'] as $email) {
                  $mailer->AddBCC($email);
            }
      }

      if ($params['html']) $mailer->IsHTML(true);

      if (qa_opt('smtp_active')) {
            $mailer->IsSMTP();
            $mailer->Host = qa_opt('smtp_address');
            $mailer->Port = qa_opt('smtp_port');

            if (qa_opt('smtp_secure')) $mailer->SMTPSecure = qa_opt('smtp_secure');

            if (qa_opt('smtp_authenticate')) {
                  $mailer->SMTPAuth = true;
                  $mailer->Username = qa_opt('smtp_username');
                  $mailer->Password = qa_opt('smtp_password');
            }
      } else {
            //smtp is not active 
      }
      return $mailer->Send();
}

function qw_send_email_fake($email_param) {
      qw_log(print_r($email_param['body'], true));
      //fake email should never fail 
      return true  ;
}

function qw_get_email_template($parms){
	$logo = qa_opt('logo_url');
	
	$subs = array(
		'{handle}' => $parms['handle'],
		'{base_url}' => get_base_url(),
		'{site_title}' => qa_opt('site_title'),
		'{logo}' => (!!$logo ? '<img class="navbar-site-logo" src="' . $logo . '">' : '<img class="navbar-site-logo" src="' . Q_THEME_URL . '/images/logo.png">'),
		'{avatar}' => ra_get_avatar($parms['handle'], 40),
            '{body}' => $parms['body'],
	);
	$email_body  = '';
	$email_body  = qa_get_email_template_head($parms, $subs);
	$email_body .= qa_get_email_template_body($parms, $subs);
	$email_body .= qa_get_email_template_footer($parms, $subs);
	return $email_body;
}


function qa_get_email_template_head($parms, $subs){
	ob_start();
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html>
			<head>
			<!-- If you delete this meta tag, the ground will open and swallow you. -->
			<meta name="viewport" content="width=device-width" />

			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<title><?php echo $parms['subject']; ?></title>
			
			</head>
			<body bgcolor="#f6f6f6" style="background:#ddd;">
			
			<?php 
				$email_head = qa_opt('qw_email_head');
				if(!!$email_head){
					echo $email_head;
				}else{
				?>
				<!-- HEADER -->
				<table class="head-wrap" style="max-width: 600px; width: 600px; margin: 0 auto;">
					<tr>
						<td></td>
						<td class="header container" align="">
							
							<!-- /content -->
							<div class="content">
								<table style="max-width: 600px; width: 600px;margin: 0 auto;">
									<tr >
										<td>
											<a title="{site_title}" href="{base_url}">{logo}</a>				
										</td>
										<td align="right">{avatar}</td>
									</tr>
								</table>
							</div><!-- /content -->
							
						</td>
						<td></td>
					</tr>
				</table><!-- /HEADER -->
			<?php
			}
	return strtr(ob_get_clean(), $subs);
}

function qa_get_email_template_body($parms, $subs){
	ob_start();
            $email_body = qa_opt('qw_email_body');
            if(!!$email_body){
                  echo $email_body;
            }else{  /*print the default template */
            ?>

			<!-- body -->
			<table class="body-wrap" style="background:#fff; border:solid 1px #ddd; max-width: 600px; width: 600px; margin: 0 auto;">
				<tr>
					<td></td>
					<td class="container" bgcolor="#FFFFFF">

						<!-- content -->
						<div class="content">
						<table>
							<tr>
								<td>
									{body}
								</td>
							</tr>
						</table>
						</div>
						<!-- /content -->
						
					</td>
					<td></td>
				</tr>
			</table>
			<!-- /body -->
	     <?php
            }
	return strtr(ob_get_clean(), $subs);
}

function qa_get_email_template_footer($parms, $subs){
	ob_start();
            $email_footer = qa_opt('qw_email_footer');
            if(!!$email_footer){
                  echo $email_footer;
            }else{  /*print the default template */
            ?>
			<!-- footer -->
			<table class="footer-wrap" style="background:#fff; border:solid 1px #ddd; max-width: 600px; width: 600px; margin: 0 auto;">
				<tr>
					<td></td>
					<td class="container">
						
						<!-- content -->
						<div class="content">
							<table>
								<tr>
									<td align="center">
										<p>Don't like these annoying emails? <a href="#"><unsubscribe>Unsubscribe</unsubscribe></a>.
										</p>
									</td>
								</tr>
							</table>
						</div>
						<!-- /content -->
						
					</td>
					<td></td>
				</tr>
			</table>
			<!-- /footer -->

			
		<?php
            } /*end of else */
            ?>
            </body> <!-- CLOSE BODY TAG -->
      </html><!-- CLOSE HTML TAG -->
            <?php
	return strtr(ob_get_clean(), $subs);
}
function qw_send_notification($userid, $email, $handle, $subject, $body, $subs){
	
	global $qa_notifications_suspended;
	
	if ($qa_notifications_suspended>0)
		return false;
	
	require_once QA_INCLUDE_DIR.'qa-db-selects.php';
	require_once QA_INCLUDE_DIR.'qa-util-string.php';
	
	if (isset($userid)) {
		$needemail=!qa_email_validate(@$email); // take from user if invalid, e.g. @ used in practice
		$needhandle=empty($handle);
		
		if ($needemail || $needhandle) {
			if (QA_FINAL_EXTERNAL_USERS) {
				if ($needhandle) {
					$handles=qa_get_public_from_userids(array($userid));
					$handle=@$handles[$userid];
				}
				
				if ($needemail)
					$email=qa_get_user_email($userid);
			
			} else {
				$useraccount=qa_db_select_with_pending(
					qa_db_user_account_selectspec($userid, true)
				);
				
				if ($needhandle)
					$handle=@$useraccount['handle'];

				if ($needemail)
					$email=@$useraccount['email'];
			}
		}
	}
		
	if (isset($email) && qa_email_validate($email)) {
		$subs['^site_title']=qa_opt('site_title');
		$subs['^handle']=$handle;
		$subs['^email']=$email;
		$subs['^open']="\n";
		$subs['^close']="\n";
	
		$email_param  = array(
			'fromemail' => qa_opt('from_email'),
			'fromname' => qa_opt('site_title'),
			'toemail' => $email,
			'toname' => $handle,
			'handle' => $handle,
			'subject' => strtr($subject, $subs),
			'body' => (empty($handle) ? '' : qa_lang_sub('emails/to_handle_prefix', $handle)).strtr($body, $subs),
			'html' => true,
		);

		$email_param['body'] = qw_get_email_template($email_param);
		if (QW_SEND_EMAIL_DEBUG_MODE) {
				//this will write to the log file 
				return qw_send_email_fake($email_param);
		  }
		return qa_send_email($email_param);
	} else
		return false;
}