<?php

/*
	Plugin Name: Ra Theme Options
	Plugin URI: http://rahularyan.com
	Plugin Description: RA theme option panel for customizing theme by RahulAryan
	Plugin Version: 3.0
	Plugin Date: 2013-10-9
	Plugin Author: Rahul Aryan
	Plugin Author URI: http://www.rahularyan.com/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: 
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}
define('QW_CONTROL_DIR', dirname( __FILE__ ));
define('QW_CONTROL_ADDON_DIR', QW_CONTROL_DIR.'/addons');


define('QW_BASE_URL', get_base_url());
define('QW_CONTROL_URL', QW_BASE_URL.'/qa-plugin/ra-option-panel');
define('Q_THEME_URL', QW_BASE_URL.'/qa-theme/dude');
define('Q_THEME_DIR', QA_THEME_DIR . '/dude');

qa_register_plugin_module('event', 'inc/init.php', 'qw_init', 'QW Init');
qa_register_plugin_module('event', 'inc/cs-event-logger.php', 'qw_event_logger', 'QW Event Logger');
qa_register_plugin_module('event', 'inc/cs-user-events.php', 'qw_user_event_logger', 'QW User Event Logger');

// register plugin language
qa_register_plugin_phrases('language/dude-lang-*.php', 'dude');

qa_register_plugin_module('module', 'ra-option-admin.php', 'ra_option_admin', 'ra_theme_option');
qa_register_plugin_layer('ra-option-layer.php', 'RA Option Layer');

//load all addons
qw_load_addons();

//register addons language
if (qw_hook_exist('register_language')){
	$lang_file_array = qw_apply_filter('register_language', array());

	if(isset($lang_file_array) && is_array($lang_file_array)){
		foreach($lang_file_array as $key => $file){
			qa_register_phrases($file, $key);
		}
	}
}	
function get_base_url()
{
	/* First we need to get the protocol the website is using */
	$protocol = isset($_SERVER['HTTPS'] ) ? 'https://' : 'http://';

	$root = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
	if(substr($root, -1) == '/')$root = substr($root, 0, -1);
	$base = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, rtrim(QA_BASE_DIR, '/'));

		
	/* Returns localhost OR mysite.com */
	$host = $_SERVER['HTTP_HOST'];

	$url = $protocol . rtrim($host, '/') . '/' . str_replace($root, '', $base );
	
	return (substr($url, -1) == '/') ? substr($url, 0, -1) : $url;
}		

function qw_event_hook($event, $value = NULL, $callback = NULL, $check = false, $filter = false, $order = 100){
    static $events;
	
    // Adding or removing a callback?
    if($callback !== NULL){
        if($callback){
            $events[$event][$order][] = $callback;
        }else{
            unset($events[$event]);
        }
    }elseif($filter) // filter
    {	
		if(!isset($events[$event]) )
			return $value[1];
			
		ksort($events[$event]);
        foreach($events[$event] as $order){		
			foreach($order as $function){
				$filtered = call_user_func_array($function, $value);
				
				if(isset($filtered))
					$value[1] = $filtered;
				else
					$value[1] = $value[1];
			}			
        }
	
        return $value[1];
    }
	elseif($check && isset($events[$event])) // check if hook exist
    {
		ksort($events[$event]);
        foreach($events[$event] as $key => $order)
        {
			
			foreach($order as $function){
				if(is_array($function))
					return method_exists($function[0], $function[1] );
				return function_exists($function);
			}	
        }        
    }
    elseif(isset($events[$event])) // Fire do_action
    {				
		ksort($events[$event]);
        foreach($events[$event] as $order){
			//ob_start();
			$output = '';
			foreach($order as $function){
				
				$output .= call_user_func_array($function, $value);				
			}
			//$output = ob_get_clean();
        }
        return $output;
    }
	return false;
}

function qw_apply_filter(){
	$args = func_get_args();
	unset($args[0]);
	return qw_event_hook(func_get_arg(0), $args, NULL, false, true);
}
function qw_do_action(){
	$args = func_get_args();
	if(isset($args))
		unset($args[0]);

	return qw_event_hook(func_get_arg(0), $args, NULL);
}

function qw_add_filter(){
	$args = func_get_args();
	
	if(isset($args))
		$order = (count($args) > 2) ? end($args) : 100;
		
	qw_event_hook(func_get_arg(0), NULL, (isset($args[1]) ? $args[1] : ''), false, false, (isset($order) ? $order : 100));
}

function qw_add_action(){
	$args = func_get_args();
	
	if(isset($args))
		$order = (count($args) > 2) ? end($args) : 100;
		
	qw_event_hook(func_get_arg(0), NULL, (isset($args[1]) ? $args[1] : ''), false, false, (isset($order) ? $order : 100));
}

// an Alice for qw_event_hook 
function qw_hook_exist($event){
	return qw_event_hook($event, null, null, true);
}


function qw_read_addons(){
	$addons = array();
	//load files from addons folder
	$files=glob(QW_CONTROL_DIR.'/addons/*/addon.php');
	//print_r($files);
	foreach ($files as $file){
		$data = qw_get_addon_data($file);
		$data['folder'] = basename(dirname($file));
		$data['file'] = basename($file);
		$addons[] = $data;
	}
	return $addons;
}


function qw_load_addons(){
	$addons = qw_read_addons();
	if(!empty($addons))
		foreach($addons as $addon){
			include_once QW_CONTROL_DIR.'/addons/'.$addon['folder'].'/'.$addon['file'];
		}
}
function qw_load_addons_ajax(){
	$addons = qw_read_addons_ajax();
	if(!empty($addons))
		foreach($addons as $addon){			
			require_once QW_CONTROL_DIR.'/addons/'.$addon['folder'].'/'.$addon['file'];			
		}
}


function qw_get_addon_data( $plugin_file) {
	$plugin_data = qw_get_file_data( $plugin_file);

	return $plugin_data;
}

function qw_get_file_data( $file) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 1000 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	$metadata=qw_addon_metadata($file_data, array(
		'theme_name' => 'Theme Name',
		'theme_version' => 'Theme Version',
		'class' => 'Class',
		'description' => 'Description',
		'version' => 'Version',
		'author' => 'Author',
		'author_uri' => 'Author URI'
	));

	return $metadata;
}

function qw_addon_metadata($contents, $fields){
	$metadata=array();

	foreach ($fields as $key => $field)
		if (preg_match('/'.str_replace(' ', '[ \t]*', preg_quote($field, '/')).':[ \t]*([^\n\f]*)[\n\f]/i', $contents, $matches))
			$metadata[$key]=trim($matches[1]);
	
	return $metadata;
}


function qw_get_all_styles($template = 'none'){
	// short css
	$sort = qw_apply_filter('sort_enqueue_css', array('bootstrap', 'qw_admin'));	
	$css = qw_apply_filter('enqueue_css', array(), $template);
	return array_merge(array_flip( $sort ), $css);
}

function qw_get_all_scripts($template = 'none'){
	$sort = qw_apply_filter('sort_enqueue_scripts', array('jquery', 'bootstrap'));
	$scripts = qw_apply_filter('enqueue_scripts', array(), $template);
	return array_merge(array_flip( $sort ), $scripts);
}


	function ra_opt($name, $value=null)
	{
		/*
			Shortcut to get or set an option value without specifying database
		*/

		global $qa_options_cache;
		
		if ((!isset($value)) && isset($qa_options_cache[$name])){
			if (ra_is_serialized($qa_options_cache[$name]))
				return unserialize($qa_options_cache[$name]);
			return $qa_options_cache[$name]; // quick shortcut to reduce calls to qa_get_options()
		}
		require_once QA_INCLUDE_DIR.'qa-app-options.php';
		
		if (isset($value)){
			if (is_array($value))
				$value = serialize($value);
			qa_set_option($name, $value);	
		}
		$options=qa_get_options(array($name));

		if (ra_is_serialized($options[$name]))
			return unserialize($options[$name]);
		
		return $options[$name];
	}
	
	// check if data is serialized, if yes return true
	function ra_is_serialized($data){
		// if it isn't a string, it isn't serialized
		if ( !is_string( $data ) )
			return false;
		$data = trim( $data );
		if ( 'N;' == $data )
			return true;
		if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
			return false;
		switch ( $badions[1] ) {
			case 'a' :
			case 'O' :
			case 's' :
				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
					return true;
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
					return true;
				break;
		}
		return false;
	}
	
	function _ra_lang($str){
		global $qa_content;
		if(isset($qa_content['lang'][strtolower($str)]))
			return $qa_content['lang'][strtolower($str)];
		else
			return $str;
	}	
	function ra_lang($str){
		global $qa_content;
		if(isset($qa_content['lang'][strtolower($str)]))
			echo $qa_content['lang'][strtolower($str)];
		else
			echo $str;
	}
	// for including template file and returning output
	function ra_template_part($file_name, $context=null, $args = null){
		// $file_name without extension
		ob_start();
			include DUDE_THEME_DIR.'/'.$file_name.'.php';
		$output = ob_get_clean();
		
		return $output;	
	}
	
// theme logo
function ra_logo(){
	global $qa_content;
	if (strlen(qa_opt('ra_logo')))
		echo '<a href="'.qa_path_html('').'" class="logo image" title="'.qa_html(qa_opt('site_title')).'"><img src="'.qa_opt('ra_logo').'" /></a>';
	else
		echo '<a href="'.qa_path_html('').'" class="logo">'.qa_html(qa_opt('site_title')).'</a>';
}

// check if current page is home
function ra_is_home(){
	global $qa_content;
	return strlen($qa_content['script_var']['qa_request']) == 0;
}

// theme menu
function ra_nav($arg , $class='', $home=false, $mobile = false){
	$main = $arg['navigation']['main'];
	$subs = $arg['navigation']['ra_nav'];
	$o = '<ul class="'.$class.'">';
	
	$d_class = $mobile ? 'dl-submenu' : 'dropdown-menu';
	if($home)
		$o .= '<li'.(qa_request() == '' ? ' class="active"' : '').'><a href="'.qa_opt('site_url').'" title="'.qa_opt('site_title').'" class="home-anchor icon-home"></a></li>';
	

	foreach ($main as $k => $a){
		if ( $k != 'ask' && $k != 'admin'){
			$icon = (isset($a['icon']) ? $a['icon'] : '');
			$o .= '<li class="'.((isset($a['selected']) && $a['selected']==1) ? 'active ': '').(array_key_exists($k, $subs) ? 'dropdown': '').'"><a class="'.$icon.(array_key_exists($k, $subs) ? ' dropdown-toggle" data-toggle="dropdown': '').'" href="'.$a['url'].'"></i>'.$a['label'].'</a>';

			if(array_key_exists($k, $subs)){
				$o .= '<ul class="'.$d_class.'">';
				if($mobile)
					$o .= '<li class="dl-back"><a href="#">back</a></li>';
				foreach ($subs[$k] as $sub){
					$o .= '<li class="'.(isset($sub['selected']) && $sub['selected']==1 ? 'active ': '').'"><a  href="'.$sub['url'].'">'.$sub['label'].'</a></li>';
				}
				$o .= '</ul>';
			}
			$o .= '</li>';
		}
	}
	
	$o .= '</ul>';
	return $o;	
}


function ra_answered_nav($sort, $categoryslugs){
	global $qa_content;
	$request='questions';

	if (isset($categoryslugs) && isset($qa_content['navigation']['main']['questions']['selected']))
		foreach ($categoryslugs as $slug)
			$request.='/'.$slug;

	$navigation=array(
		'recent' => array(
			'label' => qa_lang('main/nav_most_recent'),
			'url' => qa_path_html($request),
		),
		
		'hot' => array(
			'label' => qa_lang('main/nav_hot'),
			'url' => qa_path_html($request, array('sort' => 'hot')),
		),
		
		'votes' => array(
			'label' => qa_lang('main/nav_most_votes'),
			'url' => qa_path_html($request, array('sort' => 'votes')),
		),

		'answers' => array(
			'label' => qa_lang('main/nav_most_answers'),
			'url' => qa_path_html($request, array('sort' => 'answers')),
		),

		'views' => array(
			'label' => qa_lang('main/nav_most_views'),
			'url' => qa_path_html($request, array('sort' => 'views')),
		),
	);
	
	if (isset($navigation[$sort]))
		$navigation[$sort]['selected']=true;
	
	if (!qa_opt('do_count_q_views'))
		unset($navigation['views']);
	
	return $navigation;
}

function ra_unanswered_nav($by,$categoryslugs){
		global $qa_content;
		$request='unanswered';

		if (isset($categoryslugs) && isset($qa_content['navigation']['main']['unanswered']['selected']))
			foreach ($categoryslugs as $slug)
				$request.='/'.$slug;
		
		$navigation=array(
			'by-answers' => array(
				'label' => qa_lang('main/nav_no_answer'),
				'url' => qa_path_html($request),
			),
			
			'by-selected' => array(
				'label' => qa_lang('main/nav_no_selected_answer'),
				'url' => qa_path_html($request, array('by' => 'selected')),
			),
			
			'by-upvotes' => array(
				'label' => qa_lang('main/nav_no_upvoted_answer'),
				'url' => qa_path_html($request, array('by' => 'upvotes')),
			),
		);
		
		if (isset($navigation['by-'.$by]))
			$navigation['by-'.$by]['selected']=true;
			
		if (!qa_opt('voting_on_as'))
			unset($navigation['by-upvotes']);

		return $navigation;

}



function ra_sub_nav(){
	//require_once QA_INCLUDE_DIR.'qa-app-q-list.php';
	require_once QA_INCLUDE_DIR.'qa-app-admin.php';
	
	$m = array();
	$handle=qa_get_logged_in_handle();
	$categoryslugs=qa_request_parts(1);
	$countslugs=count($categoryslugs);
	$sort=($countslugs && !QA_ALLOW_UNINDEXED_QUERIES) ? null : qa_get('sort');
	$by=qa_get('by');

	$forfavorites=qa_get('show')!='content';
	$forcontent=qa_get('show')!='favorites';

	//$m['account']=qa_account_sub_navigation();
	$m['admin']=qa_admin_sub_navigation();
	$m['questions']=ra_answered_nav($sort, $categoryslugs);
		
	$m['unanswered']=ra_unanswered_nav($by, $categoryslugs);
	$m['updates']=array(
		'all' => array(
			'label' => qa_lang_html('misc/nav_all_my_updates'),
			'url' => qa_path_html('updates'),
			'selected' => $forfavorites && $forcontent,
		),
		
		'favorites' => array(
			'label' => qa_lang_html('misc/nav_my_favorites'),
			'url' => qa_path_html('updates', array('show' => 'favorites')),
			'selected' => $forfavorites && !$forcontent,
		),
		
		'myposts' => array(
			'label' => qa_lang_html('misc/nav_my_content'),
			'url' => qa_path_html('updates', array('show' => 'content')),
			'selected' => $forcontent && !$forfavorites,
		),
	);
	//$m['activity']=qa_user_sub_navigation($handle, 'activity');
	//$m['answers']=qa_user_sub_navigation($handle, 'answers');
	//$m['profile']=qa_user_sub_navigation($handle, 'profile');
	//$m['users']['questions']=qa_user_sub_navigation($handle, 'questions');
	//$m['users']['wall']=qa_user_sub_navigation($handle, 'wall');
	
	if ((!QA_FINAL_EXTERNAL_USERS) && (qa_get_logged_in_level()>=QA_USER_LEVEL_MODERATOR)) {
		$m['user'] = array(
				'users$' => array(
					'url' => qa_path_html('users'),
					'label' => qa_lang_html('main/highest_users'),
				),
	
				'users/special' => array(
					'label' => qa_lang('users/special_users'),
					'url' => qa_path_html('users/special'),
				),
	
				'users/blocked' => array(
					'label' => qa_lang('users/blocked_users'),
					'url' => qa_path_html('users/blocked'),
				),
			);
		
	}
	
	return $m;
}



function ra_installed_plugin(){
	$tables=qa_db_list_tables_lc();
	$moduletypes=qa_list_module_types();
	$pluginfiles=glob(QA_PLUGIN_DIR.'*/qa-plugin.php');
	
	foreach ($moduletypes as $type) {
		$modules=qa_load_modules_with($type, 'init_queries');

		foreach ($modules as $name => $module) {
			$queries=$module->init_queries($tables);
		
			if (!empty($queries)) {
				if (qa_is_http_post())
					qa_redirect('install');
				
				else
					$qa_content['error']=strtr(qa_lang_html('admin/module_x_database_init'), array(
						'^1' => qa_html($name),
						'^2' => qa_html($type),
						'^3' => '<a href="'.qa_path_html('install').'">',
						'^4' => '</a>',
					));
			}
		}
	}
	
	if ( qa_is_http_post() && !qa_check_form_security_code('admin/plugins', qa_post_text('qa_form_security_code')) ) {
		$qa_content['error']=qa_lang_html('misc/form_security_reload');
		$showpluginforms=false;
	} else
		$showpluginforms=true;
	$plugin = array();	
	if (count($pluginfiles)) {
		foreach ($pluginfiles as $pluginindex => $pluginfile) {
			$plugindirectory=dirname($pluginfile).'/';
			$hash=qa_admin_plugin_directory_hash($plugindirectory);
			$showthisform=$showpluginforms && (qa_get('show')==$hash);
			
			$contents=file_get_contents($pluginfile);
			
			$metadata=qa_admin_addon_metadata($contents, array(
				'name' => 'Plugin Name',
				'uri' => 'Plugin URI',
				'description' => 'Plugin Description',
				'version' => 'Plugin Version',
				'date' => 'Plugin Date',
				'author' => 'Plugin Author',
				'author_uri' => 'Plugin Author URI',
				'license' => 'Plugin License',
				'min_q2a' => 'Plugin Minimum Question2Answer Version',
				'min_php' => 'Plugin Minimum PHP Version',
				'update' => 'Plugin Update Check URI',
			));
			
			if (strlen(@$metadata['name']))
				$namehtml=qa_html($metadata['name']);
			else
				$namehtml=qa_lang_html('admin/unnamed_plugin');
			
			$plugin_name = $namehtml;	
			
			if (strlen(@$metadata['uri'])){
				$plugin_uri = qa_html($metadata['uri']);
			}
			
			
			if (strlen(@$metadata['version']))
				$plugin_version=qa_html($metadata['version']);
				
			if (strlen(@$metadata['author'])) {
				$plugin_author=qa_html($metadata['author']);
				
				if (strlen(@$metadata['author_uri']))
					$plugin_author_url=qa_html($metadata['author_uri']);
		
			} 
			
			if (strlen(@$metadata['version']) && strlen(@$metadata['update'])) {
				$elementid='version_check_'.md5($plugindirectory);
				
				$plugin_update='(<span id="'.$elementid.'"></span>)';
				
				$qa_content['script_onloads'][]=array(
					"qa_version_check(".qa_js($metadata['update']).", 'Plugin Version', ".qa_js($metadata['version'], true).", 'Plugin URI', ".qa_js($elementid).");"
				);

			}
			
			if (strlen(@$metadata['description']))
				$plugin_description=qa_html($metadata['description']);
			
			
			//if (isset($pluginoptionmodules[$plugindirectory]))			
				$plugin_option =qa_admin_plugin_options_path($plugindirectory);
				
			if (qa_qa_version_below(@$metadata['min_q2a']))
				$plugin_error=qa_lang_html_sub('admin/requires_q2a_version', qa_html($metadata['min_q2a']));
					
			elseif (qa_php_version_below(@$metadata['min_php']))
				$plugin_error = qa_lang_html_sub('admin/requires_php_version', qa_html($metadata['min_php']));

			$plugin[] = array(
				'tags' => 'id="'.qa_html($hash).'"',
				'name' => @$plugin_name,
				'uri' => @$plugin_uri,
				'version' => @$plugin_version,
				'author' => @$plugin_author,
				'author_url' => @$plugin_author_url,
				'update' => @$plugin_update,
				'description' => @$plugin_description,
				'path' => @$plugindirectory,
				'option' => @$plugin_option,
				'error' => @$plugin_error,
				'fields' => array(
					array(
						'type' => 'custom',
					)
				),
			);

		}
		
	}
	return $plugin;
}	
function ra_upload_cover($name){
	if (isset($_FILES[$name]['name']) && (getimagesize($_FILES[$name]['tmp_name']) >0) && $_FILES[$name]['size'] > 0 && ($_FILES[$name]['size'] < 1048576)){
		
			require_once DUDE_THEME_DIR.'/inc/class.Images.php';
			require_once QA_INCLUDE_DIR.'qa-db-users.php';
			
			if(!file_exists(QA_BASE_DIR.'images/'))
				mkdir(QA_BASE_DIR.'images/');
			
			$uploaddir 	= QA_BASE_DIR.'images/';
			$ext = pathinfo( $_FILES[$name]['name'], PATHINFO_EXTENSION);
			$file_name = md5(time().uniqid());
			$temp_name = $file_name.'_temp';
			$temp_name_with_ext =$file_name.'_temp'.$ext;
			$file_name_with_ext = $file_name .'.'.$ext;
			move_uploaded_file($_FILES[$name]['tmp_name'], $uploaddir.$temp_name_with_ext);
			
			$image = new Image($uploaddir.$temp_name_with_ext);
			
			$image->resize(621, 300, 'crop', 'c', 't', 99);
			$image->save($file_name, $uploaddir);
			
			$thumb = new Image($uploaddir.$temp_name_with_ext);
			$thumb->resize(278, 120, 'crop', 'c', 't', 99);
			$thumb->save($file_name.'_s', $uploaddir);
			
			unlink ($uploaddir.$temp_name_with_ext); 
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				$prev_file = get_user_meta( qa_get_logged_in_handle(), 'cover' );;
			}else{
				$prev_file = ra_user_profile(qa_get_logged_in_handle(), 'cover');
			}
			
			if (strlen($prev_file)){	
			
				$delete 			= $uploaddir.$prev_file;
				$prev_file_name 	= explode('.', $prev_file);
				if (file_exists($delete))  
					unlink ($delete); 
				if (file_exists($uploaddir.$prev_file_name[0].'_s.'.$prev_file_name[1]))  
					unlink ($uploaddir.$prev_file_name[0].'_s.'.$prev_file_name[1]); 
			}
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				update_user_meta( qa_get_logged_in_userid(), 'cover', $file_name_with_ext);
			}else{
				qa_db_user_profile_set(qa_get_logged_in_userid(), 'cover', $file_name_with_ext) ;
			}

	}else{
		global $ra_error;
		if($_FILES[$name]['size'] > 1048576)
			$ra_error = _ra_lang('Upload failed, image size is bigger then 1MB');
		else
			$ra_error = _ra_lang('An error occurred when uploading, please try again.');
	}
}
function ra_readdir($path){
	$path = DUDE_THEME_DIR.'/'.$path;
	foreach(array_diff(scandir($path), array('.', '..')) as $f) 
		if (is_file($path . '/' . $f) && (('.php') ? preg_match('/.php/' , $f) : 1)) 
			$l[] = str_replace('.php', '', $f);
	return $l; 
}
function ra_list_theme(){
	if(strlen(qa_opt('ra_list_layout')))
		return qa_opt('ra_list_layout');
	else
		return 'default';
}
function ra_home_theme(){

	if(ra_is_home() && strlen(qa_opt('ra_home_layout')))
		return '/home/'.qa_opt('ra_home_layout');
	else
		return '/index';
}

/* ra_functions */
		
function ra_user_profile($handle, $field =NULL){
	$userid = qa_handle_to_userid($handle);
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		return get_user_meta( $userid );
	}else{
		$query = qa_db_select_with_pending(qa_db_user_profile_selectspec($userid, true));
		
		if(!$field) return $query;
		if (isset($query[$field]))
			return $query[$field];
	}
	
	return false;
}	
function ra_user_data($handle){
	$userid = qa_handle_to_userid($handle);
	$identifier=QA_FINAL_EXTERNAL_USERS ? $userid : $handle;
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		
		$u=qa_db_select_with_pending( 
			qa_db_user_rank_selectspec($handle),
			qa_db_user_points_selectspec($identifier)
		);
		$user = array();
		$user[]['points'] = $u[1]['points'];
		unset($u[1]['points']);
		$user[] = 0;
		$user[] = $u[1];
	}else{
		$user=qa_db_select_with_pending( 
			qa_db_user_account_selectspec($userid, true),
			qa_db_user_rank_selectspec($handle),
			qa_db_user_points_selectspec($identifier)
		);
	}
	return $user;
}	

function ra_q_title($post){
	echo '<a href="'.ra_post_link($post['raw']['postid']).'" title="'._ra_lang('bookmark this!').'">'.htmlspecialchars($post['raw']['title']).'</a>';
}

function ra_fav_count($post){
	echo $post['raw']['userfavoriteq'];
}
function ra_less_chk($name, $default){
	return strlen(qa_opt($name)) ? qa_opt($name) : $default;
}

function ra_user_badge($handle) {
	if(qa_opt('badge_active')){
	$userids = qa_handles_to_userids(array($handle));
	$userid = $userids[$handle];

	
	// displays small badge widget, suitable for meta
	
	$result = qa_db_read_all_values(
		qa_db_query_sub(
			'SELECT badge_slug FROM ^userbadges WHERE user_id=#',
			$userid
		)
	);

	if(count($result) == 0) return;
	
	$badges = qa_get_badge_list();
	foreach($result as $slug) {
		$bcount[$badges[$slug]['type']] = isset($bcount[$badges[$slug]['type']])?$bcount[$badges[$slug]['type']]+1:1; 
	}
	$output='<ul class="user-badge clearfix">';
	for($x = 2; $x >= 0; $x--) {
		if(!isset($bcount[$x])) continue;
		$count = $bcount[$x];
		if($count == 0) continue;

		$type = qa_get_badge_type($x);
		$types = $type['slug'];
		$typed = $type['name'];

		$output.='<li class="badge-medal '.$types.'"><i class="icon-badge" title="'.$count.' '.$typed.'"></i><span class="badge-pointer badge-'.$types.'-count" title="'.$count.' '.$typed.'"> '.$count.'</span></li>';
	}
	$output = substr($output,0,-1);  // lazy remove space
	$output.='</ul>';
	return($output);
	}
}

/* Get handle avatar */
function ra_get_avatar($handle, $size = 40, $html =true){
$userid = qa_handle_to_userid($handle);
	if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
		$img_html = get_avatar( qa_get_user_email($userid), $size);
	}else if(QA_FINAL_EXTERNAL_USERS){
		$img_html = qa_get_external_avatar_html($userid, $size, false);
	}else{
		if (!isset($handle)){

			if ( qa_opt('avatar_allow_upload') && qa_opt('avatar_default_show') && strlen(qa_opt('avatar_default_blobid')) )
				$img = qa_opt('avatar_default_blobid');
			else
				$img = '';
		}else{
			$f = ra_user_data($handle);
			
			if(empty($f[0]['avatarblobid'])){
				if ( qa_opt('avatar_allow_upload') && qa_opt('avatar_default_show') && strlen(qa_opt('avatar_default_blobid')) )
					$img = qa_opt('avatar_default_blobid');
				else
					$img = '';
			} else
				$img = $f[0]['avatarblobid'];
		}

	}
	if (empty($img))
		return;

	if($html)
		return '<a href="'.qa_path_absolute('user/'.$handle).'"><img src="'.qa_path_absolute('', array('qa' => 'image', 'qa_blobid' => $img, 'qa_size' => $size)).'" /></a>';		
	elseif(!empty($img))
		return qa_path_absolute('', array('qa' => 'image', 'qa_blobid' => $img, 'qa_size' => $size));
}
function ra_post_link($id){
	$type = mysql_result(qa_db_query_sub('SELECT type FROM ^posts WHERE postid = "'.$id.'"'), 0);
	
	if($type == 'A')
		$id = mysql_result(qa_db_query_sub('SELECT parentid FROM ^posts WHERE postid = "'.$id.'"'),0);
	
	$post = qa_db_query_sub('SELECT title FROM ^posts WHERE postid = "'.$id.'"');
	return qa_q_path_html($id, mysql_result($post,0));

}	
function ra_user_points($handle){
	$userid = qa_handle_to_userid($handle);
	$db = qa_db_select_with_pending(qa_db_user_points_selectspec($userid, true));
	return strlen($db['points']) ? $db['points'] : '0';
}

function ra_tag_list($limit = 20){
	$populartags=qa_db_single_select(qa_db_popular_tags_selectspec(0, $limit));
			
	$i= 1;
	foreach ($populartags as $tag => $count) {							
		echo '<li><a class="icon-tag" href="'.qa_path_html('tag/'.$tag).'">'.qa_html($tag).'<span>'.filter_var($count, FILTER_SANITIZE_NUMBER_INT).'</span></a></li>';
	}
}
function ra_cat_list($limit = 15){
	$categoryslugs=qa_request_parts(1);	
	$cats = qa_db_select_with_pending(qa_db_category_nav_selectspec($categoryslugs, false, false, true));
	$cats = qa_category_navigation($cats);
	$output = '<ul class="ra-cat-list clearfix">';
	$i=1;
	foreach ($cats as $k => $c){
		if($k != 'all'){
			$output .= '<li><a class="icon-folder-close" title="'.@$c['popup'].'" href="'.$c['url'].'">'.$c['label'].'<span>'.filter_var($c['note'], FILTER_SANITIZE_NUMBER_INT).'</span></a></li>';
			if($limit == $i)break;
			$i++;
		}
	}
	$output .= '</ul>';
	echo $output;
}

/* function ra_sanitize($str){
	$str = htmlentities($str);
	$str = strip_tags($str);
	$str = addslashes($str);
	return $str;
} */



function ra_edit_mode(){
	if (isset($_REQUEST['edit_mode']))
		return true;
	return;
}


function ra_is_admin(){
	if (qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN)
		return true;
	return false;
}

function ra_is_ajax(){
	if(isset($_REQUEST['ra_ajax']))
		return true;
	return false;
}


function ra_widget($name, $t){
	$module	=	qa_load_module('widget', $name);
	$module->output_widget('side', 'top', $t, $t->template, $t->request, $t->content);
}


function ra_return_echo($func) {
    ob_start();
    $func;
    return ob_get_clean();
}

function ra_post_type($id){
	$result = qa_db_read_one_value(qa_db_query_sub('SELECT type FROM ^posts WHERE postid=#', $id ),true);
	return $result;
}

function ra_post_notice($item){
	// this will return a notice whether question is open, closed, duplicate or solved
	
	if (@$item['answer_selected'] || @$item['raw']['selchildid']){	
		$notice =   '<span class="post-status selected">'._ra_lang('Solved').'</span>' ;
	}elseif(@$item['raw']['closedbyid']){
		$type = ra_post_type(@$item['raw']['closedbyid']);
		if($type == 'Q')
			$notice =   '<span class="post-status duplicate">'._ra_lang('Duplicate').'</span>' ;	
		else
			$notice =   '<span class="post-status closed">'._ra_lang('Closed').'</span>' ;	
	}else{
		$notice =   '<span class="post-status open">'._ra_lang('Open').'</span>' ;	
	}
	return $notice;
}

function ra_cat_path($categorybackpath){
	return qa_path_html(implode('/', array_reverse(explode('/', $categorybackpath))));
}

function ra_answer_selected($id){
	$result = qa_db_read_one_value(qa_db_query_sub('SELECT selchildid FROM ^posts WHERE postid=#', $id ),true);
	return $result;
}

function ra_url_grabber($str) {
	preg_match_all(
	  '#<a\s
		(?:(?= [^>]* href="   (?P<href>  [^"]*) ")|)
		(?:(?= [^>]* title="  (?P<title> [^"]*) ")|)
		(?:(?= [^>]* target=" (?P<target>[^"]*) ")|)
		[^>]*>
		(?P<text>[^<]*)
		</a>
	  #xi',
	  $str,
	  $matches,
	  PREG_SET_ORDER
	);
	

	foreach($matches as $match) {
	 return '<a href="'.$match['href'].'" title="'.$match['title'].'">'.$match['text'].'</a>';
	}	
}
function ra_user_followers_count($handle){
	$userid = qa_handle_to_userid($handle);
	$count =  qa_db_read_one_value(qa_db_query_sub('SELECT count(userid) FROM ^userfavorites  WHERE  entityid = # and entitytype = "U"', $userid), true);
	echo $count;
}
function ra_user_followers($handle){
	$userid = qa_handle_to_userid($handle);
	$followers = qa_db_read_all_values(qa_db_query_sub('SELECT ^users.handle FROM ^userfavorites, ^users  WHERE (^userfavorites.userid = ^users.userid and ^userfavorites.entityid = #) and ^userfavorites.entitytype = "U" LIMIT 12', $userid));	

	
	if(count($followers)){
		$output = '<div class="user-followsers widget">';
		$output .= '<h3 class="widget-title">'._ra_lang('Followers').'<span class="counts">'.count($followers).'</span></h3>';
		$output .= '<ul class="user-followers clearfix">';
		foreach($followers as $user){
			$id = qa_handle_to_userid($user);
			$output .= '<li><div class="avatar" data-handle="'.$user.'" data-id="'.$id.'"><a href="'.qa_path_html('user/'.$user).'"><img src="'.ra_get_avatar($user, 50, false).'" /></a></div></li>';
		}
		$output .= '</ul>';
		$output .= '</div>';
		echo $output;
	}
	return;
}

function ra_ajax_upload_cover(){
	?>
	<div id="cover-uploader" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
	<form method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?php ra_lang('Upload cover'); ?></h4>
      </div>
      <div class="modal-body">
        
			<label for="cover"><?php ra_lang('File name'); ?></label>
			<input type="file" name="cover" id="cover">
			<input type="hidden" name="code" value="<?php echo qa_get_form_security_code('upload_cover') ?>">
		
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php ra_lang('Close'); ?></button>
        <button type="submit" class="btn btn-primary"><?php ra_lang('Save changes'); ?></button>
      </div>
	  </form>
    </div>
  </div>
</div>

		
	<?php
	die();
}

function ra_ajax_user_popover(){
	
	$handle_id= qa_post_text('handle');
	$handle= qa_post_text('handle');
	require_once QA_INCLUDE_DIR.'qa-db-users.php';
	if(isset($handle)){
		$userid = qa_handle_to_userid($handle);
		$badges = ra_user_badge($handle);
		
		if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
			$userid = qa_handle_to_userid($handle);
			$cover = get_user_meta( $userid, 'cover' );
			$cover = $cover[0];
		}else{
			$profile = ra_user_profile($handle);
			$cover = @$profile['cover'];
		}

		if(isset($cover)){
			$image_file = explode('.',$cover);
			$cover = 'style="background:url('.qa_opt('site_url').'images/'.@$image_file[0].'_s.'.$image_file[1].') no-repeat scroll 0 0 / cover;"';
		}
		?>
		<div id="<?php echo $userid;?>_popover" class="user-popover">
			<div class="cover" <?php echo @$cover ?>>
				<div class="avatar pull-left"><?php echo ra_get_avatar($handle, 50); ?></div>
			</div>
			<div class="bar">		
				<span class="followers-count pull-right icon-star btn btn-warning"><?php echo ra_user_followers_count($handle); ?></span>
				<h3 class="name"><?php echo ra_name($handle); ?></h3>				
			</div>
			<div class="extra clearfix">
				<div class="points">
					<?php echo '<span>'.ra_user_points($handle).'</span>'._ra_lang('P'); ?>
				</div>
				<div class="badges">
					<?php echo $badges; ?>
				</div>
			</div>
		</div>	
		<?php
	}
	die();
}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

/*
 * Function to import SQL for a given $file
 */
function import_sql($file, $delimiter = ';') {
        $handle = fopen($file, 'r');
        $sql = '';

        if($handle) {
                /*
                 * Loop through each line and build
                 * the SQL query until it detects the delimiter
                 */
                while(($line = fgets($handle, 4096)) !== false) {
                        $sql .= trim(' ' . trim($line));
                        if(substr($sql, -strlen($delimiter)) == $delimiter) {
                                qa_db_query_sub($sql);
                                $sql = '';
                        }
                }

                fclose($handle);
        }
}

// quick answer
function ra_ajax_add_answer(){

	//	Load relevant information about this question

	$questionid=qa_post_text('a_questionid');
	$userid=qa_get_logged_in_userid();
	
	list($question, $childposts)=qa_db_select_with_pending(
		qa_db_full_post_selectspec($userid, $questionid),
		qa_db_full_child_posts_selectspec($userid, $questionid)
	);


//	Check if the question exists, is not closed, and whether the user has permission to do this

	if ((@$question['basetype']=='Q') && (!isset($question['closedbyid'])) && !qa_user_post_permit_error('permit_post_a', $question, QA_LIMIT_ANSWERS)) {
		require_once QA_INCLUDE_DIR.'qa-app-captcha.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		require_once QA_INCLUDE_DIR.'qa-app-post-create.php';
		require_once QA_INCLUDE_DIR.'qa-app-cookies.php';
		require_once QA_INCLUDE_DIR.'qa-page-question-view.php';
		require_once QA_INCLUDE_DIR.'qa-page-question-submit.php';


	//	Try to create the new answer
	
		$usecaptcha=qa_user_use_captcha(qa_user_level_for_post($question));
		$answers=qa_page_q_load_as($question, $childposts);
		$answerid=qa_page_q_add_a_submit($question, $answers, false, $in, $errors);
		
		if($answerid) return true;
		
	}
	die();
}

function ra_get_cat_desc($slug){
	$result = qa_db_read_one_assoc(qa_db_query_sub('SELECT title,content FROM ^categories WHERE tags=$', $slug ),true);
	return $result;
}

function ra_name($handle){
	if(qa_opt('show_real_name'))
		return strlen(ra_user_profile($handle, 'name')) ? ra_user_profile($handle, 'name') : $handle;
	else
		return $handle;
}

function ra_current_url() {
  $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  $url .= $_SERVER["REQUEST_URI"];
  return $url;
}

function qw_truncate($string, $limit, $pad="...") {
	if(strlen($string) <= $limit) 
		return $string; 
	else{ 
		//preg_match('/^.{1,'.$limit.'}\b/s', $string, $match);
		//return $match[0].$pad;
		$text = $string.' ';
		$text = substr($text,0,$limit);
		$text = substr($text,0,strrpos($text,' '));
		return $text.$pad;
	} 
}

/*
  This function sets and invokes the timeout
  $time_out => always a positive value in seconds
 */

function qw_scheduler($function_name, $time_out = NULL, $params = NULL) {
 	  require_once QA_INCLUDE_DIR . 'qa-app-options.php';
      require_once QA_INCLUDE_DIR . 'qa-db.php';
      //first check $time_out == 0 , then check timeout and set the current rundate 
      if (!$function_name) {
            return;
      }

	  $time_out_opt_name      =  $function_name . '_time_out';
	  $last_run_date_opt_name =  $function_name . '_last_run_date';

      if ($time_out === NULL || !$time_out) {
            //the call is for invoke the timeout function 
            $time_out_val = qa_opt($time_out_opt_name);
            if (!!$time_out_val && is_numeric($time_out_val) && $time_out_val > 0) { //check if the $time_out_value for this function is set in the options or not 
                  $date_format = "d/m/Y H:i:s";
                  $last_run_date = qa_opt($last_run_date_opt_name);
                  if (!$last_run_date) {
                        // if the lastrun_date is not set then set with an default value 
                        $last_run_date = "01/01/2014 01:00:00";
                  }
                  $event_interval = "PT" . $time_out_val . "S";
                  // $last_run_date = new DateTime($last_run_date);
                  $last_run_date = date_create_from_format($date_format, $last_run_date);
                  $last_run_date->add(new DateInterval($event_interval));
                  $probable_run_date = $last_run_date;
                  //get the current time 
                  $current_time = new DateTime("now");

                  //if current time is grater than last_rundate + interval then 
                  if ($current_time > $probable_run_date) {
                  	 // update the last rundate to make sure it handles concurrent requests 
                        qa_opt($last_run_date_opt_name, $current_time->format($date_format));
                        // once lastrundate  is updated , call the callback function to do the action  
                        $value = call_user_func($function_name, $params);
                        return $value;
                  }
            } else {
                  //this executes if the timeout is not set but it is invoked for the first time 
                  // then set with default timeout 
                  $time_out = 15 * 60; //15 mins 
                  qa_opt($time_out_opt_name, $time_out);
            }
      } else {
            //it is to set the timeout 
            if (!(is_numeric($time_out) && $time_out > 0 )) {
                  // if the $time_out is not a numeric value or not grater than 0 , then return 
                  return;
            }
            qa_opt($time_out_opt_name, $time_out);
      }

      //first check the timeout for the function name 
}

function qw_check_scheduler($function_name, $params = NULL) {
      if ($params !== NULL) {
            qw_scheduler($function_name, NULL, $params);
      } else {
            qw_scheduler($function_name);
      }
}

function qw_scheduler_set($function_name, $time_out = NULL) {
      if ($time_out !== NULL && is_numeric($time_out) && $time_out > 0) {
            qw_scheduler($function_name, $time_out);
      }
}

// functions for testing of the qw_scheduler_set
function call_me() {
      $current_time = new DateTime("now");
      $date_format = "d/m/Y H:i:s";
}

function call_this_method() {
      // this way we can set the scheduler 
      // qw_scheduler_set('call_me', 20);
	  // execute the scheduler 
      qw_check_scheduler('call_me');
}

function qw_log($string) {
  // if (qa_opt('event_logger_to_files')) {
            //   Open, lock, write, unlock, close (to prevent interference between multiple writes)
            $directory = QW_CONTROL_DIR.'/logs/';

            if (substr($directory, -1) != '/') $directory.='/';

            $log_file_name = $directory . 'cs-log-' . date('Y\-m\-d') . '.txt';

            $log_file_exists = file_exists($log_file_name);

            $log_file = @fopen($log_file_name, 'a');
            if (is_resource($log_file) && (!!$log_file_exists)) {
                  if (flock($log_file, LOCK_EX)) {
                        fwrite($log_file, $string . PHP_EOL);
                        flock($log_file, LOCK_UN);
                  }
            }
            @fclose($log_file);
      //}
}

function qw_event_log_row_parser( $row ){
            $result = preg_split('/\t/', $row) ;
            $param = array();
            $embeded_arrays = array();

            foreach ( $result as $value ) {
                  $arr_elem = explode("=", $value ) ;
                  $arr_elem_0 = (isset($arr_elem[0])) ? $arr_elem[0] : "" ;
                  $arr_elem_1 = (isset($arr_elem[1])) ? $arr_elem[1] : "" ;
                  
                  if(!$arr_elem_0) continue ;

                  $param[$arr_elem_0] = $arr_elem_1 ;
                  if (preg_match("/array(.)/", $arr_elem_1)) {
                       $embeded_arrays[] = $arr_elem_0; 
                  }
            }
            $unset_keys = array();
            foreach ($embeded_arrays as $embeded_array) {
                  $param[$embeded_array] = array() ; 
                  foreach ($param as $key => $value) {
                        if (preg_match("/".$embeded_array."_./", $key)) {
                        	  $str = preg_split("/".$embeded_array."_/", $key ) ;
                              $new_key = $str[1] ;
                              $param[$embeded_array][$new_key] = $value ;
                              $unset_keys[] = $key ;
                        }
                  }
            }
            foreach ($unset_keys as $key) {
                  unset($param[$key]);
            }
            return $param ; 
}
//just a helper methos for Testing
function qw_event_log_reader()
{     
      return qa_db_read_one_value(qa_db_query_sub("SELECT ^eventlog.params from  ^eventlog WHERE ^eventlog.datetime = $ ", '2014-05-10 22:55:08'), true);
}

function qw_is_internal_link($link){
	$link_host = parse_url($link, PHP_URL_HOST);
	if( $link_host == $_SERVER['HTTP_HOST'])
		return true;
		
	return false;
}

function qw_get_all_notification_settings()
{
   $key = 'qw_notification_settings' ;
   $values = qa_db_read_all_assoc(qa_db_query_sub("SELECT ^userprofile.userid , ^userprofile.content as settings from  ^userprofile WHERE ^userprofile.title = #" , $key ));
   foreach ($values as &$value) {
            $value['settings'] = json_decode($value['settings'] , true );
   }
   return $values ;
}

function qw_check_pref_for_event($userid , $event , $all_preferences='' )
{
      if (!$all_preferences) {
            $all_preferences = qw_get_all_notification_settings();
      }
      $event = 'qw_mail_when_'.$event  ;
      foreach ($all_preferences as $preferences) {
         if ($preferences['userid']==$userid) {
               return @$preferences['settings'][$event];
         }
      }   
      return false;
}

/*
	Omit PHP closing tag to help avoid accidental output
*/