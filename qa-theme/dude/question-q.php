<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* layout of question page question */
	
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		$handle = qa_post_userid_to_handle($args['raw']['userid']);
	}else{
		$handle = $args['raw']['handle'];
	}
	$user_link = qa_path_html('user/'.$handle);
	$user_data = ra_user_data($handle);
	$created = implode('', qa_when_to_html( $args['raw']['created'], qa_opt('show_full_date_days')));
	
?>

<div class="question qa-q-view <?php echo (@$args['hidden'] ? ' qa-q-view-hidden' : '').rtrim(' '.@$args['classes']); ?>"<?php rtrim(' '.@$args['tags']); ?>>
	<?php $context->post_tags($args, 'qa-q-view'); ?>
	<div class="vote-float clearfix">
		<div class="vote-c pull-left">			
			<?php $context->voting($args); ?>
			<?php $context->favorite(); ?>
		</div>
		<div class="q-outer-right">
			<div class="question-content">	
				<div class="q-head">
					<div class="avatar pull-left" data-handle="<?php echo $handle; ?>" data-id="<?php echo qa_handle_to_userid($handle); ?>">
						<a class="avatar-wrap" href="<?php echo $user_link; ?>">
							<?php $context->ra_list_avatar($args,60); ?>
						</a>			
					</div>
					<div class="q-head-inner">
						<h1 class="q-title"><?php ra_q_title($args); ?></h1>
						<dl class="post-meta-q clearfix">
							<dt class="time icon-calendar"><?php ra_lang('Asked'); ?> <time><?php echo $created; ?></time></dt>
							<dt class="category icon-folder-close">
								<a href="<?php echo ra_cat_path($args['raw']['categorybackpath']); ?>"><?php echo $args['raw']['categoryname']; ?></a>
							</dt>
							<dt class="views icon-eye" title="Total views">
								<?php echo @$args['raw']['views']; ?> <?php ra_lang('Views'); ?></dt> 
								<dt class="answer icon-chat-3" title="Total Answers"><?php echo @$args['raw']['acount']; ?> <?php ra_lang('Answers'); ?></dt> 
							<dt class="status" title="<?php ra_lang('Status of question'); ?>"><?php echo ra_post_notice($args); ?></dt> 
						</dl>
					</div>
				</div>

				<?php $context->output_raw( base64_decode(qa_opt('ads_below_question_title'))); ?>

				<?php 	
					$context->output_raw(ra_template_part('question_form', $context, $args));	
					$context->q_view_content($args); 	
					if(qa_opt('short_url_content_on')/* && $_SERVER['REMOTE_ADDR'] != '127.0.0.1'*/)
					{
						$login = qa_opt('short_url_bitly_username');
						$api_key = qa_opt('short_url_bitly_api_key');
						$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
						$api_url =  "http://api.bit.ly/v3/shorten?login=".$login."&apiKey=".$api_key."&uri=".urlencode($url)."&format=txt";
						if(qa_opt($url))
							$short_url = qa_opt($url);
						else{
							$short_url = file_get_contents($api_url);
							qa_opt($url, $short_url);
						}
							echo '<input value="'.$short_url.'">';
					}					
				?>
				<?php
					$context->q_view_extra($args);						
					$context->post_meta($args, 'qa-q-view');									
				?>
				<?php $context->output_raw( base64_decode(qa_opt('ads_after_question_content'))); ?>
				<div class="q-buttons">
					<?php 
					if (isset($args['main_form_tags']))
						$context->output('<form '.$args['main_form_tags'].'>'); // form for buttons on question
						$ans_button = @$args['form']['buttons']['answer']['tags'];
						if(isset($ans_button)){
							$onclick = preg_replace('/onclick="([^"]+)"/', '', $ans_button);
							$args['form']['buttons']['answer']['tags'] = $onclick;
						}
						$context->q_view_buttons($args);
					
					if (isset($args['main_form_tags'])) {
						$context->form_hidden_elements(@$args['buttons_form_hidden']);
						$context->output('</form>');
					}
					
					
					?>
				</div>
				
				<div class="comments" <?php echo @$args['main_form_tags']; ?>>
				<?php //if (!empty($args['c_list']['cs'])): ?>
					<?php 
						if (isset($args['main_form_tags']))
							$context->output('<form '.$args['main_form_tags'].'>'); // form for buttons on question
					
						$context->c_list(@$args['c_list'], 'qa-q-view'); 
						
						if (isset($args['main_form_tags'])) {
							$context->form_hidden_elements(@$args['buttons_form_hidden']);
							$context->output('</form>');
						}
					?>
					<?php //endif; ?>	
					<?php $context->c_form(@$args['c_form']); ?>
				</div>					
			</div>
			<?php
				if (!empty($args['follows'])){
				?>						
					<div class="related-to">
						<?php echo $args['follows']['label']; ?>
						<a href="<?php echo $args['follows']['url']; ?>" class="qa-q-view-follows-link"><?php echo $args['follows']['title']; ?></a>
					</div>
						
				<?php		
				}
				$context->q_view_closed($args); 
			?>	
		</div>
	</div>
</div>
<?php
