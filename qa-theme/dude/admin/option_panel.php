<?php 
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Theme option panel */
	
	
	$plugins = ra_installed_plugin();
	$content = $args['content'];
	foreach ($plugins as $k => $info){ 	
		preg_match('/"([^"]+)"/', $info['tags'], $form_id);		
		if($form_id[1] == $args['request']){ 				
			$plugin_info = $info;
		}	
	}
	$tab ='';
	foreach ($content['form_plugin_options']['fields'] as $key => $m){
		if(isset($m['type']) && $m['type'] == 'block_start')
			$tab .=  '<li><a href="#'.$m['tags'].'" class="'.$m['icon'].'" data-toggle="tab">'.$m['label'].'</a></li>';
	}

?>

<form <?php echo @$content['form_plugin_options']['tags']; ?>>
	<ul class="nav nav-tabs" id="theme_option_tab">
		<li><h4><?php echo $plugin_info['name']; ?></h4></li>
		<?php echo $tab; ?>
		<li><a href="#about" data-toggle="tab" class="icon-info-4"><?php ra_lang('About');?></a></li>
	</ul>
	<div class="tab-content">

			<?php 
				foreach ($content as $key => $form){ 
											
					if( $key == 'form_plugin_options'){ 				
						?>													
							<?php $context->form_fields($form, 0); ?>
							
							<div class="tab-pane" id="about">
								<h3><?php echo $plugin_info['name']; ?></h3>
								
								<?php if (strlen($plugin_info['uri'])) echo '<p class="plugin-link"><a href="'.$plugin_info['uri'].'" class="icon-link">'.$plugin_info['uri'].'</a></p>'; ?>
								
								<p class="plugin-tags">
									<?php if (strlen($plugin_info['uri'])) echo '<span class="icon-link">'.$plugin_info['uri'].'</span>'; ?>
									<a class="version icon-stats">v<?php echo $plugin_info['version']; ?><?php echo $plugin_info['update']; ?></a>
									<span class="icon-group"><a href="<?php echo $plugin_info['author_url']; ?>"><?php echo $plugin_info['author']; ?></a></span>
								</p>
								<p><?php echo $plugin_info['description']; ?></p>
								<p><?php ra_lang('Plugin path');?> <?php echo $plugin_info['path']; ?></p>
							</div>	
							
							<div class="form-actions">
								<?php $context->form_buttons($form, 0); ?>
							</div>
							<?php $context->form_hidden($form); ?>					

						<?php
					}

				} 
			?>
	</div>