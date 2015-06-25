<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Login page */
	
	
	foreach ($context->content as $k => $part){
		if (strpos($k, 'form')===0){
		
		$part['fields']['email_handle']['hidelabel'] = true;
		$part['fields']['password']['hidelabel'] = true;
		?>	
			<div class="container">
				<?php $context->form($part); ?>
			</div>
		<?php

		}
		elseif(strpos($k, 'custom')===0){
		?>	
			<div class="container">
				<div class="custom">
					<?php $context->output_raw($part); ?>
				</div>
			</div>
		<?php
		}
	}
	
	