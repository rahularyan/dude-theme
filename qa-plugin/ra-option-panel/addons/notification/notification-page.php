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
		if (qa_is_logged_in() && $request=='notifications')
			return true;

		return false;
	}
	function process_request($request)
	{
		
		$qa_content=qa_content_prepare();		
		$qa_content['site_title']="Notifications";
		$qa_content['error']="";
		$qa_content['suggest_next']="";
		$qa_content['template']="notifications";

		// Get the no of notifications 
		$start=qa_get_start();
		$pagesize = qa_opt('qw_all_notification_page_size') ;
		if (!$pagesize) {
			$pagesize = 15 ;
		}
		
		$notifications_count = qw_get_notification_count(qa_get_logged_in_userid()) ; 
		$qa_content['page_links']=qa_html_page_links(qa_request(), $start, $pagesize, $notifications_count , qa_opt('pages_prev_next'));
		
		if (empty($qa_content['page_links']))
			$qa_content['suggest_next']=qa_html_suggest_ask();
		
		$qa_content['custom']= $this->opt_form();
		
		return $qa_content;	
	}
	
	function opt_form(){
		ob_start();
		?>
			<div id="notifications-page" class="clearfix">
				<a class="mark-activity icon-tick" href="#" data-id="<?php echo qa_get_logged_in_userid() ?> "> <?php echo qa_lang('dude/mark_all_as_read') ?> </a>
				<?php 
					$limit = qa_opt('qw_all_notification_page_size') ;
					if (!$limit) {
						$limit = 15 ;
					}
					qw_activitylist($limit); 
				?>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

	
	
}

