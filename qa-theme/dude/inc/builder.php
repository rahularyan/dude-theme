<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* builder functions and elements */
	
	
/* Save builder data to DB */
function ra_set_builder_content($name, $value){
	qa_db_query_sub(
		'REPLACE ^builder (name, content) VALUES ($, $)',
		$name, $value
	);
}

/* Get the builder data from db if $vale is set then save that value to DB	 */
function ra_db_builder($name, $value=null)
{		
	if(isset($value)){
		ra_set_builder_content($name, $value);
	}else{
		$content = qa_db_read_one_value(
						qa_db_query_sub(
							'SELECT BINARY content as content FROM ^builder WHERE name=#',
							$name
						),
						true
					);
		return str_replace('\\','',$content);
	}
}

function ra_builder_control(){
	echo '<button class="tools remove btn btn-default btn-xs btn-danger"><i class="icon-trashcan icon-white"></i></button>';
	echo '<span class="tools drag btn btn-default btn-xs"><i class="icon-move"></i></span>';
}
function ra_builder_html_control(){
	echo '<button class="tools remove btn btn-default btn-xs btn-danger"><i class="icon-trashcan icon-white"></i></button>';
	echo '<span class="tools drag btn btn-default btn-xs"><i class="icon-move"></i></span>';
	echo '<a href="#html-modal" role="button" class="open-html-modal tools btn btn-primary btn-xs icon-html5" data-toggle="modal"></a>';
}
function ra_builder_widget_control(){
	echo '<button class="tools remove btn btn-default btn-xs btn-danger"><i class="icon-trashcan"></i></button>';
	echo '<span class="tools drag btn btn-default btn-xs"><i class="icon-move"></i></span>';
	echo '<button class="tools param btn btn-default btn-xs btn-danger"><i class="icon-wrench"></i></button>';
	echo '<div class="param-field">
			<div class="param-wrap">
				<input type="text" name="limit" placeholder="Limit" />
				<button class="btn btn-small icon-checkmark"></button>
			</div>
		 </div>';
}
function ra_builder_stats(){
	?>
	<div class="activity-count block">
		<ul class="nav clearfix">
			<li class="icon-question">				
				<?php echo qa_opt('cache_qcount'); ?>
				<span><?php  ra_lang('Questions'); ?></span>
			</li>
			<li class="icon-chat4">
				<?php echo qa_opt('cache_acount'); ?>		
				<span><?php  ra_lang('Answers'); ?></span>
			</li>
			<li class="icon-comments">
				<?php echo qa_opt('cache_ccount'); ?>
				<span><?php  ra_lang('Comments'); ?></span>
			</li>
			<li class="icon-group">
				<?php echo qa_opt('cache_userpointscount'); ?>
				<span><?php  ra_lang('Users'); ?></span>
			</li>
			<li class="icon-tag">
				<?php echo qa_opt('cache_tagcount'); ?>
				<span><?php  ra_lang('Tags'); ?></span>
			</li>
		</ul>
	</div>
	<?php
}

function ra_builder_ql($limit=5){
	?>
		<div class="widget w-question-list">
			<h3 class="widget-title"><?php  ra_lang('Latest Questions'); ?></h3>
			<?php ra_post_list('Q', $limit); ?>
		</div>
	<?php
}
function ra_builder_al($limit=5){
	?>
		<div class="widget w-question-list">
			<h3 class="widget-title"><?php  ra_lang('Latest Answers'); ?></h3>
			<?php ra_post_list('A', $limit); ?>
		</div>
	<?php
}

function ra_builder_cl($limit=5){
	?>
		<div class="widget w-question-list">
			<h3 class="widget-title"><?php  ra_lang('Latest Comments'); ?></h3>
			<?php ra_post_list('C', $limit); ?>
		</div>
	<?php
}

function ra_builder_new_users($limit=5){
	?>
		<div class="widget top-users block clearfix">
			<h3 class="widget-title have-link"><a class="all-user-list btn pull-right" href="<?php echo qa_path_html('users'); ?>"><?php  ra_lang('All Users'); ?></a> <?php  ra_lang('New Users'); ?></h3>
			<?php ra_user_list($limit, 50); ?>
		</div>	
	<?php
}

function ra_builder_top_users($limit = 5){
	?>
		<div class="widget top-users block clearfix">
			<h3 class="widget-title"><?php  ra_lang('Top Users'); ?></h3>
			<?php ra_top_users($limit, 35) ?>
		</div>
	<?php
}

function ra_builder_events($limit=5){
	if(qa_opt('event_logger_to_database')){
	?>
		<div class="widget events clearfix">
			<h3 class="widget-title"><?php  ra_lang('Recent Activities'); ?></h3>
			<?php ra_events($limit) ?>
		</div>
	<?php
	}
}
function ra_builder_tags_list($limit=5){
	?>
		<div class="widget top-users block clearfix">
			<h3 class="widget-title have-link"><a class="all-user-list btn pull-right icon-reorder" href="<?php echo qa_path_html('tags'); ?>"></a> <?php ra_lang('Tags'); ?></h3>
			<ul class="tag-list">
				<?php ra_tag_list($limit); ?>
			</ul>
		</div>
	<?php
}
function ra_builder_categories_list($limit=5){
	?>
		<div class="cat-list widget top-users block clearfix">
			<h3 class="widget-title have-link"><a class="all-user-list btn pull-right icon-reorder" href="<?php echo qa_path_html('categories'); ?>"></a> <?php ra_lang('Categories'); ?></h3>
			<?php ra_cat_list($limit); ?>
		</div>
	<?php
}


// Builder ajax functions

function ra_ajax_save_home_title(){
 	if (qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN){
		qa_opt('ra_home_title', strip_tags($_REQUEST['value']));
	}
	die();
}

function ra_ajax_save_builder_css(){
 	if (ra_is_admin()){		
		ra_db_builder('css_'.$_REQUEST['name'], $_REQUEST['value']);		
	}

	die();
}
function ra_ajax_get_builder_css(){
 	if (ra_is_admin()){		
		echo ra_db_builder('css_'.$_REQUEST['name']);			
	}
	die();
}
function ra_ajax_get_builder_js(){
 	if (ra_is_admin()){		
		echo base64_decode(ra_db_builder('js_'.$_REQUEST['name']));			
	}
	die();
}
function ra_ajax_save_builder_js(){
 	if (ra_is_admin()){		
		ra_db_builder('js_'.$_REQUEST['name'], base64_encode($_REQUEST['value']));		
	}

	die();
}

function ra_ajax_save_builder_data(){
 	if (ra_is_admin()){
		foreach($_REQUEST['value'] as $k => $v){
			ra_db_builder($k, $v);			
		}
	}
	die();
}

function ra_layout_cache($page){
	$file = DUDE_THEME_DIR.'/cache/'.$page.'.php';
	ob_start();
	
	//if in builder mode load from database
	if(ra_edit_mode() && ra_is_admin()){
		if (file_exists($file)) { unlink ($file); }
		return ra_db_builder($page);
	}
	
	// check if the cache file already exists
	if (file_exists($file)) {
		include($file);	 
	} else {
		$html = ra_db_builder($page);
		if(strlen($html)){
			$dom = new DOMDocument();
			$dom->loadHTML( '<?xml encoding="UTF-8">' .$html );
			$xpath = new DOMXPath( $dom );
			$pDivs = $xpath->query(".//div[@class='config']");

			foreach ( $pDivs as $div ) {
			  $div->parentNode->removeChild( $div );
			}

			file_put_contents($file, builder_innerHTML( $dom->documentElement->firstChild ));
			include($file);
		}else{		
			file_put_contents($file, '<h2 style="text-align:center">The layout of this page was not built before, please build the layout using builder.</h2>');
			include($file);
		}
	}	
	$output = ob_get_clean();		
	return $output;	
}
function builder_innerHTML($node){
  $doc = new DOMDocument();
  foreach ($node->childNodes as $child)
    $doc->appendChild($doc->importNode($child, true));

  return preg_replace('/(<[^>]+) style=".*?"/i', '$1', $doc->saveHTML());
}