<?php

	class ra_option_admin {
		// extract font name from goosle stylesheet URL
		function ra_font_name($font_url){
			$patterns = array(
			  //replace the path root
			'!^http://fonts.googleapis.com/css\?!',
			  //capture the family and avoid and any following attributes in the URI.
			'!(family=[^&:]+).*$!',
			  //delete the variable name
			'!family=!',
			  //replace the plus sign
			'!\+!');
			$replacements = array(
			"",
			'$1',
			'',
			' ');
			
			$font = preg_replace($patterns,$replacements,$font_url);
			return $font;

		}

		function ra_font_family(){
			$fonts = array();
			$fonts[] = '';
			$fonts['Georgia, Times New Roman, Times, serif'] = 'Serif Family';
			$fonts['Helvetica Neue, Helvetica, Arial, sans-serif'] = 'Sans Family';	
			$option_fonts = qa_opt('ra_fonts');
			if(!empty($option_fonts)){
				foreach (explode("\n", qa_opt('ra_fonts')) as $font){				
					$fonts[$this->ra_font_name($font).', Helvetica, Arial, sans-serif'] = $this->ra_font_name($font);
				}
			}
			return $fonts;
		}
		
 		function ra_readdir($path){
			$path = QA_THEME_DIR.qa_get_site_theme().$path;
			foreach(array_diff(scandir($path), array('.', '..')) as $f) 
				if (is_file($path . '/' . $f) && (('.php') ? preg_match('/.php/' , $f) : 1)) 
					$l[str_replace('.php', '', $f)] = str_replace('.php', '', $f);
			return $l; 
		} 
		
		function option_default($option)
		{
			if ($option=='option_ra_home_layout'):
				return 'modern';
			elseif($option == 'ra_logo'):
				return qa_opt('site_url').'qa-theme/'.qa_get_site_theme().'/images/logo.png';
			endif;
		}
	
		
		function admin_form(&$qa_content)
		{
			$saved=false;

			if (qa_clicked('ra_save_button')) {	
				if ($_FILES['ra_logo_field']['size'] > 0){
					if(getimagesize($_FILES['ra_logo_field']['tmp_name']) >0){
						$url		= qa_opt('site_url').'qa-theme/'.qa_get_site_theme().'/images/';
						$uploaddir 	= QA_THEME_DIR.qa_get_site_theme().'/images/';
						$uploadfile = $uploaddir . basename($_FILES['ra_logo_field']['name']);
						move_uploaded_file($_FILES['ra_logo_field']['tmp_name'], $uploadfile);
						
						qa_opt('ra_logo', $url.$_FILES['ra_logo_field']['name']);
					}
				}

				//general
				qa_opt('ra_home_layout', qa_post_text('option_ra_home_layout'));				
				qa_opt('google_analytics', qa_post_text('option_google_analytics'));	
				qa_opt('custom_head_meta', qa_post_text('option_custom_head_meta'));
				qa_opt('ra_colla_comm', (bool)qa_post_text('option_ra_colla_comm'));
				qa_opt('show_real_name', (bool)qa_post_text('option_show_real_name'));
				qa_opt('users_table_layout', (bool)qa_post_text('option_users_table_layout'));

				//color
				qa_opt('ra_primary_color', qa_post_text('option_ra_primary_color'));	
				qa_opt('ra_nav_bg', qa_post_text('option_ra_nav_bg'));	
				qa_opt('ask-btn-bg', qa_post_text('option_ask-btn-bg'));	
				qa_opt('selected-ans-bg', qa_post_text('option_selected-ans-bg'));	
				qa_opt('hero-bg', qa_post_text('option_hero-bg'));	
				qa_opt('tags-bg', qa_post_text('option_tags-bg'));	
				qa_opt('vote-positive-bg', qa_post_text('option_vote-positive-bg'));	
				qa_opt('vote-negative-bg', qa_post_text('option_vote-negative-bg'));	
				qa_opt('vote-default-bg', qa_post_text('option_vote-default-bg'));	
				qa_opt('post-status-open', qa_post_text('option_post-status-open'));	
				qa_opt('post-status-selected', qa_post_text('option_post-status-selected'));	
				qa_opt('post-status-closed', qa_post_text('option_post-status-closed'));	
				qa_opt('post-status-duplicate', qa_post_text('option_post-status-duplicate'));	
				qa_opt('favourite-btn-bg', qa_post_text('option_favourite-btn-bg'));	
				qa_opt('bottom-bg', qa_post_text('option_bottom-bg'));	
				
				// Typography
				qa_opt('ra_fonts', qa_post_text('option_ra_fonts'));	
				qa_opt('ra_body_font', qa_post_text('option_ra_body_font'));	
				qa_opt('ra_h_font', qa_post_text('option_ra_h_font'));	
				qa_opt('q-list-ff', qa_post_text('option-q-list-ff'));	

				//list
				qa_opt('ra_list_layout', qa_post_text('option_ra_list_layout'));
				qa_opt('ra_show_ans_view', (bool)qa_post_text('option_ra_show_ans_view'));

				
				// Navigation
				qa_opt('ra_nav_fixed', (bool)qa_post_text('option_ra_nav_fixed'));	
				qa_opt('ra_show_icon', (bool)qa_post_text('option_ra_show_icon'));	
				qa_opt('ra_nav_parent_font_size', qa_post_text('option_ra_nav_parent_font_size'));	
				qa_opt('ra_nav_child_font_size', qa_post_text('option_ra_nav_child_font_size'));	
				
				// bootstrap							
				qa_opt('ra_body_bg', qa_post_text('option_ra_body_bg'));				
				qa_opt('ra_text_color', qa_post_text('option_ra_text_color'));				
				qa_opt('font-size-base', qa_post_text('option-font-size-base'));				
				qa_opt('ra_base_fontfamily', qa_post_text('option_ra_base_fontfamily'));				
				qa_opt('ra_base_lineheight', qa_post_text('option_ra_base_lineheight'));
				
				
				qa_opt('ads_below_question_title', base64_encode(@$_REQUEST['option_ads_below_question_title']));
				qa_opt('ads_after_question_content', base64_encode(@$_REQUEST['option_ads_after_question_content']));
				
				
				$ra_ads = $_REQUEST['ra_qaads'];
				$ra_list_ads = $_REQUEST['ra_list_ads'];
				foreach($ra_ads as $k => $ads){
					$ra_ads[$k]['code'] = base64_encode($ra_ads[$k]['code']);
				}
				foreach($ra_list_ads as $k => $ads){
					$ra_list_ads[$k]['code'] = base64_encode($ra_list_ads[$k]['code']);
				}
				qa_opt('ra_qaads', serialize($ra_ads));	
				qa_opt('ra_list_ads', serialize($ra_list_ads));	
				
				$saved=true;
			}

			$ra_forms =  array(
				'ok' => $saved ? 'Settings saved' : null,
				'tags' => 'method="post" enctype="multipart/form-data" class="form-horizontal"', 
				'is_ra_option' => 1,
				'fields' => array(					
					array(
						'label' => 'General',
						'type' => 'block_start',
						'tags'	=>	'general',
						'icon'	=>	'icon-equalizer'
					),
					array(
						'label' => 'Logo',
						'type' => 'static',
						'value' =>  '<img class="image-preview" src ="'.qa_opt('ra_logo').'" /><input type="file" name="ra_logo_field" id="ra_logo_field" class="btn btn-success">',
						'tags' => 'name="option_ra_logo" id="option_ra_logo"',
						'description' => 'Upload a image to use as logo',
					),	
					array(
						'id' => 'ra_home_layout',
						'label' => 'Home layout',
						'type' => 'ra_select',
						'value' => qa_opt('ra_home_layout'),
						'options' => $this->ra_readdir('/home'),
						'tags' => 'name="option_ra_home_layout"',
						'description' => 'Set the layout of home'
					),					
								

					array(
						'id' => 'google_analytics',
						'label' => 'Analytics tracking',
						'value' => qa_opt('google_analytics'),
						'tags' => 'name="option_google_analytics"',
						'description' => 'Google analytics tracking ID'
					),						
					array(
						'id' => 'custom_head_meta',
						'label' => 'Custom Meta',
						'type' => 'textarea',
						'rows' => '3',
						'value' => qa_opt('custom_head_meta'),
						'tags' => 'name="option_custom_head_meta"',
						'description' => 'Like google and bing verification meta'
					),
					array(
						'label' => 'Collapsible comments',
						'type' => 'checkbox',
						'value' => qa_opt('ra_colla_comm'),
						'tags' => 'name="option_ra_colla_comm" id="option_ra_colla_comm"',
						'description' => 'Show collapsible comments',
					),	
					array(
						'id' => 'show_real_name',
						'label' => 'Show Real name',
						'type' => 'checkbox',
						'value' => qa_opt('show_real_name'),
						'tags' => 'name="option_show_real_name" id="option_show_real_name"',
						'description' => 'Show real name instead of username',
					),	
					array(
						'id' => 'users_table_layout',
						'label' => 'User list in table',
						'type' => 'checkbox',
						'value' => qa_opt('users_table_layout'),
						'tags' => 'name="option_users_table_layout" id="option_users_table_layout"',
						'description' => 'Show table based user list in user page',
					),						
					'generalend' => array(
						'type' => 'block_end'
					),	
		/* -------------------------Start typography---------------------- */
					array(
						'label' => 'Typo',
						'type' => 'block_start',
						'tags'	=>	'typo',
						'icon'	=>	'icon-pen'
					),	
					array(
						'label' => 'Google fonts',
						'type' => 'textarea',
						'rows' => '5',
						'value' => qa_opt('ra_fonts'),
						'tags' => 'name="option_ra_fonts"',
						'description' => 'Google font url separated by a |'
					),						
					array(
						'label' => 'Body font family',
						'type' => 'ra_select',
						'value' => qa_opt('ra_body_font'),
						'options' => $this->ra_font_family(),
						'tags' => 'name="option_ra_body_font"',
						'description' => 'Set the fontfamily for the main body'
					),						
					array(
						'id' => 'ra_h_font',
						'label' => 'Heading font family',
						'type' => 'ra_select',
						'value' => qa_opt('ra_h_font'),
						'options' => $this->ra_font_family(),
						'tags' => 'name="option_ra_h_font"',
						'description' => 'Set the font family for the heading'
					),						
					array(
						'id' => 'q-list-ff',
						'label' => 'List font family',
						'type' => 'ra_select',
						'value' => qa_opt('q-list-ff'),
						'options' => $this->ra_font_family(),
						'tags' => 'name="option-q-list-ff"',
						'description' => 'Set the font family for the list'
					),	
					
					array(
						'type' => 'block_end'
					),
		/* -------------------------Start colors---------------------- */
					array(
						'label' => 'Colors',
						'type' => 'block_start',
						'tags'	=>	'colors',
						'icon'	=>	'icon-eyedropper'
					),	
					
					array(
						'id' => 'ra_nav_bg',
						'label' => 'Nav Background',
						'type' => 'color',
						'value' => qa_opt('ra_nav_bg'),
						'tags' => 'name="option_ra_nav_bg"',
						'description' => 'Background color of navigation'
					),					
					array(
						'id' => 'ra_primary_color',
						'label' => 'Parimary color',
						'type' => 'color',
						'value' => qa_opt('ra_primary_color'),
						'tags' => 'name="option_ra_primary_color"',
						'description' => 'Set a primary color'
					),						
					array(
						'id' => 'ask-btn-bg',
						'label' => 'Ask button background',
						'type' => 'color',
						'value' => qa_opt('ask-btn-bg'),
						'tags' => 'name="option_ask-btn-bg"',
						'description' => 'Ask button background color'
					),	

					array(
						'id' => 'hero-bg',
						'label' => 'Hero background',
						'type' => 'color',
						'value' => qa_opt('hero-bg'),
						'tags' => 'name="option_hero-bg"',
						'description' => 'BAckground color for hero unit'
					),						
					array(
						'id' => 'vote-positive-bg',
						'label' => 'Positive vote bg',
						'type' => 'color',
						'value' => qa_opt('vote-positive-bg'),
						'tags' => 'name="option_vote-positive-bg"',
						'description' => 'Background for positive vote'
					),					
					array(
						'id' => 'vote-negative-bg',
						'label' => 'Negative vote bg',
						'type' => 'color',
						'value' => qa_opt('vote-negative-bg'),
						'tags' => 'name="option_vote-negative-bg"',
						'description' => 'Background for negative vote'
					),	
					array(
						'id' => 'vote-default-bg',
						'label' => 'Default vote bg',
						'type' => 'color',
						'value' => qa_opt('vote-default-bg'),
						'tags' => 'name="option_vote-default-bg"',
						'description' => 'Background for default vote'
					),	
					array(
						'id' => 'bottom-bg',
						'label' => 'Bottom Bg',
						'type' => 'color',
						'value' => qa_opt('bottom-bg'),
						'tags' => 'name="option_bottom-bg"',
						'description' => 'Background for bottom'
					),					
					
					
					array(
						'type' => 'block_end'
					),	
					
		/* -------------------------Start list---------------------- */
		
					'liststart' => array(
						'label' => 'List',
						'type' => 'block_start',
						'tags'	=>	'list',
						'icon'	=>	'icon-layout-10'
					),	
					array(
						'id' => 'ra_list_layout',
						'label' => 'List layout',
						'type' => 'ra_select',
						'value' => qa_opt('ra_list_layout'),
						'options' => $this->ra_readdir('/q_list'),
						'tags' => 'name="option_ra_list_layout"',
						'description' => 'Set the layout of list'
					),	
					array(
						'id' => 'ra_show_ans_view',
						'label' => 'Show Answer & view count',
						'type' => 'checkbox',
						'value' => qa_opt('ra_show_ans_view'),
						'tags' => 'name="option_ra_show_ans_view" id="option_ra_show_ans_view"',
						'description' => 'Show answer and view count in list',
					),	
										
					'listend' => array(
						'type' => 'block_end'
					),	
		/* -------------------------Start nav---------------------- */
					array(
						'label' => 'Navigation',
						'type' => 'block_start',
						'tags'	=>	'navigation',
						'icon'	=>	'icon-reorder'
					),	
					
					array(
						'label' => 'Fixed Nav',
						'type' => 'checkbox',
						'value' => qa_opt('ra_nav_fixed'),
						'tags' => 'name="option_ra_nav_fixed" id="option_ra_nav_fixed"',
						'description' => 'Stick navigation to the top',
					),					
					array(
						'label' => 'Show menu Icon',
						'type' => 'checkbox',
						'value' => qa_opt('ra_show_icon'),
						'tags' => 'name="option_ra_show_icon" id="option_ra_show_icon"',
						'description' => 'Check this for hiding menu icon',
					),
					array(
						'label' => 'Nav parent font size',
						'type' => 'number',
						'value' => qa_opt('ra_nav_parent_font_size'),
						'tags' => 'name="option_ra_nav_parent_font_size" id="option_ra_nav_parent_font_size"',
						'description' => 'Font size of parent menu',
					),						
					array(
						'label' => 'Nav child font size',
						'type' => 'number',
						'value' => qa_opt('ra_nav_child_font_size'),
						'tags' => 'name="option_ra_nav_child_font_size" id="option_ra_nav_child_font_size"',
						'description' => 'Font size of child menu',
					),						

					array(
						'type' => 'block_end'
					),	
		/* -------------------------Start Bootstrap---------------------- */
					array(
						'label' => 'Bootstrap',
						'type' => 'block_start',
						'tags'	=>	'bootstrap',
						'icon'	=>	'icon-twitter'
					),	
					
					array(
						'id' => 'ra_body_bg',
						'label' => 'Body Background',
						'type' => 'color',
						'value' => qa_opt('ra_body_bg'),
						'tags' => 'name="option_ra_body_bg"',
						'description' => 'Background color of body'
					),						
					array(
						'id' => 'ra_text_color',
						'label' => 'Body text color',
						'type' => 'color',
						'value' => qa_opt('ra_text_color'),
						'tags' => 'name="option_ra_text_color"',
						'description' => 'Color of the main body texts'
					),						
					
					array(
						'id' => 'font-size-base',
						'label' => 'Base font size',
						'value' => qa_opt('font-size-base'),
						'tags' => 'name="option-font-size-base"',
						'description' => 'Font size of main body'
					),							
					
					array(
						'id' => 'ra_base_fontfamily',
						'label' => 'Base font family',
						'value' => qa_opt('ra_base_fontfamily'),
						'tags' => 'name="option_ra_base_fontfamily"',
						'description' => 'Font family of main body'
					),					
					array(
						'id' => 'ra_base_lineheight',
						'label' => 'Base line height',
						'value' => qa_opt('ra_base_lineheight'),
						'tags' => 'name="option_ra_base_lineheight"',
						'description' => 'Font size of main body'
					),							
					
					array(
						'type' => 'block_end'
					),	
					
		/* -------------------------end colors---------------------- */	

					array(
						'label' => 'Ads',
						'type' => 'block_start',
						'tags'	=>	'ads',
						'icon'	=>	'icon-money'
					),	
						
					array(
						'id' => 'ra_qaads',
						'label' => 'Ads code',
						'description' => 'Add ads using builder, use ad name to add the code',
						'input_label' => 'Ad name',
						'type' => 'ra_qaads_multi_text',	
						'value' => qa_opt('ra_qaads'),
						'tags' => 'ra_qaads[]',
					),
					array(
						'id' => 'ra_list_ads',
						'label' => 'List Ads',
						'description' => 'Add ads between questions list',
						'input_label' => 'Order',
						'type' => 'ra_qaads_multi_text',	
						'value' => qa_opt('ra_list_ads'),
						'tags' => 'ra_list_ads[]',
					),
					array(
						'label' => 'Ads below question title',
						'type' => 'textarea',
						'rows' => '5',
						'value' => str_replace('\\', '', base64_decode(qa_opt('ads_below_question_title'))),
						'tags' => 'name="option_ads_below_question_title"',
						'description' => 'Ads below question title and avatar'
					),
					array(
						'label' => 'Ads after question content',
						'type' => 'textarea',
						'rows' => '5',
						'value' => str_replace('\\', '', base64_decode(qa_opt('ads_after_question_content'))),
						'tags' => 'name="option_ads_after_question_content"',
						'description' => 'Add ads after question content'
					),
					
					array(
						'type' => 'block_end'
					),						
				),
				
				'buttons' => array(
					array(
						'label' => 'Save Changes',
						'tags' => 'name="ra_save_button"',
					),
				),
			);
			return $ra_forms;
		}
		
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/