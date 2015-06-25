<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/

	
	File: qa-plugin/wysiwyg-editor/qa-plugin.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Initiates WYSIWYG editor plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

/*
	Plugin Name: RA WYSIWYG Editor
	Plugin URI: http://rahularyan.com/ra-wysiwyg-editor
	Plugin Description: New version of CKeditor, based on qa wysiwyg editor
	Plugin Version: 1.0
	Plugin Date: 2013-10-14
	Plugin Author: Rahul Aryan
	Plugin Author URI: http://www.rahularyan.com/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: 
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	qa_register_plugin_layer('ra-wysiwyg-layer.php', 'RA wysiwyg layer');
	qa_register_plugin_module('editor', 'ra-wysiwyg.php', 'ra_wysiwyg', 'RA WYSIWYG Editor');
	qa_register_plugin_module('page', 'ra-wysiwyg-upload.php', 'ra_wysiwyg_upload', 'RA WYSIWYG Upload');


/*
	Omit PHP closing tag to help avoid accidental output
*/