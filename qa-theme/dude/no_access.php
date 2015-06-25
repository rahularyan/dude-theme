<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* un-authorize access warning */

?>
<div class="no-access">
	<i class="icon-locked"></i>
	<h1>Dude! get out of here</h1>
	<p class="lead">You don't have permission to this access page</p>
</div>