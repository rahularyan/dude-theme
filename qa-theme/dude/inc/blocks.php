<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* theme class override */
	
	class qa_html_theme extends qa_html_theme_base
	{			
		 
		function doctype(){	
			// add ra_nav to $this->content
			$this->content['navigation']['ra_nav'] = ra_sub_nav();
			include( DUDE_THEME_DIR.'/inc/menu_icon.php'); // menu icons
		}
		function html()	{		
			if ($this->template =='admin')
				$this->output_raw(ra_template_part('admin/head', $this));
			else
				$this->output_raw(ra_template_part('head', $this));
			
			//$this->head();
			$this->body();
			if ($this->template =='admin')
				$this->output_raw(ra_template_part('admin/footer', $this));
			else
				$this->output_raw(ra_template_part('footer', $this));	
			
		}
		function head()
		{
			$this->output(
				
				'<meta http-equiv="content-type" content="'.$this->content['content_type'].'"/>'
			);
			
			$this->head_title();
			
			$this->head_metas();
			$this->head_css();
			$this->head_links();
			$this->head_lines();
			$this->head_script();
			$this->head_custom();
		}
		function ra_builder_css()
		{
			
			// dont remove this, unless you have purchased licence removal
			$this->output('<meta name="generator" content="rahularyan">');
			if($this->template == 'user'){
				if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
					$id = $this->content['raw']['userid'];
					$cover = get_user_meta( $id, 'cover' );
					$cover = $cover[0];
				}else{
					@$cover = ra_user_profile(@$this->content['raw']['account']['handle'], 'cover');
				}
				if($cover)
					$this->output('<style>#user .user-bar{background:url("'.qa_opt('site_url').'images/'.$cover.'") no-repeat scroll 0 0 / cover;}</style>');
				
			}
			if(ra_is_home()){					
				$this->output('<style>'.ra_db_builder('css_home').ra_db_builder('css_bottom').'</style>');
			}elseif($this->template == 'questions'){					
				$this->output('<style>'.ra_db_builder('css_questions').ra_db_builder('css_bottom').'</style>');
			}elseif($this->template == 'unanswered'){				
				$this->output('<style>'.ra_db_builder('css_unanswered').ra_db_builder('css_bottom').'</style>');
			}elseif($this->template == 'tags'){					
				$this->output('<style>'.ra_db_builder('css_tags').ra_db_builder('css_bottom').'</style>');
			}elseif($this->template == 'categories'){					
				$this->output('<style>'.ra_db_builder('css_categories').ra_db_builder('css_bottom').'</style>');
			}elseif($this->template == 'users'){				
				$this->output('<style>'.ra_db_builder('css_users').ra_db_builder('css_bottom').'</style>');
			}elseif($this->template == 'user'){				
				$this->output('<style>'.ra_db_builder('css_user').ra_db_builder('css_bottom').'</style>');
			}
		}
		
		function body(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->body_header();			
			$this->body_prefix();
			$this->body_script();
			
			$this->notices();
			
			if ($this->template =='admin'){
				
				$this->output('<div id="admin">');
					if (qa_get_logged_in_level()>=QA_USER_LEVEL_MODERATOR){
						$this->output_raw(ra_template_part('admin/nav', $this));
						$this->output_raw(ra_template_part('admin/index', $this));
					}else{
						$this->output_raw(ra_template_part('no_access', $this));
					}
				$this->output('</div>');

			}else{
				$flags=qa_get_logged_in_flags();
				if ( ($flags & QA_USER_FLAGS_MUST_CONFIRM) && qa_opt('confirm_user_emails') && ($this->template != 'confirm')  && ($this->template != 'account') ) {
					$this->output_raw(ra_template_part('confirmation', $this));
				}else{
					$this->output_raw(ra_template_part(ra_home_theme(), $this));
				}

			}
			
			//$this->body_content();
			
			//$this->footer();
			$this->widgets('full', 'bottom');	
			
			$this->body_suffix();
			$this->body_footer();
			$this->body_hidden();
		}
		function body_header(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($this->content['body_header']))
				$this->output_raw($this->content['body_header']);
		}
		
		function nav_list($navigation, $class, $level=null)	{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<ul class="qa-'.$class.'-list'.(isset($level) ? (' qa-'.$class.'-list-'.$level) : '').'">');

			$index=0;
			
			foreach ($navigation as $key => $navlink) {

				$this->set_context('nav_key', $key);
				$this->set_context('nav_index', $index++);
				$this->nav_item($key, $navlink, $class, $level);
			}

			$this->clear_context('nav_key');
			$this->clear_context('nav_index');
			
			$this->output('</ul>');
		}
		function header(){			
			//$this->nav_user_search();
			//$this->nav_main_sub();
			//$this->header_clear();
		}
		
		function search(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw(ra_template_part('searchform', $this));
		}
		function ra_user_avatar(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (qa_is_logged_in()) {// output user avatar to login bar
				$img_html = 	QA_FINAL_EXTERNAL_USERS
					? qa_get_external_avatar_html(qa_get_logged_in_userid(), 24, true)
					: qa_get_user_avatar_html(qa_get_logged_in_flags(), qa_get_logged_in_email(), qa_get_logged_in_handle(),
						qa_get_logged_in_user_field('avatarblobid'), qa_get_logged_in_user_field('avatarwidth'), qa_get_logged_in_user_field('avatarheight'),
						24, true);
				preg_match( '@src="([^"]+)"@' , $img_html , $match );
				$this->output($match[1]);
			}
		}		
		function ra_user_points(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			if (qa_is_logged_in()) { 
				$userpoints=qa_get_logged_in_points();
				
				$this->output($userpoints);
			}
		}
		function ra_hello_user(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw($this->content['loggedin']['data']);
		}
		function ra_user_box(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw(ra_template_part('nav_userbox', $this));
		}
		
		function body_content()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		}
		
		function main()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		}
		
		function main_parts($content)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$request = (isset($_REQUEST['show']) ? $_REQUEST['show'] : NULL);
			
			if($request && qa_request() == 'admin/plugins' && !isset($content['form_plugin_options']['is_ra_option'])){
				$is_page = 'plugin_single';
			}elseif($request && qa_request() == 'admin/plugins' && isset($content['form_plugin_options']['is_ra_option'])){
				$is_page = 'theme_option';
			}elseif(!$request && qa_request() == 'admin/plugins'){
				$is_page = 'plugin';
			}else{
				$is_page = false;
			}
				
			foreach ($content as $key => $part)
				$this->set_context('part', $key);
			
			if($is_page == 'plugin'){	
				$this->output('<div id="theme_option">');
				$this->output_raw(ra_template_part('admin/plugins', $this));				
				$this->output('</div>');
			}elseif($is_page == 'plugin_single'){
				$this->output('<div id="theme_option">');
				$args= array('request' => $request, 'content' => $content);
				$this->output_raw(ra_template_part('admin/plugin_single', $this, $args ));	
				$this->output('</div>');
			}elseif($is_page == 'theme_option'){
				$this->output('<div id="theme_option">');
				$args= array('request' => $request, 'content' => $content);
				$this->output_raw(ra_template_part('admin/option_panel', $this, $args));	
				$this->output('</div>');				
			}elseif(file_exists(DUDE_THEME_DIR.'/'.$this->template.'.php') ){				
				$this->output('<div id="'.$this->template.'">');				
					$this->output_raw(ra_template_part($this->template, $this, $part));
				$this->output('</div>');
				
			}else{
				if(($this->template != 'admin') && ($this->template!= 'login') && ($this->template!= 'register') && ($this->template != 'reset')&& ($this->template != 'forgot')){		
					$this->output('<div id="'.$this->template.'" class="main-parts">');				
						$this->output_raw(ra_template_part('main-parts', $this, $part));
					$this->output('</div>');
				}else{
					foreach ($content as $key => $part) {					
						$this->main_part($key, $part);
					}
				}
			}
			$this->clear_context('part');
		}
		function main_part($key, $part)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$partdiv=(
				(strpos($key, 'custom')===0) ||
				(strpos($key, 'form')===0) ||
				(strpos($key, 'q_list')===0) ||
				(strpos($key, 'q_view')===0) ||
				//(strpos($key, 'a_form')===0) ||
				(strpos($key, 'a_list')===0) ||
				(strpos($key, 'ranking')===0) ||
				(strpos($key, 'message_list')===0) ||
				(strpos($key, 'nav_list')===0)
			);
				
			if ($partdiv)
				$this->output('<div class="container qa-part-'.strtr($key, '_', '-').'">'); // to help target CSS to page parts

			if (strpos($key, 'custom')===0)
				$this->output_raw($part);

			elseif (strpos($key, 'form')===0)
				$this->form($part);
				
			elseif (strpos($key, 'q_list')===0)
				$this->q_list_and_form($part);

			elseif (strpos($key, 'q_view')===0)
				$this->q_view($part);
				
			/* elseif (strpos($key, 'a_form')===0)
				$this->a_form($part); */
			
			elseif (strpos($key, 'a_list')===0)
				$this->a_list($part);
				
			elseif (strpos($key, 'ranking')===0)
				$this->ranking($part);
				
			elseif (strpos($key, 'message_list')===0)
				$this->message_list_and_form($part);
				
			elseif (strpos($key, 'nav_list')===0) {
				$this->part_title($part);		
				$this->nav_list($part['nav'], $part['type'], 1);
			}

			if ($partdiv)
				$this->output('</div>');
		}
		
		function q_list_and_form($q_list)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($q_list)) {
				$this->part_title($q_list);
	
				if (!empty($q_list['form']))
					$this->output('<form '.$q_list['form']['tags'].'>');
				
				$this->q_list($q_list);
				
				if (!empty($q_list['form'])) {
					unset($q_list['form']['tags']); // we already output the tags before the qs
					$this->q_list_form($q_list);
					$this->output('</form>');
				}
			}
		}

		function form($form)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($form)) {
				$this->part_title($form);

				if (isset($form['tags']))
					$this->output('<form class="form-horizontal" '.$form['tags'].'>');
				
				$this->form_body($form);
	
				if (isset($form['tags']))
					$this->output('</form>');

			}
		}
		function a_form($a_form)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw(ra_template_part('answer_form', $this));
		}
		
		function form_body($form)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (@$form['boxed'])
				$this->output('<div class="qa-form-table-boxed">');
			
			$columns=$this->form_columns($form);
			
			if ($columns)
				$this->output('<div class="qa-form-'.$form['style'].'">');
			
			$this->form_ok($form, $columns);
			$this->form_fields($form, $columns);
			
			if (!empty($form['buttons'])) {
				$this->output('<div class="form-actions">');
				$this->form_buttons($form, $columns);
				$this->output('</div>');
			}
			
			if ($columns)
				$this->output('</div>');

			$this->form_hidden($form);

			if (@$form['boxed'])
				$this->output('</div>');
		}		
		function form_ok($form, $columns)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($form['ok']))
				$this->output(
					'<div class="well success-message">',
					$form['ok'],
					'</div>'
				);
		}
		function form_field_rows($form, $columns, $field)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$style=$form['style'];
			
			if (isset($field['style'])) { // field has different style to most of form
				$style=$field['style'];
				$colspan=$columns;
				$columns=($style=='wide') ? 3 : 1;
			} else
				$colspan=null;
			
			$prefixed=((@$field['type']=='checkbox') && ($columns==1) && !empty($field['label']));
			$suffixed=(((@$field['type']=='select') || (@$field['type']=='number')) && ($columns==1) && !empty($field['label'])) && (!@$field['loose']);
			$skipdata=@$field['tight'];
			$tworows=($columns==1) && (!empty($field['label'])) && (!$skipdata) &&
				( (!($prefixed||$suffixed)) || (!empty($field['error'])) || (!empty($field['note'])) );
			if (isset($field['type']) && $field['type'] == 'block_start'){
				$this->form_block_start($field);
			}elseif(isset($field['type']) && $field['type'] == 'block_end'){
				$this->form_block_end($field);
			}elseif(isset($field['type']) && $field['type'] == 'blank'){
				$this->output('</div><div class="qa-form-wide">');
			}else{
				preg_match( '@name="([^"]+)"@' , @$field['tags'] , $f_class );
				$this->output('<div '.(isset($field['id'])?'id="'.$field['id'].'"':'').' class="form-group '.@$f_class[1].' clearfix">');
					
					if (($columns>1) || !empty($field['label']))
						$this->form_label($field, $style, $columns, $prefixed, $suffixed, $colspan);
					
					
					$this->form_data($field, $style, $columns, 1, $colspan);
				
				$this->output('</div>');
			}
		}
		
		function form_label($field, $style, $columns, $prefixed, $suffixed, $colspan)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(!isset($field['hidelabel'])){
				$this->output('<label class="col-lg-2 control-label" '.(isset($field['id']) ? 'for="option_'.$field['id'].'"':'').'>');
				
				$this->output(@$field['label']);
				if(isset($field['description']))
					$this->output('<small>'.@$field['description'].'</small>');
				$this->output('</label>');
			}
		}
		function form_fields($form, $columns)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($form['fields'])) {
				foreach ($form['fields'] as $key => $field) {
					$this->set_context('field_key', $key);
					$this->form_field_rows($form, $columns, $field);
				}
						
				$this->clear_context('field_key');
			}
		}
		function form_data($field, $style, $columns, $showfield, $colspan)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if ($showfield || (!empty($field['error'])) || (!empty($field['note']))) {
				if (isset($field['label']))
					$this->output('<div class="col-lg-10">');
							
				if ($showfield)
					$this->form_field($field, $style);
	
				if (!empty($field['error'])) {
					$this->output('<span class="help-inline">');
					if (@$field['note_force'])
						$this->form_note($field, $style, $columns);
						
					$this->form_error($field, $style, $columns);
					$this->output('</span>');
				} elseif (!empty($field['note'])){
					$this->output('<span class="help-inline">');
					$this->form_note($field, $style, $columns);
					$this->output('</span>');					
				}
				if (isset($field['label']))
					$this->output('</div>');
			}
		}
		
		function form_field($field, $style)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->form_prefix($field, $style);
			
			switch (@$field['type']) {
				case 'checkbox':
					$this->form_checkbox($field, $style);
					break;
				
				case 'static':
					$this->form_static($field, $style);
					break;
				
				case 'password':
					$this->form_password($field, $style);
					break;
				
				case 'number':
					$this->form_number($field, $style);
					break;
				
				case 'select':
					$this->form_select($field, $style);
					break;				
					
				case 'ra_select':
					$this->ra_form_select($field, $style);
					break;
					
				case 'select-radio':
					$this->form_select_radio($field, $style);
					break;
					
				case 'image':
					$this->form_image($field, $style);
					break;
					
				case 'color':
					$this->form_color($field, $style);
					break;
					
				
				case 'custom':
					echo @$field['html'];
					break;
				
				default:
					if ((@$field['type']=='textarea') || (@$field['rows']>1))
						$this->form_text_multi_row($field, $style);
					else
						$this->form_text_single_row($field, $style);
					break;
			}	

			$this->form_suffix($field, $style);
		}

		function form_password($field, $style)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<input '.@$field['tags'].' type="password" value="'.@$field['value'].'" class="qa-form-'.$style.'-text" placeholder="'.str_replace(':', '', @$field['label']).'"/>');
		}
		function form_text_single_row($field, $style)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<input '.@$field['tags'].' type="text" value="'.@$field['value'].'" class="qa-form-'.$style.'-text" placeholder="'.str_replace(':', '', @$field['label']).'"/>');
		}
		function form_color($field, $style)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<input '.@$field['tags'].' type="text" value="'.@$field['value'].'" class="ra_colorpicker qa-form-'.$style.'-number"/>');
		}
		function ra_form_select($field, $style)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<select '.@$field['tags'].' class="qa-form-'.$style.'-select">');
			
			foreach ($field['options'] as $tag => $value)
				$this->output('<option value="'.$tag.'"'.(($tag==@$field['value']) ? ' selected' : '').'>'.$value.'</option>');
			
			$this->output('</select>');
		}
		function form_buttons($form, $columns)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($form['buttons'])) {
				$style=@$form['style'];
				
				foreach ($form['buttons'] as $key => $button) {
					$this->set_context('button_key', $key);
					
					if (empty($button))
						$this->form_button_spacer($style);
					else {
						$this->form_button_data($button, $key, $style);
						$this->form_button_note($button, $style);
					}
				}
				$this->clear_context('button_key');
			}
		}
		
		function form_button_data($button, $key, $style)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$baseclass='btn btn-default btn-'.$key;
			
			$this->output('<input'.rtrim(' '.@$button['tags']).' value="'.@$button['label'].'" title="'.@$button['popup'].'" type="submit"'.
				(isset($style) ? (' class="'.$baseclass.'"') : '').'/>');
		}	

		function form_block_start($form)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<div id="'.$form['tags'].'" class="tab-pane ra-form-block">');
		}		
		function form_block_end($form)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('</div>');
		}

		function q_list($q_list)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($q_list['qs'])) {				
				$this->output('<div class="qa-q-list'.($this->list_vote_disabled($q_list['qs']) ? ' qa-q-list-vote-disabled' : '').'">', '');
				$this->q_list_items($q_list['qs']);
				
				$this->output('</div> <!-- END qa-q-list -->', '');	
				$this->page_links();
			}
		}
		function q_list_items($q_items)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$i = 0;
			foreach ($q_items as $q_item){
				$option = ra_opt('ra_list_ads');

				if(is_array($option)){
					if(in_array_r($i, $option )){
						foreach($option as $opt){							
								if ($opt['name'] == $i)
									$this->output(str_replace('\\', '', base64_decode($opt['code'])));
													
						}
					}
				}
				$this->q_list_item($q_item);
				
				$i++;
			}
		}
		function q_list_item($q_item)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(ra_list_theme()){	
				$this->output_raw(ra_template_part('q_list/'.ra_list_theme(), $this, $q_item));
			}else{
				$this->output('<div class="qa-q-list-item'.rtrim(' '.@$q_item['classes']).'" '.@$q_item['tags'].'>');

				$this->q_item_stats($q_item);
				$this->q_item_main($q_item);
				$this->q_item_clear();

				$this->output('</div> <!-- END qa-q-list-item -->', '');
			}
			
		}
		
		function ra_list_avatar($q_item,$size = false){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				@$handle = qa_post_userid_to_handle($q_item['raw']['userid']);
			}else{
				@$handle = @$q_item['raw']['handle'];
			}
				
			$this->output('<img height="'.$size.'" width="'.$size.'" src="'.ra_get_avatar($handle, $size, false).'" />');			
		}
		
 		function q_view($q_view)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($q_view)) {
				$this->output_raw(ra_template_part('question-q', $this, $q_view));
			}
		}
		
		function favorite()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$favorite=@$this->content['favorite'];
			
			if (isset($favorite)) {
				$this->output('<div class="fav-parent">');
				$this->favorite_inner_html($favorite);
				$this->output('</div>');
			}
		}
		function favorite_inner_html($favorite)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->favorite_button(@$favorite['favorite_add_tags'], 'icon-star-empty btn-warning,'.@$favorite['form_hidden']['code'].',Favourite');
			$this->favorite_button(@$favorite['favorite_remove_tags'], 'icon-star btn-warning active remove,'.@$favorite['form_hidden']['code'].',Unfavourite');
		}
		function favorite_button($tags, $class)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($tags)){
				if($this->template == 'user') $text =  isset($favorite['favorite_add_tags'])? _ra_lang('Follow') : _ra_lang('Unfollow');
				$code_icon = explode(',', $class);
				$data = str_replace('name', 'data-id', @$tags);
				$data = str_replace('onclick="return qa_favorite_click(this);"', '', @$data);

				$this->output('<a href="#" '.@$favorite['favorite_tags'].' '.$data.' data-code="'.$code_icon[1].'" class="btn fav-btn '.$code_icon[0].'"><span>'.@$code_icon[2].'</span></a>');
			}
		}
		
		function sidepanel()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->widgets('side', 'top');
			$this->sidebar();
			$this->widgets('side', 'high');
			$this->nav('cat', 1);
			$this->widgets('side', 'low');
			$this->output_raw(@$this->content['sidepanel']);
			$this->feed();
			$this->widgets('side', 'bottom');
		}
		
		function title()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($this->content['title'])){
				if (isset($this->content['q_view'])){
					$this->output(_ra_lang('Question'));	
				}elseif(ra_is_home()){
					return;
				}elseif($this->template == 'user'){
					if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
						require_once QA_INCLUDE_DIR.'qa-app-posts.php';
						$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
						$userid = $this->content['raw']['userid'];
					}else{
						$handle = @$this->content['raw']['account']['handle'];
						$userid = @$this->content['raw']['account']['userid'];
					}
					$profile = ra_user_profile($handle);
					$name = isset($profile['name']) ? $profile['name'] : $handle;
					$this->output($name);	
				}else{
					$this->output($this->content['title']);		
				}
			}
		}
		function voting($post)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$code = @$post['voting_form_hidden']['code'] ;
			if (isset($post['vote_view'])) {
				$state = @$post['vote_state'] ;
				$this->output('<div class="'.$state.' '.(isset($this->content['q_list']) ? 'list-':'').'voting clearfix '.(($post['vote_view']=='updown') ? 'qa-voting-updown' : 'qa-voting-net').(($post['raw']['netvotes']< (0)) ? ' negative' : '').(($post['raw']['netvotes']> (0)) ? ' positive' : '').'" '.@$post['vote_tags'].'>');
				$this->voting_inner_html($post);
				$this->output('</div>');
			}
			
		}	

		
		function voting_inner_html($post)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$up_tags = preg_replace('/onclick="([^"]+)"/', '', str_replace('name', 'data-id', @$post['vote_up_tags']));
			$down_tags = preg_replace('/onclick="([^"]+)"/', '', str_replace('name', 'data-id', @$post['vote_down_tags']));
			if (qa_is_logged_in()){	
			$user_point = qa_get_logged_in_points();
			if($post['raw']['type'] == 'Q'){
				if ((qa_opt('permit_vote_q') == '106' )) {
					$need = (qa_opt('permit_vote_q_points') - $user_point);
					$up_tags = str_replace(qa_lang_html('main/vote_disabled_level'), 'You need '.$need.' more points to vote', $up_tags);
				}			

				if ((qa_opt('permit_vote_q') == '106' ) && (qa_opt('permit_vote_down') == '106')) {	
					$max 	= max(qa_opt('permit_vote_down_points'), qa_opt('permit_vote_q_points'));
					$need 	= ($max - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need '.$need.' more points to vote" ', $down_tags);
					
				}elseif (qa_opt('permit_vote_q') == '106' ) {
					$need = (qa_opt('permit_vote_q_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need '.$need.' more points to vote" ', $down_tags);
				}elseif (qa_opt('permit_vote_down') == '106') {
					$need = (qa_opt('permit_vote_down_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need '.$need.' more points to vote" ', $down_tags);
				}
			}			
			if($post['raw']['type'] == 'A'){
				if ((qa_opt('permit_vote_a') == '106' )) {
					$need = (qa_opt('permit_vote_a_points') - $user_point);
					$up_tags = str_replace(qa_lang_html('main/vote_disabled_level'), 'You need '.$need.' more points to vote', $up_tags);
				}			
				if ((qa_opt('permit_vote_a') == '106' ) && (qa_opt('permit_vote_down') == '106')) {	
					$max 	= max(qa_opt('permit_vote_down_points'), qa_opt('permit_vote_a_points'));
					$need 	= ($max - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need '.$need.' more points to vote" ', $down_tags);
					
				}elseif (qa_opt('permit_vote_a') == '106' ) {
					$need = (qa_opt('permit_vote_a_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need '.$need.' more points to vote" ', $down_tags);
				}elseif (qa_opt('permit_vote_down') == '106') {
					$need = (qa_opt('permit_vote_down_points') - $user_point);
					$down_tags = preg_replace('/title="([^"]+)"/', 'title="You need '.$need.' more points to vote" ', $down_tags);
				}
			}
			}
			
			$state = @$post['vote_state'] ;
			$code = qa_get_form_security_code('vote');
				$vote_text = ($post['raw']['netvotes'] >1 || $post['raw']['netvotes']< (-1)) ? _ra_lang('votes') : _ra_lang('vote');
				$this->output('<p class="count">'.$post['raw']['netvotes'].'<span>'.$vote_text.'</span></p>');				
				if (isset($post['vote_up_tags']))
					$this->output('<a '.@$up_tags.' href="#" data-code="'.$code.'" class="icon-chevron-up enabled vote-up '.$state.'"></a>');
				if (isset($post['vote_down_tags']))
					$this->output('<a '.@$down_tags.' href="#" data-code="'.$code.'" class="icon-chevron-down enabled vote-down '.$state.'"></a>');

		}
		
			
		function vote_buttons($post)
		{				
		}
		
		function q_view_closed($q_view)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($q_view['closed'])) {
				$haslink=isset($q_view['closed']['url']);
				
				$this->output(
					'<div class="close-notice">',
					'<i class="icon-blocked"></i>',
					$q_view['closed']['label'],
					($haslink ? ('<a href="'.$q_view['closed']['url'].'"') : '<span').' class="qa-q-view-closed-content">',
					$q_view['closed']['content'],
					$haslink ? '</a>' : '</span>',
					'</div>'
				);
			}
		}
		
		function post_tags($post, $class)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($post['q_tags'])) {
				$this->output('<div class="tags clearfix">');
				$this->post_tag_list($post, $class);
				$this->output('</div>');
			}
		}
		function post_meta($post, $class, $prefix=null, $separator='<br/>')
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<div class="post-meta">');
			$this->output('<div class="asker-meta">');
			if (isset($prefix))
				$this->output($prefix);
			
			$order=explode('^', @$post['meta_order']);
			
			foreach ($order as $element)
				switch ($element) {
					case 'what':
						$this->post_meta_what($post, $class);
						break;
						
					case 'when':
						$this->post_meta_when($post, $class);
						break;
						
					case 'where':
						$this->post_meta_where($post, $class);
						break;
						
					case 'who':
						$this->post_meta_who($post, $class);
						break;
				}
				
			$this->post_meta_flags($post, $class);
			$this->output('</div>');
			
			if (!empty($post['what_2'])) {
				$this->output('<div class="update-meta">');
				
				foreach ($order as $element)
					switch ($element) {
						case 'what':
							$this->output('<span class="'.$class.'-what">'.$post['what_2'].'</span>');
							break;
						
						case 'when':
							$this->output_split(@$post['when_2'], $class.'-when');
							break;
						
						case 'who':
							$this->output_split(@$post['who_2'], $class.'-who');
							break;
					}
				$this->output('</div>');
			}
			
			$this->output('</div>');
		}
		function q_view_buttons($q_view)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($q_view['form'])) {
				$this->form($q_view['form']);
			}
		}
		
		function c_list($c_list, $class)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($c_list)) {
				$args = array('c_list' => $c_list, 'class' => $class);
				$this->output_raw(ra_template_part('comments', $this, $args));
			}
		}
		function c_list_item($c_item)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw(ra_template_part('comment', $this, $c_item));
		} 
		function c_item_content($c_item)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<div class="qa-c-item-content">');
			$this->output_raw('<h3 class="comment-title">');

			if (isset($c_item['who']['data'])){
				$handle = strip_tags($c_item['who']['data']);
				$name 	= ra_name($handle);					
				$this->output('<a href="'.qa_path_html('user/'.$handle).'">'.$name.'</a>');
				
			}
			if(isset($c_item['what']))
				$this->output_raw($c_item['what'].' ');
			if(isset($c_item['when']))
				$this->output_raw(implode(' ', @$c_item['when']));
			$this->output('</h3>');
			$this->output_raw($c_item['content']);
			$this->output('</div>');
		}
		function a_list($a_list)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($a_list)) {
				$this->output_raw(ra_template_part('answers', $this, $a_list));
			}
		}
		
		function a_list_items($a_items)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			foreach ($a_items as $a_item)
				$this->output_raw(ra_template_part('answer', $this, $a_item));
		}
		function a_list_item($a_item)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw(ra_template_part('answer', $this, $a_item));
		}
		function a_selection($post)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($post['main_form_tags']))
				$this->output('<form '.$post['main_form_tags'].'>');

			$this->output('<div class="select-ans">');
			
			if (isset($post['select_tags']))
				$this->output('<button '.$post['select_tags'].' class="icon-checkmark btn btn-default btn-xs qa-a-select-button">'._ra_lang('Select answer').'</button>');
			elseif (isset($post['unselect_tags']))
				$this->output('<button '.$post['unselect_tags'].' class="icon-checkmark btn btn-success btn-xs">'._ra_lang('Best answer').'</button>');
			elseif ($post['selected']){
					$this->output('<button class="icon-checkmark btn btn-success btn-xs">'._ra_lang('Best answer').'</button>');
			}
			/* if (isset($post['select_text']))
				$this->output('<div class="qa-a-selected-text">'.@$post['select_text'].'</div>'); */
			
			$this->output('</div>');
			
			if (isset($post['main_form_tags'])) {
				$this->form_hidden_elements(@$post['buttons_form_hidden']);
				$this->output('</form>');
			}
		}
		
		function page_links()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$page_links=@$this->content['page_links'];
			
			if (!empty($page_links)) {				
				//$this->page_links_label(@$page_links['label']);
				$this->page_links_list(@$page_links['items']);

			}
		}
		function page_links_list($page_items)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($page_items)) {
				$this->output('<ul class="pagination">');
				
				$index=0;
				
				foreach ($page_items as $page_link) {
					$this->set_context('page_index', $index++);
					$this->page_links_item($page_link);
					
					if ($page_link['ellipsis'])
						$this->page_links_item(array('type' => 'ellipsis'));
				}
				
				$this->clear_context('page_index');
				
				$this->output('</ul>');
			}
		}
		function c_form($c_form)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output('<div class="comment-form "'.(isset($c_form['id']) ? (' id="'.$c_form['id'].'"') : '').
				(@$c_form['collapse'] ? ' style="display:none;"' : '').'>');

			$this->form($c_form);
			
			$this->output('</div>', '');
		}		
		
		function ra_dynamic_layout($page){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$o  = '<div id="builder_'.$page.'" data-name="'.$page.'" class="dynamic-layout '.(ra_edit_mode() && ra_is_admin() ? ' edit-canvas edit-mode':'').'">';
			$o .= $this->do_shortcode(ra_layout_cache($page));
			$o .= '</div>';
			$this->output( $o);
		}
		
		function do_shortcode($content) {
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$shortcodes = implode('|', array_map('preg_quote', ra_opt('shortcodes')));
			$pattern    = "/(.?)\[($shortcodes)(.*?)(\/)?\](?(4)|(?:(.+?)\[\/\s*\\2\s*\]))?(.?)/s";
			
			return preg_replace_callback($pattern, array($this,'handleShortcode'), $content);
		}
		
		function handleShortcode($matches) {
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$prefix    = $matches[1];
			$suffix    = $matches[6];
			$shortcode = 'shortcode_'.$matches[2];
			
			// allow for escaping shortcodes by enclosing them in double brackets ([[shortcode]])
			if($prefix == '[' && $suffix == ']') {
				return substr($matches[0], 1, -1);
			}
			
			$attributes = array(); // Parse attributes into into this array.
			
			if(preg_match_all('/(\w+) *= *(?:([\'"])(.*?)\\2|([^ "\'>]+))/', $matches[3], $match, PREG_SET_ORDER)) {
				foreach($match as $attribute) {
					if(!empty($attribute[4])) {
						$attributes[strtolower($attribute[1])] = $attribute[4];
					} elseif(!empty($attribute[3])) {
						$attributes[strtolower($attribute[1])] = $attribute[3];
					}
				}
			}
			return $prefix. call_user_func(array($this,$shortcode), $attributes, $matches[5], $shortcode) . $suffix;
		}
		
		//shortcode:widget for builder
		function shortcode_widget($att, $cont=NULL){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$module	=	qa_load_module('widget', $att['name']);
			if(is_object($module)){
				ob_start();
				$module->output_widget('side', 'top', $this, $this->template, $this->request, $this->content);
				return ob_get_clean();
			}
			return;
		}		
		
		//shortcode: ra_widget for builder
		function shortcode_ra_widget($att, $cont){	
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($att['limit'])) $limit = $att['limit']; else  $limit = 5;
			ob_start();
			if($att['name'] == 'Ra Ask Box'){				
				ra_ask_form();					
			}elseif($att['name'] == 'Ra Stats'){
				ra_builder_stats();
			}elseif($att['name'] == 'Ra Ads'){
				$this->sc_ra_ads($limit );
			}elseif($att['name'] == 'Ra Questions List'){
				ra_builder_ql($limit );
			}elseif($att['name'] == 'Ra Answers List'){
				ra_builder_al($limit );
			}elseif($att['name'] == 'Ra Comments List'){
				ra_builder_cl($limit );
			}elseif($att['name'] == 'Ra New Users'){
				ra_builder_new_users($limit );
			}elseif($att['name'] == 'Ra Top Users'){
				ra_builder_top_users($limit);
			}elseif($att['name'] == 'Ra Events'){
				ra_builder_events($limit );
			}elseif($att['name'] == 'Ra Tags List'){
				ra_builder_tags_list($limit );
			}elseif($att['name'] == 'Ra Categories List'){
				ra_builder_categories_list($limit );
			}elseif($att['name'] == 'Q2A Suggest'){
				$this->suggest_next();
			}elseif($att['name'] == 'Q2A Question'){
				$this->q_view($this->content['q_view']);		
			}elseif($att['name'] == 'Q2A Answers'){
				$this->a_list($this->content['a_list']);		
			}elseif( $att['name'] == 'Q2A Pagination'){
				//$this->page_links();
			}else{
				$shortcode_func = 'sc_'.strtolower(str_replace(' ', '_', $att['name']));
				$this->$shortcode_func();
			}
			return ob_get_clean();				
			
		}
		function sc_ra_menu_list(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output_raw('<h3 class="widget-title">'._ra_lang('Site links').'</h3><div class="site-menu clearfix">'.ra_nav($this->content, 'widget-menu').'</div>');
		}
		function sc_main_parts(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			foreach ($this->content as $key => $part) {					
				$this->main_part($key, $part);
			}
		}
		function sc_q2a_list(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$q_list = $this->content['q_list'];
			if (!empty($q_list['form']))
				$this->output('<form '.$q_list['form']['tags'].'>');
			
			$this->q_list($q_list);
			
			if (!empty($q_list['form'])) {
				unset($q_list['form']['tags']); // we already output the tags before the qs
				$this->q_list_form($q_list);
				$this->output('</form>');
			}
		}		
		function sc_activity_list(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$content = include QA_INCLUDE_DIR.'qa-page-activity.php';
			$q_list = $content['q_list'];
			if (!empty($q_list['form']))
				$this->output('<form '.$q_list['form']['tags'].'>');
			
			$this->q_list($q_list);
			
			if (!empty($q_list['form'])) {
				unset($q_list['form']['tags']); // we already output the tags before the qs
				$this->q_list_form($q_list);
				$this->output('</form>');
			}
			
		}
		function sc_user_activity(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
			}else{
				$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
			}
			if(isset($handle)){
				$content = get_user_activity($handle);
				$this->output($content);
			}
		}
		function sc_q2a_tags()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$ranking = $this->content['ranking'];
			$class='page-tags-list clearfix';
			
			$rows=min($ranking['rows'], count($ranking['items']));
			
			if ($rows>0) {
				$this->output('<div class="row '.$class.'">');
			
				$columns=ceil(count($ranking['items'])/$rows);
				
				for ($column=0; $column<$columns; $column++) {
					$this->set_context('ranking_column', $column);					
					$this->output('<div class="col-lg-'.ceil(12/$columns).'">');
					$this->output('<ul class="list-group">');
		
					for ($row=0; $row<$rows; $row++) {
						$this->set_context('ranking_row', $row);
						$this->ra_tags_item(@$ranking['items'][$column*$rows+$row], $class, $column>0);
					}

					$this->clear_context('ranking_column');
		
					$this->output('</ul>');
					$this->output('</div>');
				}
			
				$this->clear_context('ranking_row');

				$this->output('</div>');
				$this->page_links();
			}
		}
		function ra_tags_item($item, $class, $spacer)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(isset($item))
				$this->output('<li class="list-group-item">'.$item['label'].'<span>'.$item['count'].'</span></li>');		
		}

		
		function sc_q2a_categories()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$navigation = $this->content['nav_list']['nav'];
			$row=ceil(count($navigation)/2);
			$this->output('<div class="row"><div class="col-lg-6"><ul class="page-cat-list">');

			$index=0; $i=1;
			foreach ($navigation as $key => $navlink) {
				$this->set_context('nav_key', $key);
				$this->set_context('nav_index', $index++);
				$this->ra_cat_items($key, $navlink, '');
				if($row == $i)
					$this->output('</ul></div><div class="col-lg-6"><ul class="page-cat-list">');
				
				$i++;
			}

			$this->clear_context('nav_key');
			$this->clear_context('nav_index');			
			
			$this->output('</ul></div></div>');
			$this->page_links();
		}
		
		function ra_cat_items($key, $navlink, $class, $level=null)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$suffix=strtr($key, array( // map special character in navigation key
				'$' => '',
				'/' => '-',
			));
			
			$this->output('<li class="ra-cat-item'.(@$navlink['opposite'] ? '-opp' : '').
				(@$navlink['state'] ? (' ra-cat-'.$navlink['state']) : '').' ra-cat-'.$suffix.'">');
			$this->ra_cat_item($navlink, 'cat');
			
			if (count(@$navlink['subnav']))
				$this->nav_list($navlink['subnav'], $class, 1+$level);
			
			$this->output('</li>');
		}
		function ra_cat_item($navlink, $class)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($navlink['url']))
				$this->output(
					'<h4>'.
					(strlen(@$navlink['note']) ? '<span>'.ra_url_grabber($navlink['note']).'</span>' : '').'<a href="'.$navlink['url'].'" class="ra-'.$class.'-link'.
					(@$navlink['selected'] ? (' ra-'.$class.'-selected') : '').
					(@$navlink['favorited'] ? (' ra-'.$class.'-favorited') : '').
					'"'.(strlen(@$navlink['popup']) ? (' title="'.$navlink['popup'].'"') : '').
					(isset($navlink['target']) ? (' target="'.$navlink['target'].'"') : '').'>'.(@$navlink['favorited'] ? '<i class="icon-star" title="You have added this category to your favourite"></i>' : '').$navlink['label'].
					'</a>'.
					'</h4>'
				);

			else
				$this->output(
					'<h4 class="ra-'.$class.'-nolink'.(@$navlink['selected'] ? (' ra-'.$class.'-selected') : '').
					(@$navlink['favorited'] ? (' ra-'.$class.'-favorited') : '').'"'.
					(strlen(@$navlink['popup']) ? (' title="'.$navlink['popup'].'"') : '').
					'>'.(strlen(@$navlink['note']) ? '<span>'.ra_url_grabber($navlink['note']).'</span>' : '').(@$navlink['favorited'] ? '<i class="icon-star" title="You have added this category to your favourite"></i>' : '').$navlink['label'].
					'</h4>'
				);

			if (strlen(@$navlink['note']))
				$this->output('<span class="ra-'.$class.'-note">'.str_replace('-', '',preg_replace('/<a[^>]*>(.*)<\/a>/iU','',$navlink['note'])).'</span>');
		}
		
		function ra_builder_shortcode_elm($name){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			ob_start();
			?>
			<div class="item parent ui-draggable">		
				<div class="config">					
					<?php ra_builder_control(); ?>
				</div>
				<div class="item-title"><?php echo $name; ?></div>
				<div data-type="ra_widget" data-name="<?php echo $name; ?>" class="item-content widget-c">
					<?php echo $this->do_shortcode('[ra_widget name="'.$name.'"]'); ?>
				</div>
			</div>	
			<?php
			$this->output(ob_get_clean());
		}
		
		function sc_q2a_users()
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$ranking = $this->content['ranking'];
			$class=(@$ranking['type']=='users') ? 'qa-top-users' : 'qa-top-tags';
			
			$rows=min($ranking['rows'], count($ranking['items']));
			
			if ($rows>0) {
				if(qa_opt('users_table_layout')){
					$this->output('<table class="page-users-list">');
					$this->output('
						<tr class="users-list-head">
							<td class="favourite"></td>
							<td class="user">'._ra_lang('User').'</td>
							<td class="activity"><span class="ra-tip" title="'._ra_lang('Questions').'">Q</span></td>
							<td class="activity"><span class="ra-tip" title="'._ra_lang('Answers').'">A</span></td>
							<td class="activity"><span class="ra-tip" title="'._ra_lang('Comments').'">C</span></td>
							<td class="score">'._ra_lang('Points').'</td>');
							if(qa_opt('badge_active'))
								$this->output('<td class="badge-list">'._ra_lang('Badges').'</td>');
							if (!defined('QA_FINAL_WORDPRESS_INTEGRATE_PATH'))					
								$this->output('<td class="action"></td>');
						$this->output('</tr>');
				
					$columns=ceil(count($ranking['items'])/$rows);
					foreach($ranking['items'] as $user){
						
						$handle = ltrim(strip_tags($user['label']));
						
						$fav = '<i title="In your favourite list" class="'.(strpos($user['label'],'qa-user-favorited') ? 'icon-star': 'icon-star-empty').'"></i>';
						$data = ra_user_data($handle);
						$this->output('
							<tr class="user-list-item">
								<td class="favourite">'.$fav.'</td>
								<td class="user">'.ra_get_avatar($handle, 30).'<a href="'.qa_path_html('user/'.$handle).'">'.ra_name($handle).'</a></td>
								<td class="activity q">'.$data[2]['qposts'].'</td>
								<td class="activity a">'.$data[2]['aposts'].'</td>
								<td class="activity c">'.$data[2]['cposts'].'</td>
								<td class="score"><span>'.$data[0]['points'].'</span></td>');
								if(qa_opt('badge_active'))
									$this->output('<td class="badge-list">'.ra_user_badge($handle).'</td>');
							if (!defined('QA_FINAL_WORDPRESS_INTEGRATE_PATH')){
								$this->output('<td class="action">
									<a class="icon-envelope-alt" title="'._ra_lang('Send private message').'" href="'.qa_path_html('message/'.$handle).'"></a>
								</td>');
							}
							$this->output('</tr>');
					}

					$this->output('</table>');
					$this->page_links();
				}else{
					$columns=ceil(count($ranking['items'])/$rows);
					$col = 12/$columns;
					$this->output('<div class="page-users-list non-table">');
					$this->output('<div class="row">');

						foreach($ranking['items'] as $user){
							
							$handle = ltrim(strip_tags($user['label']));
							$profile = ra_user_profile($handle);
							
							if(isset($profile['cover'])){
								$image_file = explode('.',$profile['cover']);
								$cover = 'style="background:url('.qa_opt('site_url').'images/'.@$image_file[0].'_s.'.$image_file[1].') no-repeat scroll 0 0 / cover;"';
							}else{
								$cover = '';
							}
								
							$this->output('<div class="col-md-'.$col.'">');
							$fav = '<i title="In your favourite list" class="'.(strpos($user['label'],'qa-user-favorited') ? 'icon-star': '').'"></i>';
							$data = ra_user_data($handle);
							$this->output('
								<div class="user-list-item clearfix">
									<div class="user-head clearfix">
										<div class="user-cover" '.$cover.'></div>
										<div class="over-cover">									
										<div class="favourite">'.$fav.'</div>
										'.ra_get_avatar($handle, 80).'										
										</div>
										
									</div>
									<div class="user-detail">	
										<div class="name-points">
											<a class="name" href="'.qa_path_html('user/'.$handle).'">'.ra_name($handle).'</a>
											<span class="points">'._ra_lang('Points').'<span>'.$data[0]['points'].'</span></span>										
										</div>');
										if(qa_opt('badge_active'))
										$this->output('<div class="badge-list">'.ra_user_badge($handle).'</div>');
								
								$this->output('</div></div>');
								$this->output('</div>');								
								
						}
					
					$this->output('</div></div>');
					$this->page_links();
					
					
				}
			}
		}
		function sc_full_categories_list(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$level = 1;
			$navigation=@$this->content['navigation']['cat'];
			$this->output('<div class="widget cat-list">
			<h3 class="widget-title">'._ra_lang('Categories').'</h3><ul class="ra-full-catlist'.(isset($level) ? (' qa-list-'.$level) : '').'">');
			$index=0;
			if(isset($navigation))
			foreach ($navigation as $key => $navlink) {
				$this->set_context('nav_key', $key);
				$this->set_context('nav_index', $index++);
				$this->full_categories_list_item($key, $navlink, '', $level);
			}
			$this->clear_context('nav_key');
			$this->clear_context('nav_index');
			
			$this->output('</ul></div>');
		}
		function full_categories_list_item($key, $navlink, $class, $level=null)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$suffix=strtr($key, array( // map special character in navigation key
				'$' => '',
				'/' => '-',
			));
			
			$this->output('<li class="full-cat-list-item icon-folder-close qa-'.$class.'-item'.(@$navlink['opposite'] ? '-opp' : '').
				(@$navlink['state'] ? (' qa-'.$class.'-'.$navlink['state']) : '').' qa-'.$class.'-'.$suffix.'">');
			$this->nav_link($navlink, $class);
			
			if (count(@$navlink['subnav']))
				$this->nav_list($navlink['subnav'], $class, 1+$level);
			
			$this->output('</li>');
		}
		function sc_ra_user_cover(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
				$userid = $this->content['raw']['userid'];
			}else{
				$handle = $this->content['raw']['account']['handle'];
				$userid = $this->content['raw']['account']['userid'];
			}
			$user = ra_user_data($handle);
			$profile = ra_user_profile($handle);
			
			ob_start();
			?>
			<div class="user-top clearfix">
				<?php if(qa_get_logged_in_userid()== $userid){ ?>
					<a id="upload-cover" class="btn btn-default"><?php ra_lang('Change cover'); ?></a>
				<?php } ?>
				<div class="user-bar">
					<div class="avatar pull-left">
						<?php echo ra_get_avatar($handle, 150) ?>
					</div>			
				</div>	
				<div class="user-bar-holder">
					<div class="user-stat pull-right">
						<ul>
							<li class="points"><?php echo $user[0]['points']; ?> <span><?php ra_lang('Points'); ?></span></li>
							<li class="followers"><?php ra_user_followers_count($handle, true); ?> <span><?php ra_lang('Followers'); ?></span></li>
						</ul>
					</div>
					<div class="user-nag">	
						<div class="user-buttons pull-right">
							<?php 
							
								$this->favorite(); 	
								
								if ( !defined('QA_WORDPRESS_INTEGRATE_PATH') && qa_opt('allow_private_messages') && (qa_get_logged_in_userid()!= $this->content['raw']['account']['userid']) &&
							!($this->content['raw']['account']['flags'] & QA_USER_FLAGS_NO_MESSAGES) )
							
								echo '<a class="btn btn-primary btn-sm icon-envelope-alt" href="'.qa_path_html('message/'.$handle).'">'._ra_lang('Send message').'</a>';
							
							?>
						</div>
						<h3 class="user-name"><?php echo ra_name($handle); ?></h3>				
					</div>
				</div>
			</div>
		<?php
			$this->output(ob_get_clean());
		}
		function sc_ra_cat_description(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$req = qa_request(2);
			if(strlen($req)){
				$cat = ra_get_cat_desc($req);
				$this->output('<div class="cat-description"><h2>'.$cat['title'].'</h2>');
				$this->output('<div class="cat-content">'.$cat['content'].'</div></div>');
			}
		}
		function sc_ra_ads($name){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$option = ra_opt('ra_qaads');
			
			if(is_array($option)){
				
				if(in_array_r($name, $option )){
					foreach($option as $opt){
						if(ra_edit_mode() && $opt['name'] == $name){
							$this->output('<div style="height:100px;background:#333;text-align:center;font-size:20px;margin-bottom:20px;">', $opt['name'], '</div>');
						}elseif ($opt['name'] == $name){
							$this->output(str_replace('\\', '', base64_decode($opt['code'])));
						}						
					}
				}else{
					$this->output('No ads code found with this name');
				}
			}
		}
		function sc_ra_user_badges(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(!qa_opt('badge_active'))
				return;
				
			$handle = $this->content['raw']['account']['handle'];
			$this->output('<div class="widget"><h3 class="widget-title">Badges</h3>');
			$this->output('<div class="user-badges-user">'.ra_user_badge($handle).'</div></div>');
		}	
		
		function sc_ra_user_menu(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
			}else{
				$handle = $this->content['raw']['account']['handle'];
			}
			ob_start();
			
			?>
			<ul class="user-page-menu nav nav-tabs">
				<?php
					foreach($this->content['navigation']['sub'] as $k => $nav){
						if($k != 'wall') 
							echo '<li><a href="'.@$nav['url'].'">'.@$nav['label'].'</a></li>';
					}
					
					if(!defined('QA_WORDPRESS_INTEGRATE_PATH') && ra_is_admin()){
						echo '<li class="edit-profile-link pull-right"><a id="edit-user" class="btn btn-xs btn-success edit-profile icon-edit" href="'.qa_path_absolute('user/'.$handle,array('state'=>'edit')).'">Edit User</a></li>';
					}					
					
					if (!defined('QA_WORDPRESS_INTEGRATE_PATH') && (qa_get_logged_in_userid()== $this->content['raw']['account']['userid'] ))	
						echo '<li class="edit-profile-link pull-right"><a class="btn btn-xs btn-primary edit-profile icon-edit" href="'.qa_path_absolute('account').'">Edit</a></li>';				
				?>
			</ul>
			<?php	
			if(!defined('QA_WORDPRESS_INTEGRATE_PATH') && ra_is_admin() && (qa_get_logged_in_userid()!= $this->content['raw']['account']['userid'] )){ ?>			
				<div class="block-user clearfix">
					<?php
						$block = $this->content['form_profile'];
						//unset($activity['title']);
						foreach($block['fields'] as $k => $act){
							if($k != 'bonus')
								unset($block['fields'][$k]);
						}
						$this->form($block);
					?>
				</div>
				<?php }	
			$this->output(ob_get_clean());
			
		}
		function sc_ra_user_profile(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(!defined('QA_WORDPRESS_INTEGRATE_PATH')){
			unset($this->content['form_profile']['fields']['avatar']);
			$user_profile = $this->content['form_profile']['fields'];
			$chk = array_filter($user_profile);
				if(count($chk)){ 
				ob_start();
				
				?>
				<div class="widget profile user-profile-fields">
					<h3 class="widget-title"><?php ra_lang('Profile'); ?></h3>
					<ul class="list-group">
						<?php 								
							$short = array_flip(array('name', 'location', 'website'));
							$ordered_pro = array_merge($short, $user_profile);
							
							foreach($ordered_pro as $k => $profile){
								if($k != 'about'){
									echo '<li class="list-group-item '.$k.'"><span class="profile-label">'.$profile['label'].'</span>'.($k == 'permits' ? '<span class="show-priv">Show all privileges</span><div class="all-priv">'.@$profile['value'].'</div>' : @$profile['value']).'</li>';
								}
							}
						?>					
					</ul>
				</div>	
			<?php 
			$this->output(ob_get_clean());
			}
			}
		}
		function sc_ra_user_about(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(!defined('QA_WORDPRESS_INTEGRATE_PATH')){
				
			$form_profile = $this->content['form_profile']['fields'];
			if(isset($form_profile['about']['value']) && strlen($form_profile['about']['value'])){ 
				ob_start();
				?>
					<div class="widget user-profile-fields">
						<h3 class="widget-title"><?php ra_lang('About'); ?></h3>
						<ul class="list-group about">	
							<li class="list-group-item">
								<?php
									echo $form_profile['about']['value'];
								?>		
							</li>
						</ul>
					</div>
				<?php 
				$this->output(ob_get_clean());
			}
			}
			
		}
		function sc_ra_user_activity(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(!defined('QA_WORDPRESS_INTEGRATE_PATH')){
			if(ra_is_admin()){ ?>			
				<div class="bonus-points clearfix">
					<?php
						$activity = $this->content['form_activity'];
						unset($activity['title']);
						foreach($activity ['fields'] as $k => $act){
							if($k != 'bonus')
								unset($activity['fields'][$k]);
						}
						$this->form($activity);
					?>
				</div>
			<?php }
			?>
			<div class="widget user-profile-fields activity">
				<h3 class="widget-title"><?php ra_lang('Activity'); ?></h3>
				
				<ul class="list-group profile-activity">				
					<?php
						$this->content['form_activity']['fields']['bonus']['icon'] = 'icon-box-2';
						$this->content['form_activity']['fields']['points']['icon'] = 'icon-radio-checked';
						$this->content['form_activity']['fields']['answers']['icon'] = 'icon-chat-3';
						$this->content['form_activity']['fields']['questions']['icon'] = 'icon-question';
						$this->content['form_activity']['fields']['comments']['icon'] = 'icon-comments';
						$this->content['form_activity']['fields']['votedon']['icon'] = 'icon-vote';
						$this->content['form_activity']['fields']['votegave']['icon'] = 'icon-forward';
						$this->content['form_activity']['fields']['votegot']['icon'] = 'icon-reply';
						foreach( $this->content['form_activity']['fields'] as $k=> $profile){
							echo '<li class="list-group-item '.$k.' '.@$profile['icon'].'"><span class="profile-label">'.@$profile['label'].'</span>'.@$profile['value'].'</li>';
						}

					?>				
				</ul>
			</div>
			<?php 
			}
		}
		function sc_ra_user_wall(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(!defined('QA_WORDPRESS_INTEGRATE_PATH'))
				$this->message_list_and_form($this->content['message_list']);
		}
		function sc_ra_user_followers(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
			if(!defined('QA_WORDPRESS_INTEGRATE_PATH')){
				$handle = $this->content['raw']['account']['handle'];			
				ra_user_followers($handle);
			}
		}
		function sc_ra_user_questions($limit=10){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
			}else{
				$handle = $this->content['raw']['account']['handle'];
			}
			?>
				<div class="widget w-question-list">
					<h3 class="widget-title"><?php  ra_lang('My Questions'); ?></h3>
					<?php ra_user_post_list($handle, 'Q', $limit); ?>
				</div>
			<?php
		}
		
		function sc_ra_user_answers($limit=10){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if(defined('QA_WORDPRESS_INTEGRATE_PATH')){
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				$handle = qa_post_userid_to_handle($this->content['raw']['userid']);
			}else{
				$handle = $this->content['raw']['account']['handle'];
			}
			?>
				<div class="widget w-question-list">
					<h3 class="widget-title"><?php  ra_lang('My Answers'); ?></h3>
					<?php ra_user_post_list($handle, 'A', $limit); ?>
				</div>
			<?php
		}
		function sc_ra_badge_history(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$userid = $this->content['raw']['userid'];	
			$badges_arr = ra_user_badges_list($userid);			
			
			
				print_r($badges_arr);
			
		}
		function sc_ra_user_compact(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		
			$handle = explode('/', qa_request());
			$handle = $handle[1];
			
			ob_start();
			
			?>
			<div class="user-compact">
			<div class="user-info clearfix">
				<div class="avatar pull-left">
					<?php echo ra_get_avatar($handle, 40) ?>
				</div>	
				<div class="name-point">
					<h5><?php echo $handle; ?></h5>
					<span class="points"><?php echo ra_user_points($handle); ?></span>
				</div>
			</div>
			<ul class="user-list-menu">
				<?php
					if(ra_is_admin()){
						echo '<li class="edit-profile-link"><a id="edit-user" class="btn btn-xs btn-success edit-profile icon-edit" href="'.qa_path_absolute('user/'.$handle,array('state'=>'edit')).'">Edit User</a></li>';
					}
					foreach($this->content['navigation']['sub'] as $k => $nav){
						if($k != 'wall') 
							echo '<li><a href="'.@$nav['url'].'">'.@$nav['label'].'</a></li>';
					}
				?>
			</ul>
			</div>
			<?php			
			$this->output(ob_get_clean());
		
		}

		function ra_profile_field($name, $label = true){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$this->output(($label ? '<span class="profile-label">'.$this->content['form_profile']['fields'][$name]['label'].'</span> ' : '').$this->content['form_profile']['fields'][$name]['value']);
		}
		function message_list_and_form($list)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($list)) {
				//$this->part_title($list);
				
				$this->error(@$list['error']);

				if (!isset($list['error']) && !empty($list['form'])) {
					$this->output('<form '.$list['form']['tags'].'>');
					unset($list['form']['tags']); // we already output the tags before the messages
					$this->message_list_form($list);
				}
					
				$this->message_list($list);
				
				if (!empty($list['form'])) {
					$this->output('</form>');
				}
			}		
		}
		function message_list_form($list)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (!empty($list['form'])) {
				$this->output('<div class="message-list-form">');
				$this->form($list['form']);
				$this->output('</div>');
			}
		}
		function message_item($message)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			$handle = @$message['raw']['fromhandle'];
			
			$this->output('<div class="message-item" '.@$message['tags'].'>');
			$this->output('<div class="message-inner">');
			$this->output('<div class="message-head clearfix">');
			$this->message_buttons($message);
			
			if(isset($handle))
				$this->output('<div class="avatar pull-left">'.ra_get_avatar($handle, 40).'</div>');
			
			$this->output('<div class="user-detail">');
			
			if(isset($handle))
				$this->output('<h5 class="handle">'.$handle.'</h5>');
				
			$this->post_meta($message, 'qa-message');
			$this->output('</div>');						
			$this->output('</div>');
			$this->message_content($message);
			$this->output('</div></div>', '');
		}		
		function error($error)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (strlen($error))
				$this->output(
					'<div class="alert alert-danger">',
					$error,
					'</div>'
				);
		}
		function finish(){
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
		}
		function post_meta_who($post, $class)
		{
			if (qw_hook_exist(__FUNCTION__)) {$args=func_get_args(); array_unshift($args, $this); return qw_event_hook(__FUNCTION__, $args, NULL); }
			
			if (isset($post['who'])) {
				$this->output('<span class="'.$class.'-who">');
				
				if (strlen(@$post['who']['prefix']))
					$this->output('<span class="'.$class.'-who-pad">'.$post['who']['prefix'].'</span>');
				
				if (isset($post['who']['data'])){
					$handle = strip_tags($post['who']['data']);
					if($post['who']['data'] == strip_tags($post['who']['data'])) {
						$this->output('<span class="'.$class.'-who-data">'.$handle.'</span>');
					}else{
						$name 	= ra_name($handle);					
						$this->output('<span class="'.$class.'-who-data"><a href="'.qa_path_html('user/'.$handle).'">'.$name.'</a></span>');
					}
					
				}
				
				if (isset($post['who']['title']))
					$this->output('<span class="'.$class.'-who-title">'.$post['who']['title'].'</span>');
					
				// You can also use $post['level'] to get the author's privilege level (as a string)
	
				if (isset($post['who']['points'])) {
					$post['who']['points']['prefix']='('.$post['who']['points']['prefix'];
					$post['who']['points']['suffix'].=')';
					$this->output_split($post['who']['points'], $class.'-who-points');
				}
				
				if (strlen(@$post['who']['suffix']))
					$this->output('<span class="'.$class.'-who-pad">'.$post['who']['suffix'].'</span>');
	
				$this->output('</span>');
			}
		}
		
		function qw_notification_btn(){
			qa_html_theme_base::qw_notification_btn();
		}
	}
	
		
