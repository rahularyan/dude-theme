<?php 
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Admin plugin lists */
	
	
	$plugins = ra_installed_plugin();
?>
<table class="plugin-list">
	<tr class="list-header">
		<td class="name"><?php ra_lang('Name'); ?></td>
		<td class="description"><?php ra_lang('Description');?></td>
		<td class="author"><?php ra_lang('Author');?></td>
		<td class="rest"><?php ra_lang('Actions');?></td>
	</tr>
	<?php
	foreach ($plugins as $key => $form){ 		
		 ?>
			
			<tr class="plugin-item" <?php echo $form['tags']; ?>>				
				<td class="name">
					<a href="<?php echo @$form['option']; ?>" class="plugin-icon icon-cord"></a>	
					<a href="<?php echo @$form['option']; ?>"><?php echo $form['name'] ; ?></a>					
				</td>
				<td class="description"><?php echo $form['description'] ; ?></td>						
				<td class="author"><a href="<?php echo @$form['author_uri']; ?>"><?php echo $form['author'] ; ?></a></td>
				<td class="rest">	
				
					<a class="btn icon-folder-close path-popover pull-right" title="Plugin path" data-content="<?php echo $form['path'] ; ?>" data-placement="top" data-toggle="popover" href="#" data-original-title="Popover on top"></a>
					
					<a href="<?php echo @$form['option']; ?>" class="icon-tools btn pull-right"></a>
					<a class="version label label-success">V <?php echo $form['version'] ; ?><?php echo $form['update'] ; ?></a>
					
				</td>				
			</tr>

		<?php

	}
	?>
</table>