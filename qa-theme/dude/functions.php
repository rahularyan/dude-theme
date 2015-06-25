<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* function for theme */
	
	
	if(qa_opt('ra_installed')!= true){
	/* add some option when theme init first time */
	
		//create table for builder
	 	qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^builder ('.
				'name VARCHAR (64) UNIQUE NOT NULL,'.				
				'content LONGTEXT'.				
			') ENGINE=MyISAM DEFAULT CHARSET=utf8;'
		);
		import_sql(DUDE_THEME_DIR.'/demo/builder_demo.sql');
		ra_opt('shortcodes', array('widget', 'ra_widget'));		
		ra_opt('ra_installed', true); // update db, so that this code will not execute every time

	}
	

