<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* un-authorize access warning */

?>
<div class="no-access">
	<i class="icon-locked"></i>
	<h1><?php ra_lang('Confirm your email address'); ?></h1>
	<p class="lead"><?php ra_lang('To complete your registration, please click the confirmation link that has been emailed to you, or <a href="'.qa_path_html('confirm').'">request another</a>.'); ?></p>
</div>