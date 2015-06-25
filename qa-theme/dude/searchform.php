<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	//search form
	$search=$context->content['search'];
?> 
<form <?php echo @$search['form_tags']; ?> class="form-inline search-form pull-right">
	<?php echo @$search['form_extra']; ?>
	<div class="form-group">
		<input type="text" <?php echo @$search['field_tags']; ?> value="<?php echo @$search['value']; ?>" class="form-control navbar-search" placeholder="<?php ra_lang('Search for questions'); ?>" />
	</div>
	<button type="submit" class="icon-magnifier btn btn-primary" title="<?php echo @$search['button_label']; ?>"></button>			
</form>
