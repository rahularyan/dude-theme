<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Answers list in question page */
?>
<div class="ra-answers<?php echo (!isset($context->content['a_form']) ? ' no-form' : ''); ?>">
<?php if (strlen(@$args['title'])){ ?>
	<h2 <?php echo rtrim(' '.@$args['title_tags']); ?> class="answers-title"><?php echo $args['title']; ?></h2>
<?php } ?>
				
	<div class="answer-list <?php $context->list_vote_disabled($args['as']) ? ' qa-a-list-vote-disabled' : ''; ?>" <?php echo @$args['tags']; ?>>
		<?php $context->a_list_items($args['as']); ?>
	</div>
	<?php $context->page_links(); ?>
</div>
<?php 
	if(isset($context->content['a_form'])){
		$context->a_form($context->content['a_form']); 
	}
	