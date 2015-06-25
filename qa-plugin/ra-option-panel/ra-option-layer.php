<?php
/*
	RA Social
	Author: Rahul Aryan
	Website: http://www.rahularyan.com
	Licence: GPLv3
*/

	class qa_html_theme_layer extends qa_html_theme_base {

		function form_field($field, $style)
		{			
			
			if (@$field['type'] == 'ra_qaads_multi_text'){
				$this->form_prefix($field, $style);
				$this->ra_qaads_form_multi_text($field, $style);
				$this->form_suffix($field, $style);
			
			}else{
				qa_html_theme_base::form_field($field, $style); // call back through to the default function
			}			
		}
		
		function ra_qaads_form_multi_text($field, $style)
		{
			$this->output('<div class="ra-multitext"><div class="ra-multitext-append">');
			
			$i = 0;

			if((strlen($field['value'])!=0) && is_array(unserialize($field['value']))){
				$links = unserialize($field['value']);
				foreach($links as $k => $ads){
					
					$this->output('<div class="ra-multitext-list" data-id="'.$field['id'].'">');
					$this->output('<input name="'.$field['id'].'['.$k.'][name]" type="text" value="'.$ads['name'].'" class="ra-input name" placeholder="'.$field['input_label'].'" />');

					$this->output('<textarea name="'.$field['id'].'['.$k.'][code]" class="ra-input code"  placeholder="Your advertisement code.." />'.str_replace('\\', '',base64_decode($ads['code'])).'</textarea>');
					
					$this->output('<span class="ra-multitext-delete icon-trashcan btn btn-danger btn-xs">Remove</span>');
					$this->output('</div>');
				}
			}else{
				$this->output('<div class="ra-multitext-list" data-id="'.$field['id'].'">');
				$this->output('<input name="'.$field['id'].'[0][name]" type="text"  class="ra-input name" placeholder="'.$field['input_label'].'" />');
				$this->output('<textarea name="'.$field['id'].'[0][code]" class="ra-input code" placeholder="Your advertisement code.."></textarea>');
				
				$this->output('<span class="ra-multitext-delete icon-trashcan btn btn-danger btn-xs">Remove</span>');
				
				$this->output('</div>');
			}
			
			
			$this->output('</div></div>');
			$this->output('<span class="ra-multitext-add icon-plus btn btn-primary btn-xs" title="Add more">Add more</span>');
		}
	

	}
