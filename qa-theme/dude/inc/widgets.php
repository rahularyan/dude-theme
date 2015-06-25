<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Widgets of the theme */
	
	
/* Ask box widget */

function ra_ask_form(){
	if (isset($qa_content['categoryids']))
		$params=array('cat' => end($qa_content['categoryids']));
	else
		$params=null;
	?>
	<div class="ask-block">
		<form method="post" action="<?php echo qa_path_html('ask', $params); ?>">
			<div class="ask-button pull-right">
				<input type="submit" class="btn btn-primary" value="<?php echo _ra_lang('Ask'); ?>">
			</div>
			<div class="ask-input">
				<input name="title" type="text" class="from-control" placeholder="<?php echo _ra_lang('Start typing your question here'); ?>">
			</div>
			
			<input type="hidden" name="doask1" value="1">
		</form>
	</div>
	<?php
}

/* top users widget */
function ra_top_users($limit = 5, $size){
	$users = qa_db_select_with_pending(qa_db_top_users_selectspec(qa_get_start()));
	
	$output = '<ul class="top-users-list clearfix">';
	$i = 1;
	foreach($users as $u){
		if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
			require_once QA_INCLUDE_DIR.'qa-app-posts.php';
			$u['handle'] = qa_post_userid_to_handle($u['userid']);
		}
		
		$output .= '<li class="top-user">';
		$output .= '<div class="avatar pull-left" data-handle="'.$u['handle'].'" data-id="'. qa_handle_to_userid($u['handle']).'">';
		$output .= ra_get_avatar($u['handle'], $size).'</div>';
		$output .= '<div class="top-user-data">';
		
		$output .= '<a href="'.qa_path_html('user/'.$u['handle']).'" class="name">'.ra_name($u['handle']).'</a>';
		
		//$output .= ra_user_badge($u['handle']);	
		$output .= '<dl class="points">'.$u['points'].' '._ra_lang('Points').'</dl>';		
		$output .= '</div>';
		$output .= '</li>';
		if($i==$limit)break;
		$i++;
	
	}
	$output .= '</ul>';
	echo $output;
}

/* RA events widget */

// custom function to get all events and new events
function getAllForumEvents($queryRecentEvents, $eventsToShow, $limit) {
	/* 
		Question2Answer Plugin: Recent Events Widget
		Author: http://www.echteinfach.tv/ 
	*/
	$listAllEvents = '';
	$countEvents = 0;
	$listAllEvents .= '<ul class="ra-events">';
	while ( ($row = qa_db_read_one_assoc($queryRecentEvents,true)) !== null ) {
		if(in_array($row['event'], $eventsToShow)) {
			// question title
			$qTitle = '';			
			// workaround: convert tab jumps to & to be able to use query function
			$toURL = str_replace("\t","&",$row['params']);
			// echo $toURL."<br />"; // we get e.g. parentid=4523&parent=array(65)&postid=4524&answer=array(40)
			parse_str($toURL, $data);  // parse URL to associative array $data
			// now we can access the following variables in array $data if they exist in toURL
			
			$linkToPost = "-";
			
			// find out type, if Q set link directly, if A or C do query to get correct link
			$postid = (isset($data['postid'])) ? $data['postid'] : null;
			if($postid !== null) {
				$getPostType = mysql_fetch_array( qa_db_query_sub("SELECT type,parentid FROM `^posts` WHERE `postid` = #", $postid) );
				$postType = $getPostType[0]; // type, and $getPostType[1] is parentid
				if($postType=="A") {
					$getQtitle = mysql_fetch_array( qa_db_query_sub("SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1", $getPostType[1]) );
					$qTitle = (isset($getQtitle[0])) ? $getQtitle[0] : "";
					// get correct public URL
					$activity_url = qa_path_html(qa_q_request($getPostType[1], $qTitle), null, qa_opt('site_url'), null, null);
					$linkToPost = $activity_url."?show=".$postid."#a".$postid;
				}
				else if($postType=="C") {
					// get question link from answer
					$getQlink = mysql_fetch_array( qa_db_query_sub("SELECT parentid,type FROM `^posts` WHERE `postid` = # LIMIT 1", $getPostType[1]) );
					$linkToQuestion = $getQlink[0];
					if($getQlink[1]=="A") {
						$getQtitle = mysql_fetch_array( qa_db_query_sub("SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1", $getQlink[0]) );
						$qTitle = (isset($getQtitle[0])) ? $getQtitle[0] : "";
						// get correct public URL
						$activity_url = qa_path_html(qa_q_request($linkToQuestion, $qTitle), null, qa_opt('site_url'), null, null);
						$linkToPost = $activity_url."?show=".$postid."#c".$postid;
					}
					else {
						// default: comment on question
						$getQtitle = mysql_fetch_array( qa_db_query_sub("SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1", $getPostType[1]) );
						$qTitle = (isset($getQtitle[0])) ? $getQtitle[0] : "";
						// get correct public URL
						$activity_url = qa_path_html(qa_q_request($getPostType[1], $qTitle), null, qa_opt('site_url'), null, null);
						$linkToPost = $activity_url."?show=".$postid."#c".$postid;
					}
				}
				// if question is hidden, do not show frontend!
				else if($postType=="Q_HIDDEN") {
					$qTitle = '';
				}
				else {
					// question has correct postid to link
					// $questionTitle = (isset($data['title'])) ? $data['title'] : "";
					$getQtitle = mysql_fetch_array( qa_db_query_sub("SELECT title FROM `^posts` WHERE `postid` = # LIMIT 1", $postid) );
					$qTitle = (isset($getQtitle[0])) ? $getQtitle[0] : "";
					// get correct public URL
					// $activity_url = qa_path_html(qa_q_request($getPostType[1], $qTitle), null, qa_opt('site_url'), null, null);
					$activity_url = qa_path_html(qa_q_request($postid, $qTitle), null, qa_opt('site_url'), null, null);
					$linkToPost = $activity_url;
				}
			}elseif($row['event'] == 'badge_awarded'){
				$toURL = str_replace("\t","&",$row['params']);			
				parse_str($toURL, $data);
				
				$badge = qa_get_badge_type_by_slug($data['badge_slug']);
				$badge_type = $badge['slug'];
				$badge_name = qa_opt('badge_'.$data['badge_slug'].'_name');
				$var = qa_opt('badge_'.$data['badge_slug'].'_var');
				$qTitle =  $badge_name.' - '.qa_badge_desc_replace($data['badge_slug'],$var,false);
				$linkToPost = qa_path_html('user/'.$row['handle']);
			}
			
			$username = (is_null($row['handle'])) ? _ra_lang('Anonymous') : htmlspecialchars($row['handle']);
			$usernameLink = (is_null($row['handle'])) ? _ra_lang('Anonymous') : '<a target="_blank" class="qa-user-link" href="'.qa_opt('site_url').'user/'.$row['handle'].'">'.ra_name($row['handle']).'</a>';
			
			// set event name and css class
			$eventName = '';
			if($row['event']=="q_post") {
				$eventName = _ra_lang('asked');
			}
			else if($row['event']=="a_post") {
				$eventName = _ra_lang('answered');
			}
			else if($row['event']=="c_post") {
				$eventName = _ra_lang('commented');	
			}
			else if($row['event']=="a_select") {
				$eventName = _ra_lang('selected an answer');
			}				
			else if($row['event']=="badge_awarded") {
				$eventName = _ra_lang('earned a badge');
			}			
			
			// set event icon class
			
			if($row['event']=="q_post") {
				$event_icon = 'icon-question question';
			}
			else if($row['event']=="a_post") {
				$event_icon = 'icon-chat3 ans';
			}
			else if($row['event']=="c_post") {
				$event_icon = 'icon-chat2 comment';
			}
			else if($row['event']=="a_select") {
				$event_icon = 'icon-checkmark selected';
			}
			else if($row['event']=="badge_awarded") {
				$event_icon = 'icon-badge badge-icon '.@$badge_type;
			}

			$timeCode = implode(' ', qa_when_to_html($row['unix_time'], qa_opt('show_full_date_days')));
			$time = '<span class="time">'.$timeCode.'</span>';
			
			// if question title is empty, question got possibly deleted, do not show frontend!
			if($qTitle=='') {
				continue;
			}

			$qTitleShort = mb_substr($qTitle,0,22,'utf-8'); // shorten question title to 22 chars
			$qTitleShort2 = (strlen($qTitle)>50) ? htmlspecialchars(mb_substr($qTitle,0,50,'utf-8')) .'&hellip;' : htmlspecialchars($qTitle); // shorten question title			

			$listAllEvents .= '<li class="event-item">';
			$listAllEvents .= '<div class="event-icon pull-left '.$event_icon.'"></div>';
			$listAllEvents .= '<div class="event-inner">';	
			
				$listAllEvents .= '<div class="avatar pull-left" data-handle="'.@$row['handle'].'" data-id="'. qa_handle_to_userid($row['handle']).'">'.ra_get_avatar(@$row['handle'], 40).'</div>';
				
			$listAllEvents .= '<div class="event-content">';			
			$listAllEvents .= '<h4>'.$usernameLink.' '.$eventName.' '.$time.'</h4>';			
			
			if($row['event']=="badge_awarded")
				$listAllEvents .= '<h5 class="event-title">'.$qTitleShort2.'</h5>';						
			else
				$listAllEvents .= '<a class="event-title" href="'.$linkToPost.'">'.$qTitleShort2.'</a>';
			
			$listAllEvents .= '</div>';	
			$listAllEvents .= '</div>';	
			$listAllEvents .= '</li>';
			$countEvents++;
			if($countEvents>=$limit) {
				break;
			}
		}
	}
	$listAllEvents .= '</ul>';
	return $listAllEvents;
} 

function ra_user_list($limit, $size){
	$output = '<ul class="users-list clearfix">';
	if (defined('QA_FINAL_WORDPRESS_INTEGRATE_PATH')){
		global $wpdb;
		$users = $wpdb->get_results("SELECT ID from $wpdb->users order by ID DESC LIMIT $limit");
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		
		foreach($users as $u){
			$handle = qa_post_userid_to_handle($u->ID);
			$output .= '<li class="user">';
			$output .= '<div class="avatar" data-handle="'.$handle.'" data-id="'. qa_handle_to_userid($handle).'">'.ra_get_avatar($handle, $size).'</div>';
			$output .= '</li>';
		}
		
	}else{
		$users = qa_db_query_sub('SELECT * FROM ^users ORDER BY created DESC LIMIT #', $limit);	
		while($u = mysql_fetch_array($users)){
			$output .= '<li class="user">';
			$output .= '<div class="avatar" data-handle="'.$u['handle'].'" data-id="'. qa_handle_to_userid($u['handle']).'">'.ra_get_avatar($u['handle'], $size).'</div>';
			$output .= '</li>';
		}
	}
	$output .= '</ul>';
	echo $output;
}

function ra_events($limit =10){
	/* 
		Question2Answer Plugin: Recent Events Widget
		Author: http://www.echteinfach.tv/ 
	*/
	
	$eventsToShow = array('q_post', 'a_post', 'c_post', 'a_select', 'badge_awarded');
	
	// query last 3 events
	$queryRecentEvents = qa_db_query_sub("SELECT UNIX_TIMESTAMP(datetime) as unix_time, datetime ,ipaddress,handle,event,params 
								FROM `^eventlog`
								WHERE `event`='q_post' OR `event`='a_post' OR `event`='c_post' OR `event`='a_select' OR `event`='badge_awarded'
								ORDER BY datetime DESC
								LIMIT $limit"); // check with getAllForumEvents() which returns events as links

	$recentEvents = '';

	echo getAllForumEvents($queryRecentEvents, $eventsToShow, $limit);
}

// output the list of selected post type
function ra_post_list($type, $limit){
	require_once QA_INCLUDE_DIR.'qa-app-posts.php';
	$post = qa_db_query_sub('SELECT * FROM ^posts WHERE ^posts.type=$ ORDER BY ^posts.created DESC LIMIT #', $type, $limit);	
	
	$output = '<ul class="question-list">';
	while($p = mysql_fetch_array($post)){

		$p['title'] = qa_block_words_replace($p['title'], qa_get_block_words_preg(), '*');
		$p['content'] = qa_block_words_replace($p['content'], qa_get_block_words_preg(), '*');
		
		if($type=='Q'){
			$what = _ra_lang('asked');
		}elseif($type=='A'){
			$what = _ra_lang('answered');
		}elseif('C'){
			$what = _ra_lang('commented');
		}
		
		$handle = qa_post_userid_to_handle($p['userid']);

		$output .= '<li id="q-list-'.$p['postid'].'" class="question-item">';
		$output .= '<div class="pull-left avatar" data-handle="'.$handle.'" data-id="'. qa_handle_to_userid($handle).'">'.ra_get_avatar($handle, 40).'</div>';
		$output .= '<div class="list-right">';
		
		$output .= '<h5><a href="'.qa_path_html('user/'.$handle).'">'.ra_name($handle).'</a> '.$what.'</h5>';	

		if($type=='Q'){
			$output .= '<p><a href="'. qa_q_path_html($p['postid'], $p['title']) .'" title="'. $p['title'] .'">'.qa_html($p['title']).'</a></p>';
		}elseif($type=='A'){
			$output .= '<p><a href="'.ra_post_link($p['parentid']).'#a'.$p['postid'].'">'. substr(strip_tags($p['content']), 0, 50).'</a></p>';
		}else{
			$output .= '<p><a href="'.ra_post_link($p['parentid']).'#c'.$p['postid'].'">'. substr(strip_tags($p['content']), 0, 50).'</a></p>';
		}
		
		
		if ($type=='Q'){
			$output .= '<div class="counts"><div class="vote-count icon-chevron-up"><span>'.$p['netvotes'].'</span></div>';
			$output .= '<div class="ans-count icon-chat-4"><span>'.$p['acount'].'</span></div></div>';
		}elseif($type=='A'){
			$output .= '<div class="counts"><div class="vote-count icon-chevron-up"><span>'.$p['netvotes'].'</span></div>';
		}

		$output .= '</div>';	
		$output .= '</li>';
	}
	$output .= '</ul>';
	echo $output;
}

// output the list of selected post type
function ra_user_post_list($handle, $type, $limit){
	$userid = qa_handle_to_userid($handle);
	require_once QA_INCLUDE_DIR.'qa-app-posts.php';
	$post = qa_db_query_sub('SELECT * FROM ^posts WHERE ^posts.type=$ and ^posts.userid=# ORDER BY ^posts.created DESC LIMIT #', $type, $userid, $limit);	
	
	$output = '<ul class="question-list users-widget">';
	while($p = mysql_fetch_array($post)){

		if($type=='Q'){
			$what = _ra_lang('asked');
		}elseif($type=='A'){
			$what = _ra_lang('answered');
		}elseif('C'){
			$what = _ra_lang('commented');
		}
		
		$handle = qa_post_userid_to_handle($p['userid']);

		$output .= '<li id="q-list-'.$p['postid'].'" class="question-item">';
		if ($type=='Q'){
			$output .= '<div class="big-ans-count pull-left">'.$p['acount'].'<span>'._ra_lang('Ans').'</span></div>';
		}elseif($type=='A'){
			$output .= '<div class="big-ans-count pull-left vote">'.$p['netvotes'].'<span>'._ra_lang('Vote').'</span></div>';
		}
		$output .= '<div class="list-right">';

		if($type=='Q'){
			$output .= '<h5><a href="'. qa_q_path_html($p['postid'], $p['title']) .'" title="'. $p['title'] .'">'.qa_html($p['title']).'</a></h5>';
		}elseif($type=='A'){
			$output .= '<h5><a href="'.ra_post_link($p['parentid']).'#a'.$p['postid'].'">'. substr(strip_tags($p['content']), 0, 50).'</a></h5>';
		}else{
			$output .= '<h5><a href="'.ra_post_link($p['parentid']).'#c'.$p['postid'].'">'. substr(strip_tags($p['content']), 0, 50).'</a></h5>';
		}
		
		$output .= '<div class="list-date"><span class="icon-calendar-2">'.date('d M Y', strtotime($p['created'])).'</span>';	
		$output .= '<span class="icon-chevron-up">'.$p['netvotes'].' '._ra_lang('votes').'</span></div>';	
		$output .= '</div>';	
		$output .= '</li>';
	}
	$output .= '</ul>';
	echo $output;
}

function ra_user_badges_list($userid) {
	if(!qa_opt('badge_active'))
		return;
		
	$handles = qa_userids_to_handles(array($userid));
	$handle = $handles[$userid];
	
	// displays badge list in user profile

	$result = qa_db_read_all_assoc(
		qa_db_query_sub(
			'SELECT badge_slug as slug, object_id AS oid FROM ^userbadges WHERE user_id=#',
			$userid
		)
	);
	

	if(count($result) > 0) {
		
		// count badges
		$bin = qa_get_badge_list();
		
		$badges = array();
		
		foreach($result as $info) {
			$slug = $info['slug'];
			$type = $bin[$slug]['type'];
			if(isset($badges[$type][$slug])) $badges[$type][$slug]['count']++;
			else $badges[$type][$slug]['count'] = 1;
			if($info['oid']) $badges[$type][$slug]['oid'][] = $info['oid'];
		}
		
		foreach($badges as $type => $badge) {

			$typea = qa_get_badge_type($type);
			$types = $typea['slug'];
			$typed = $typea['name'];
			$output = '';
			//$output = '<h3 class="badge-title" title="'.qa_lang('badges/'.$types.'_desc').'">'.$typed.'</h3>';				
			foreach($badge as $slug => $info) {
				
				$badge_name=qa_badge_name($slug);
				if(!qa_opt('badge_'.$slug.'_name')) qa_opt('badge_'.$slug.'_name',$badge_name);
				$name = qa_opt('badge_'.$slug.'_name');
				
				$count = $info['count'];
				
				if(qa_opt('badge_show_source_posts')) {
					$oids = @$info['oid'];
				}
				else $oids = null;
				
				$var = qa_opt('badge_'.$slug.'_var');
				$desc = qa_badge_desc_replace($slug,$var,false);
				
				// badge row
				
				$output .= '<div class="badge-container-badge">
									<span class="user-badge '.$types.' icon-badge" title="'.$desc.' ('.$typed.')">'.qa_html($name).'</span>&nbsp;<span class="badge-count">x&nbsp;'.$count.'</span>'.(is_array($oids)?'<i class="icon-chevron-down"></i>':'');
				
				// source row(s) if any	
				if(is_array($oids)) {
					$output .= '
								<div class="badge-sources '.$slug.'"><ul>';
					foreach($oids as $oid) {
						$post = qa_db_select_with_pending(
							qa_db_full_post_selectspec(null, $oid)
						);								
						$title=$post['title'];
						
						$anchor = '';
						
						if($post['parentid']) {
							$anchor = urlencode(qa_anchor($post['type'],$oid));
							$oid = $post['parentid'];
							$title = qa_db_read_one_value(
								qa_db_query_sub(
									'SELECT BINARY title as title FROM ^posts WHERE postid=#',
									$oid
								),
								true
							);	
						}
						
						//$length = 30;
						
						$text = $title;
						
						$output .= '<li><a href="'.qa_path_html(qa_q_request($oid,$title),NULL,qa_opt('site_url')).($anchor?'#'.$anchor:'').'">'.qa_html($text).'</a></li>';
					}
					$output .= '</ul></div>';
				}
				$output .= '</div>';
				
			}
			
			$outa[] = $output;
		}

		$fields = '<div class="badge-user-block widget"><h3 class="widget-title">'._ra_lang('Badges').'</h3><div class="widget-inner">'.implode('',$outa).'</div></div>';
		
	}

	return $fields;
	
}

function get_user_activity($handle){
	$userid = qa_handle_to_userid($handle);
	require_once QA_INCLUDE_DIR.'qa-db-selects.php';
	require_once QA_INCLUDE_DIR.'qa-app-format.php';
	
	$loginuserid=qa_get_logged_in_userid();
	$identifier=QA_FINAL_EXTERNAL_USERS ? $userid : $handle;

	list($useraccount, $questions, $answerqs, $commentqs, $editqs)=qa_db_select_with_pending(
		QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
		qa_db_user_recent_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_activity')),
		qa_db_user_recent_a_qs_selectspec($loginuserid, $identifier),
		qa_db_user_recent_c_qs_selectspec($loginuserid, $identifier),
		qa_db_user_recent_edit_qs_selectspec($loginuserid, $identifier)
	);
	
	if ((!QA_FINAL_EXTERNAL_USERS) && !is_array($useraccount)) // check the user exists
		return include QA_INCLUDE_DIR.'qa-page-not-found.php';


//	Get information on user references

	$questions=qa_any_sort_and_dedupe(array_merge($questions, $answerqs, $commentqs, $editqs));
	$questions=array_slice($questions, 0, qa_opt('page_size_activity'));
	$usershtml=qa_userids_handles_html(qa_any_get_userids_handles($questions), false);
	$htmldefaults=qa_post_html_defaults('Q');
	$htmldefaults['whoview']=false;
	$htmldefaults['voteview']=false;
	$htmldefaults['avatarsize']=0;
	
	foreach ($questions as $question)
		$qa_content[]=qa_any_to_q_html_fields($question, $loginuserid, qa_cookie_get(),
			$usershtml, null, array('voteview' => false) + qa_post_html_options($question, $htmldefaults));


	$output = '<div class="widget user-activities">';
	$output .= '<h3 class="widget-title">'.ra_name($handle).'\'s '._ra_lang('activities').'</h3>';
	$output .='<ul class="question-list">';
	if(isset($qa_content)){
		foreach ($qa_content as $qs){

			if($qs['what'] == 'answered'){
				$icon = 'icon-chat-3 answered';
			}elseif($qs['what'] == 'asked'){
				$icon = 'icon-question asked';
			}elseif($qs['what'] == 'commented'){
				$icon = 'icon-chat-2 commented';
			}elseif($qs['what'] == 'edited' || $qs['what'] == 'answer edited'){
				$icon = 'icon-edit edited';
			}elseif($qs['what'] == 'closed'){
				$icon = 'icon-error closed';
			}elseif($qs['what'] == 'answer selected'){
				$icon = 'icon-checked selected';
			}elseif($qs['what'] == 'recategorized'){
				$icon = 'icon-folder-close recategorized';
			}else{
				$icon = 'icon-pin undefined';
			}
			
			$output .='<li class="activity-item">';
			$output .= '<div class="type pull-left '.$icon.'"></div>';
			$output .= '<div class="list-right">';
			$output .= '<h5 class="when"><a href="'.@$qs['what_url'].'">'.$qs['what'].'</a> '.implode(' ', $qs['when']).'</h5>';
			$output .= '<h5 class="what"><a href="'.$qs['url'].'">'.$qs['title'].'</a></h5>';
			$output .= '</div>';
			$output .='</li>';
		}
	}else{
		$output .='<li>'._ra_lang('No activity yet.').'</li>';
	}
	$output .= '</ul>';
	$output .= '</div>';
	return $output;
}