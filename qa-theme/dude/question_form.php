<?php 
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Question form */
	
	
	if (isset($context->content['form_q_edit'])){
	
	$form = $context->content['form_q_edit'];
	
	if (isset($form['tags']))
			$context->output('<form class="form-horizontal" '.$form['tags'].'>');
		?>
		<div class="question-from">
			<?php $context->form_body($form); ?>
		</div>
		<?php
	
	if (isset($form['tags']))
		$context->output('</form>');
	
}