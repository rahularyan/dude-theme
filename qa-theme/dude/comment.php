<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		@$handle = qa_post_userid_to_handle($args['raw']['userid']);
	}else{
		@$handle = @$args['raw']['handle'];
	}
	/* Single Comment */
	$extraclass=@$args['classes'].(@$args['hidden'] ? ' qa-c-item-hidden' : '');
?>	
	<div class="comment-item <?php echo $extraclass; ?><?php echo isset($args['url'])? ' link-to' :''; ?>" <?php echo @$args['tags']; ?>>
		<?php if(isset($args['raw']['postid'])){ ?>
			<div class="avatar pull-left" data-handle="<?php echo @$handle; ?>" data-id="<?php echo qa_handle_to_userid($handle); ?>">
				<a href="<?php echo qa_path_html('user/'.$handle);?>">
					<?php $context->ra_list_avatar($args,30); ?>
				</a>
			</div>
		<?php } ?>
		<?php if(qa_opt('ra_colla_comm')){ ?>
			<a class="show-full-comment icon-chevron-down pull-right" title="Expand to see full comment and options"></a>
		<?php } ?>
		<div class="content<?php echo qa_opt('ra_colla_comm') ? ' collapsible' : ''; ?>">
			<div class="initial-height">
				<?php
					$context->error(@$args['error']);
					if (isset($args['expand_tags']))
						$context->c_item_expand($args);
					elseif (isset($args['url'])){
						echo '<i class="icon-link"></i>';
						$context->c_item_link($args);
					}else{
						$context->c_item_buttons($args);
						$context->c_item_content($args);
					}
					
				?>
				
				<?php $context->post_meta($args, 'comment-meta'); ?>					
			</div>
		</div>		
	</div>
