<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Main entry page layout */
?>
<div id="index">
	<?php		
		$content=$context->content;
		$context->output('<div class="contents '.(@$context->content['hidden'] ? ' qa-main-hidden' : '').'">');				
		$context->main_parts($content);				
		//$context->page_links();		
		
		$context->output('</div>', '');
	?>

</div>