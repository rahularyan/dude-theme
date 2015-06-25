<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Answer form */
	$a_form = $context->content['a_form'];
	$handle=qa_get_logged_in_handle();
?>
<div class="answer-form">
	<div class="answer-form-right" <?php echo isset($a_form['id']) ? ('id="'.$a_form['id'].'"') : ''; ?> >
		<?php if (isset($handle)){ ?>
			<div class="avatar pull-left">
				<a class="avatar-wrap" href="<?php echo qa_path_html('user/'.$handle) ?>">
					<img src="<?php echo ra_get_avatar($handle, 40, false); ?>" />
				</a>
			</div>	
		<?php 
			}
			
			$context->form($a_form);
			$context->c_list(@$a_form['c_list'], 'qa-a-item');
		?>	
		
		<h3 class="know-someone"><?php ra_lang('Know someone who can answer? Share a link to this') ?></h3>
		<ul class="social-buttons cf share clearfix">
			<li>
				<a href="https://twitter.com/share" class="twitter-share-button" data-size="medium">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
			</li>
			<li>			
				<!-- Place this tag where you want the +1 button to render. -->
				<div class="g-plusone"></div>
				<!-- Place this tag after the last +1 button tag. -->
				<script type="text/javascript">
				(function() {
				var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				po.src = 'https://apis.google.com/js/platform.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				})();
				</script>
			</li>
			<li>			
				<div id="fb-root"></div>
				<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return;
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=411924588919031";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));</script>
				<div class="fb-share-button" data-href="<?php echo ra_current_url(); ?>" data-type="button_count"></div>
			</li>
		</ul>		
	</div>
</div>