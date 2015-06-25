<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Admin footer */
?>
		</section>
		<script src="<?php echo DUDE_THEME_URL; ?>/js/bootstrap.js"></script>		
		<script src="<?php echo DUDE_THEME_URL; ?>/js/colorpicker.js"></script>		
		<script src="<?php echo DUDE_THEME_URL; ?>/js/dude.js"></script>
		
	</body>
</html>