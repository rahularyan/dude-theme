<?php
	class qw_init {
		
		function init_queries($tableslc) {
			//qw_check_for_new_version(false);
			include_once QA_INCLUDE_DIR.'qa-app-users.php';
			//if(qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN){
				$queries = array();
				$queries = qw_apply_filter('init_queries', $queries, $tableslc);
				$tablename=qa_db_add_table_prefix('ra_userevent');			
				if (!in_array($tablename, $tableslc)) {
					require_once QA_INCLUDE_DIR.'qa-app-users.php';
					require_once QA_INCLUDE_DIR.'qa-db-maxima.php';
					
					
					$queries[]= 'CREATE TABLE ^ra_userevent ('.
						'id bigint(20) NOT NULL AUTO_INCREMENT,'.
						'datetime DATETIME NOT NULL,'.
						'userid '.qa_get_mysql_user_column_type().','.
						'postid int(10) unsigned DEFAULT NULL,'.
						'effecteduserid '.qa_get_mysql_user_column_type().' unsigned DEFAULT NULL,'.
						'event VARCHAR (20) CHARACTER SET utf8 NOT NULL,'.
						'params text NOT NULL,'.
						'`read` tinyint(1) NOT NULL DEFAULT "0",'.
						'PRIMARY KEY (id),'.
						'KEY datetime (datetime),'.
						'KEY userid (userid),'.
						'KEY event (event)'.
					') ENGINE=MyISAM DEFAULT CHARSET=utf8';
				}
				return $queries;
			///}
		}

	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
