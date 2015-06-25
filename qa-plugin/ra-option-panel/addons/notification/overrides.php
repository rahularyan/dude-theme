<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


	function qa_create_new_user($email, $password, $handle, $level=QA_USER_LEVEL_BASIC, $confirmed=false)
/*
	Create a new user (application level) with $email, $password, $handle and $level.
	Set $confirmed to true if the email address has been confirmed elsewhere.
	Handles user points, notification and optional email confirmation.
*/
	{
			
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-points.php';
		require_once QA_INCLUDE_DIR.'qa-app-options.php';
		require_once QA_INCLUDE_DIR.'qa-app-emails.php';
		require_once QA_INCLUDE_DIR.'qa-app-cookies.php';

		$userid=qa_db_user_create($email, $password, $handle, $level, qa_remote_ip_address());
		qa_db_points_update_ifuser($userid, null);
		qa_db_uapprovecount_update();
		
		if ($confirmed)
			qa_db_user_set_flag($userid, QA_USER_FLAGS_EMAIL_CONFIRMED, true);
			
		if (qa_opt('show_notice_welcome'))
			qa_db_user_set_flag($userid, QA_USER_FLAGS_WELCOME_NOTICE, true);
		
		$custom=qa_opt('show_custom_welcome') ? trim(qa_opt('custom_welcome')) : '';
		
		if (qa_opt('confirm_user_emails') && ($level<QA_USER_LEVEL_EXPERT) && !$confirmed) {
			$confirm=strtr(qa_lang('emails/welcome_confirm'), array(
				'^url' => qa_get_new_confirm_url($userid, $handle)
			));
			
			if (qa_opt('confirm_user_required'))
				qa_db_user_set_flag($userid, QA_USER_FLAGS_MUST_CONFIRM, true);
				
		} else
			$confirm='';
		
		if (qa_opt('moderate_users') && qa_opt('approve_user_required') && ($level<QA_USER_LEVEL_EXPERT))
			qa_db_user_set_flag($userid, QA_USER_FLAGS_MUST_APPROVE, true);
				
		qw_send_notification($userid, $email, $handle, qa_lang('emails/welcome_subject'), nl2br(qa_lang('emails/welcome_body')), array(
			'^password' => isset($password) ? qa_lang('main/hidden') : qa_lang('users/password_to_set'), // v 1.6.3: no longer email out passwords
			'^url' => qa_opt('site_url'),
			'^custom' => strlen($custom) ? ($custom."\n\n") : '',
			'^confirm' => $confirm,
		));
		
		qa_report_event('u_register', $userid, $handle, qa_cookie_get(), array(
			'email' => $email,
			'level' => $level,
		));
		
		return $userid;
	}
	function qa_send_new_confirm($userid)
/*
	Set a new email confirmation code for the user and send it out
*/
	{
				
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		require_once QA_INCLUDE_DIR.'qa-app-emails.php';

		$userinfo=qa_db_select_with_pending(qa_db_user_account_selectspec($userid, true));
		
		if (!qw_send_notification($userid, $userinfo['email'], $userinfo['handle'], qa_lang('emails/confirm_subject'), nl2br(qa_lang('emails/confirm_body')), array(
			'^url' => qa_get_new_confirm_url($userid, $userinfo['handle']),
		)))
			qa_fatal_error('Could not send email confirmation');
	}
	
	function qa_start_reset_user($userid)
/*
	Start the 'I forgot my password' process for $userid, sending reset code
*/
	{
		
		
		require_once QA_INCLUDE_DIR.'qa-db-users.php';
		require_once QA_INCLUDE_DIR.'qa-app-options.php';
		require_once QA_INCLUDE_DIR.'qa-app-emails.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';

		qa_db_user_set($userid, 'emailcode', qa_db_user_rand_emailcode());

		$userinfo=qa_db_select_with_pending(qa_db_user_account_selectspec($userid, true));

		if (!qw_send_notification($userid, $userinfo['email'], $userinfo['handle'], qa_lang('emails/reset_subject'), nl2br(qa_lang('emails/reset_body')), array(
			'^code' => $userinfo['emailcode'],
			'^url' => qa_path_absolute('reset', array('c' => $userinfo['emailcode'], 'e' => $userinfo['email'])),
		)))
			qa_fatal_error('Could not send reset password email');
	}
	function qa_complete_reset_user($userid)
/*
	Successfully finish the 'I forgot my password' process for $userid, sending new password
*/
	{
		
		require_once QA_INCLUDE_DIR.'qa-util-string.php';
		require_once QA_INCLUDE_DIR.'qa-app-options.php';
		require_once QA_INCLUDE_DIR.'qa-app-emails.php';
		require_once QA_INCLUDE_DIR.'qa-app-cookies.php';
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
	
		$password=qa_random_alphanum(max(QA_MIN_PASSWORD_LEN, QA_NEW_PASSWORD_LEN));
		
		$userinfo=qa_db_select_with_pending(qa_db_user_account_selectspec($userid, true));
		
		if (!qw_send_notification($userid, $userinfo['email'], $userinfo['handle'], qa_lang('emails/new_password_subject'), nl2br(qa_lang('emails/new_password_body')), array(
			'^password' => $password,
			'^url' => qa_opt('site_url'),
		)))
			qa_fatal_error('Could not send new password - password not reset');
		
		qa_db_user_set_password($userid, $password); // do this last, to be safe
		qa_db_user_set($userid, 'emailcode', ''); // so can't be reused

		qa_report_event('u_reset', $userid, $userinfo['handle'], qa_cookie_get(), array(
			'email' => $userinfo['email'],
		));
	}