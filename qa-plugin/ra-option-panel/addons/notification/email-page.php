<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}


class qw_notification_page {
	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function match_request($request)
	{
		if ($request=='admin/email-notifications')
			return true;

		return false;
	}
	function process_request($request)
	{
		
		$qa_content=qa_content_prepare();		
		$qa_content['site_title']="Email Notifications";
		$qa_content['error']="";
		$qa_content['suggest_next']="";
		
		$qa_content['custom']= $this->opt_form();
		
		return $qa_content;	
	}
	
	function opt_form(){
		ob_start();
		?>
			<div id="ra-install-page">
				<div class="step-one">
					<h1>Welcome to the theme installation</h1>
				</div>
				<?php $this->qw_install_nav(); ?>
			</div>
		<?php
		$output = ob_get_clean();
		
		return $output;
	}
	function qw_install_nav(){
		?>
			<ul class="install-nav">
				<li><a href="#" class="icon-cog">Settings</a></li>
			</ul>
		<?php
	}
	
	
}

