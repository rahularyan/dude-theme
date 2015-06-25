<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* answer item */
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		$handle = qa_post_userid_to_handle($args['raw']['userid']);
	}else{
		$handle = $args['raw']['handle'];
	}
	$extraclass=@$args['classes'].($args['hidden'] ? ' qa-a-list-item-hidden' : ($args['selected'] ? ' qa-a-list-item-selected' : ''));
	$user_data = ra_user_data($handle);
	$selected_id = ra_answer_selected($args['raw']['parentid']);

	$class = $args['hidden'] ? ' post-hidden' : ($args['selected']? ' selected' : '');
?>
<div class="answer vote-float <?php echo $extraclass; ?>" <?php echo @$args['tags'];?>>
	<div class="vote-c pull-left">			
		<?php $context->voting($args); ?>
	</div>
	<div class="ans-content<?php echo $class; ?>">
		<div class="a-head clearfix">
			<div class="ans-select clearfix">
				<?php 			
					if(!($selected_id) || ($selected_id == $args['raw']['postid']))
						$context->a_selection($args);
				?>
			</div>
			<div class="avatar pull-left" data-handle="<?php echo $handle; ?>" data-id="<?php echo qa_handle_to_userid($handle); ?>">
				<a class="avatar-wrap" href="<?php echo qa_path_html('user/'.$handle) ?>">
					<?php $context->ra_list_avatar($args,40); ?>
				</a>			
			</div>
			<div class="answeredby">
				<a href="<?php echo qa_path_html('user/'.$handle) ?>"><?php echo ra_name($handle); ?></a>
				<span class="points"><?php echo _ra_lang('Points').'<span>'.ra_user_points($handle).'</span>'; ?></span>
			</div>
		</div>
		<div class="content-inner">
			<?php				
				$context->error(@$args['error']);
				$context->a_item_content($args);
				$context->post_meta($args, 'qa-a-item');
			?>
		</div>
		<?php
			if (isset($args['main_form_tags']))
				$context->output('<form '.$args['main_form_tags'].'>'); // form for buttons on answer
			$context->a_item_buttons($args);			
			if (isset($args['main_form_tags'])) {
				$context->form_hidden_elements(@$args['buttons_form_hidden']);
				$context->output('</form>');
			}		
		
		?>		
		
		<?php 
			if (isset($args['main_form_tags']))
				$context->output('<form '.$args['main_form_tags'].'>'); // form for buttons on question
				
			//if (!empty($args['c_list']['cs'])): 
		?>
			<div class="comments <?php echo $args['hidden'] ? ' post-hidden' : ''; ?>">
				<?php $context->c_list(@$args['c_list'], 'qa-a-item'); ?>		
			</div>
		<?php 
			//endif; 
			if (isset($args['main_form_tags'])) {
				$context->form_hidden_elements(@$args['buttons_form_hidden']);
				$context->output('</form>');
			}
		?>
		<?php if (!$args['hidden']) $context->c_form(@$args['c_form']); ?>
	</div>
	
</div>