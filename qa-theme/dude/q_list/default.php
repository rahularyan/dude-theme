<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	if(!isset($args['raw']['handle'])){
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		$handle = qa_post_userid_to_handle($args['raw']['userid']);
	}else{
		$handle = $args['raw']['handle'];
	}
	/* Default list layout */
?>
<div class="list-q-layout list-item clearfix <?php echo rtrim(' '.@$args['classes']); ?>" <?php echo @$args['tags']; ?>>
	<?php 
		if(isset($args['raw']['userfavoriteq']) && $args['raw']['userfavoriteq'] == 1)
			echo '<i class="fav-indicator icon-star"></i>';
	?>
	<div class="for-left">
		<div class="list-avatar avatar pull-left" data-handle="<?php echo $handle; ?>" data-id="<?php echo qa_handle_to_userid($handle); ?>">
			<a href="<?php echo qa_path_html('user/'.$handle) ?>">
				<?php $context->ra_list_avatar($args, 50); ?>
			</a>
		</div>
		<div class="list_content">
			<div class="list-counts pull-right">		
				<div class="vote">
					<?php $context->voting($args); ?>
				</div>
				<?php if(isset($args['answers_raw'])){ ?>
					<div class="ans-count ra-tip" title="<?php ra_lang('Answers'); ?>"><span><?php echo @$args['answers_raw']; ?></span><?php ra_lang('Ans'); ?></div>
				<?php } ?>
				<div class="view-count ra-tip" title="<?php ra_lang('Views'); ?>"><span><?php echo @$args['raw']['views']; ?></span><?php ra_lang('View'); ?></div>
			</div>
			<h4 class="list-title"><a href="<?php echo $args['url']?>"><?php echo $args['title']; ?></a></h4>
			<?php $context->q_item_content($args); ?>
			<div class="list-meta">
				<div class="q-status pull-left"><?php echo ra_post_notice($args); ?></div>
				<?php $context->post_meta($args, 'qa-q-item'); ?>
			</div>
			<?php if (!empty($args['q_tags'])) { ?>
			<div class="list-tags">
				<span class="label"><?php ra_lang('Tags: '); ?></span>
				<?php
					// list tags
					$context->post_tags($args, 'qa-q-item');
				?>
			</div>
			<?php } ?>

		</div>
	</div>

	<?php 			
		$context->q_item_buttons($args);
	?>
</div>