<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Comment list */
	
	$class = $args['class'];
	$c = $args['c_list']['cs'];	
	$extraclass=@$c['classes'].(@$c['hidden'] ? ' qa-c-item-hidden' : '');	
?>

<div <?php echo $args['c_list']['tags']; ?> class="<?php echo $class.'-c-list'; ?>" <?php echo (@$c_list['hidden'] ? ' style="display:none;"' : '').' '.@$c_list['tags']; ?>>

	<?php $context->c_list_items($c); ?>	
</div>
