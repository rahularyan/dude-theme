<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Admin index */
?>
<div class="admin-right">
	<header class="page-header clearfix">
		<div class="page-title pull-left">
			<h1><?php $context->title(); ?></h1>
		</div>
		<?php $context->search(); ?>
	</header>
	<?php
		$context->widgets('full', 'high');
		//$context->sidepanel();
			$content=$context->content;
			$context->output('<div class="contents '.(@$context->content['hidden'] ? ' qa-main-hidden' : '').'">');			
			$context->widgets('main', 'top');					
			$context->widgets('main', 'high');				
			$context->main_parts($content);				
			$context->widgets('main', 'low');
			$context->page_links();
			$context->suggest_next();			
			$context->widgets('main', 'bottom');
			$context->output('</div>', '');
		$context->widgets('full', 'low');			
	?>

</div>
	

