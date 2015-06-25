<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Admin navigation */
?>
<div class="admin-nav">
	<ul class="nav">
		<?php 	
			foreach ($context->content['navigation']['ra_nav']['admin'] as $k => $a){
				$icon = !empty($a['icon']) ? $a['icon'] : 'icon-cog';
				echo '<li'.(isset($context->content['navigation']['sub'][$k]['selected']) ? ' class="active" ': '').'><a class="'.$icon.'" href="'.$a['url'].'">'.$a['label'].'</a></li>';
			}
		?>
	</ul>
</div>