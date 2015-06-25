<?php
/*
	RA Social
	Author: Rahul Aryan
	Website: http://www.rahularyan.com
	Licence: GPLv3
*/

	class qa_html_theme_layer extends qa_html_theme_base {
		function head_script(){
			qa_html_theme_base::head_script();
			
			$this->output(
				'<script type="text/javascript">',
				"if (typeof qa_wysiwyg_editor_config == 'object')",
				"\tqa_wysiwyg_editor_config.skin='moono';",
				'</script>'
			);
		}

	}
