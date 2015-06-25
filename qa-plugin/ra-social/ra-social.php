<?php

/*
	RA Social
	Author: Rahul Aryan
	Website: http://www.rahularyan.com
	Licence: GPLv3
*/

	class ra_social {
		
		function option_default($option)
		{
			if ($option=='ra_social_show_title')
				return true;
			elseif ($option=='ra_social_title')
				return 'RA Social';
			elseif ($option=='ra_social_inline')
				return false;
			elseif ($option=='ra_social_only_icon')
				return true;
			elseif ($option=='ra_social_icon_hw')
				return '30px';
		}
		

		
		function admin_form()
		{
			$saved=false;
			
			if (qa_clicked('ra_social_save_button')) {		
				qa_opt('ra_social_show_title', (int)qa_post_text('ra_social_show_title'));			
				qa_opt('ra_social_title', qa_post_text('ra_social_title'));			
				qa_opt('ra_social_inline', (int)qa_post_text('ra_social_inline'));			
				qa_opt('ra_social_only_icon', (int)qa_post_text('ra_social_only_icon'));			
				qa_opt('ra_social_icon_hw', qa_post_text('ra_social_icon_hw'));			
				qa_opt('ra_social_links', serialize($_REQUEST['ra_social_links']));
				
				$saved=true;
			}
			
			return array(
				'ok' => $saved ? 'RA Social settings saved' : null,
				
				'fields' => array(
					array(
						'label' => 'Show title',
						'type' => 'checkbox',	
						'value' => (int)qa_opt('ra_social_show_title'),
						'tags' => 'name="ra_social_show_title"',
					),
					array(
						'label' => 'Show title',
						'type' => 'text',	
						'value' => qa_opt('ra_social_title'),
						'tags' => 'name="ra_social_title"',
					),
					array(
						'label' => 'Inline',
						'type' => 'checkbox',	
						'value' => (int)qa_opt('ra_social_inline'),
						'tags' => 'name="ra_social_inline"',
					),
					array(
						'label' => 'Only icon',
						'type' => 'checkbox',	
						'value' => (int)qa_opt('ra_social_only_icon'),
						'tags' => 'name="ra_social_only_icon"',
					),
					array(
						'label' => 'Icon height - width',
						'type' => 'number',	
						'value' => qa_opt('ra_social_icon_hw'),
						'tags' => 'name="ra_social_icon_hw"',
					),
					
					array(
						'label' => 'Social Links',
						'type' => 'multi_text',	
						'value' => qa_opt('ra_social_links'),
						'tags' => 'ra_social_links[]',
					)
				),
				
				'buttons' => array(
					array(
						'label' => 'Save Changes',
						'tags' => 'name="ra_social_save_button"',
					),
				),
			);
		}

		
		function allow_template($template)
		{
			$allow=false;
			
			switch ($template)
			{
				case 'activity':
				case 'qa':
				case 'questions':
				case 'hot':
				case 'ask':
				case 'categories':
				case 'question':
				case 'tag':
				case 'tags':
				case 'unanswered':
				case 'user':
				case 'users':
				case 'search':
				case 'admin':
				case 'custom':
					$allow=true;
					break;
			}
			
			return $allow;
		}

		
		function allow_region($region)
		{
			$allow=false;
			
			switch ($region)
			{
				case 'main':
				case 'side':
				case 'full':
					$allow=true;
					break;
			}
			
			return $allow;
		}
		

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			$links = unserialize(qa_opt('ra_social_links'));
			if(is_array($links)){
				$url = qa_opt('site_url').'qa-plugin/ra-social/';		
				
				
				if (qa_opt('ra_social_show_title'))
					$themeobject->output(
						'<h3 class="widget-title">'.qa_html(qa_opt('ra_social_title')).'</h3>'
					);
				
				$themeobject->output('<div class="ra-social-output clearfix">');
				
				$class = 'class="'.(qa_opt('ra_social_inline') ? 'inline' :'').(qa_opt('ra_social_only_icon') ? ' only-icon' :'').'"';
				$height_width = 'style="height:'.qa_opt('ra_social_icon_hw').';width:'.qa_opt('ra_social_icon_hw').'"';
				$themeobject->output('<ul '.$class.'>');
				
				$feed=@$themeobject->content['feed'];
			
				if (!empty($feed)) {
					$themeobject->output('<li><a href="'.$feed['url'].'"><img '.$height_width.' src="'.$url.'images/rss_24.png" /><span>'.qa_html($feed['label']).'</span></a></li>');
				}
				
				foreach ($links as $k => $link) {
					$themeobject->output('<li><a target="_blank" href="'.$link['link'].'"><img '.$height_width.' src="'.$url.'images/'.$link['icon'].'" /><span>'.qa_html($link['site']).'</span></a></li>');
				}
				
				$themeobject->output('</ul>');
				$themeobject->output('</div>');
			}
		}
	
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/