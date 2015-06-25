<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	//Registration page
	foreach ($context->content as $k => $part){
		if (strpos($k, 'form')===0){
		$part['fields']['handle']['hidelabel'] = true;
		$part['fields']['password']['hidelabel'] = true;
		$part['fields']['email']['hidelabel'] = true;
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
	