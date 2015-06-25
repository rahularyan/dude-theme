<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Footer of the template */
?>
		</section>

		<?php $context->widgets('main', 'low'); // widget position below content; ?>
		<div id="bottom">
			<?php $context->ra_dynamic_layout('bottom'); ?>
		</div>
		
		<footer id="mastfooter" class="clearfix">
			<div class="container">				
				<?php $context->widgets('main', 'bottom'); // widget position in footer; ?>
				<?php 	
					$context->nav('footer');
					
					// do not remove below copyright unless you have purchased copyright removal licence
				?>
				<div class="pull-right">
					<p class="copyright">&copy; <?php echo date("Y").' '.qa_opt('site_title'); ?> | Crafted with love by <a href="http://www.rahularyan.com" title="Q2A theme designer">Rahul Aryan</a></p>
				</div>
			</div>
		</footer>
		<script src="<?php echo DUDE_THEME_URL; ?>/js/jquery-ui.js"></script>
		<script src="<?php echo DUDE_THEME_URL; ?>/js/bootstrap.js"></script>				
		<script src="<?php echo DUDE_THEME_URL; ?>/js/jquery.dlmenu.js"></script>		

		<script src="<?php echo DUDE_THEME_URL; ?>/js/dude.js"></script>
		<script type="text/javascript">
			<?php
			if (ra_is_home())
				echo base64_decode(ra_db_builder('js_home'));
			else
				echo base64_decode(ra_db_builder('js_'.$context->template));
			?>
		</script>
		<?php if (ra_edit_mode()){ ?>
			<script src="<?php echo DUDE_THEME_URL; ?>/js/ra_builder.js"></script>			
		<?php } ?>
		
		<?php if(strlen(qa_opt('google_analytics'))){ ?>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '<?php echo qa_opt('google_analytics'); ?>']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
		<?php } ?>
	</body>
</html>