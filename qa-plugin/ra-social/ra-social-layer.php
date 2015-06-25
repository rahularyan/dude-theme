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
			$url = qa_opt('site_url').'qa-plugin/ra-social/';
			
			$this->output('<link rel="stylesheet" type="text/css" href="'.$url.'style.css">');
			$this->output('<script src="'.$url.'script.js"></script>');
			
		}
		function form_field($field, $style)
		{			
			
			if (@$field['type'] == 'multi_text'){
				$this->form_prefix($field, $style);
				$this->form_multi_text($field, $style);
				$this->form_suffix($field, $style);
			
			}else{
				qa_html_theme_base::form_field($field, $style); // call back through to the default function
			}			
		}
		
		function form_multi_text($field, $style)
		{
			
			$this->output('<div class="ra-social-append">');
			
			$i = 0;

			if((strlen($field['value'])!=0) && is_array(unserialize($field['value']))){
				$links = unserialize($field['value']);
				foreach($links as $k => $link){
					
					$this->output('<div class="ra-social-list">');
					$this->output('<input name="ra_social_links['.$k.'][site]" type="text" value="'.$link['site'].'" class="ra-input site" placeholder="Site" />');
					$this->output('<input name="ra_social_links['.$k.'][link]" type="text" value="'.$link['link'].'" class="ra-input link" placeholder="Link" />');
					$this->output('<select class="icon" name="ra_social_links['.$k.'][icon]">');
						foreach ($this->ra_list_images() as $img){						
							$selected = @$link['icon'] == $img ? 'selected' :'';
							$this->output('<option value="'.$img.'" '.$selected.'>'.$img.'</option>');
						}
					$this->output('</select>');
					$this->output('<span class="ra-social-delete"></span>');
					$this->output('</div>');
				}
			}else{
				$this->output('<div class="ra-social-list">');
				$this->output('<input name="ra_social_links[0][site]" type="text"  class="ra-input site" placeholder="Site" />');
				$this->output('<input name="ra_social_links[0][link]" type="text"  class="ra-input link" placeholder="http://twitter.com/nerdaryan" />');
				$this->output('<select class="icon" name="ra_social_links[0][icon]">');
					foreach ($this->ra_list_images() as $img)
					$this->output('<option name="ra_social_links[0][icon]" value="'.$img.'">'.$img.'</option>');
				$this->output('</select>');
				$this->output('<span class="ra-social-delete"></span>');
				
				$this->output('</div>');
			}
			
			
			$this->output('</div>');
			$this->output('<span class="ra-social-add" title="Add more"></span>');
		}
		
		function ra_list_images() {
			$ext = array("jpg", "png");
			$files = array();
			if($handle = opendir(QA_PLUGIN_DIR.'ra-social/images/')) {
			while(false !== ($file = readdir($handle)))
			for($i=0;$i<sizeof($ext);$i++)
			if(strstr($file, ".".$ext[$i]))
			$files[] = $file;
			 
			closedir($handle);
			}
			return($files);
		}	

	}
