<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Builder nav */
?>
<div class="builder-elem">
	<div class="buttons">			
		<button id="save-builder" class="btn btn-default btn-xs icon-checkmark" title="Save layout"></button>
		<!--<button id="clear-builder" class="clear btn btn-danger btn-default btn-xs icon-cancel" title="Clear layout"></button>-->
		<button class="enable-front-edit icon-edit btn btn-info btn-default btn-xs" title="Edit titles"></button>
		<a  role="button" class="open-js-modal btn btn-primary btn-xs icon-code " title="Insert JavaScript"></a>
		<a id="open-css-editor" class="icon-css3 btn btn-success btn-default btn-xs" title="Insert CSS"></a>
		<div class="css-choose-block">
			
		</div>			
	</div>
	<div class="element-container">		
		<ul class="nav nav-list accordion-group">
			<li id="grid-elem" class="nav-header">
				<i class="icon-th"></i> <?php ra_lang('Grid'); ?>
			</li>
			<li class="rows">
				<div class="ra-row parent ui-draggable">	
					<div class="config">
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title">
						<input class="row-count" value="6 6" type="text">
						<select class="ra-contain">
							<option value="yes"><?php ra_lang('Container'); ?></option>
							<option value="no"><?php ra_lang('No container'); ?></option>
						</select>

					</div>
					<div class="item-content">
						<div class="ra-container">
						<div class="container">
							<div class="row clearfix">
								<div class="col-md-6 column"></div>
								<div class="col-md-6 column"></div>
							</div>
						</div>
						</div>
					</div>
				</div>
			</li>
		</ul>
		<ul class="nav nav-list accordion-group">
			<li class="nav-header" id="html-elm"><i class="icon-box"></i> 
				<?php ra_lang('Page items'); ?>
			</li>
			<li class="items">
				<?php if (isset($context->content['q_list'])){ ?>
					<div class="item parent ui-draggable">		
						<div class="config">					
							<?php ra_builder_control(); ?>
						</div>
						<div class="item-title"><?php ra_lang('List'); ?></div>
						<div data-type="ra_widget" data-name="Q2A List" class="item-content widget-c">
							<?php echo $context->do_shortcode('[ra_widget name="Q2A List"]'); ?>
						</div>
					</div>	
				<?php } ?>				
				<?php 
					$context->ra_builder_shortcode_elm('Main Parts'); 
					$context->ra_builder_shortcode_elm('Activity List'); 
					if ($context->template == 'tags'){ 
						$context->ra_builder_shortcode_elm('Q2A Tags'); 
					} 					
					if ($context->template == 'categories'){ 
						$context->ra_builder_shortcode_elm('Q2A Categories'); 
					}  
					
					if ($context->template == 'users'){ 
						$context->ra_builder_shortcode_elm('Q2A Users'); 
					} 
				
					if ($context->template == 'user'){ 
						$context->ra_builder_shortcode_elm('RA User Cover'); 
						$context->ra_builder_shortcode_elm('RA User Menu'); 
						$context->ra_builder_shortcode_elm('RA User Profile'); 
						$context->ra_builder_shortcode_elm('RA User About'); 
						$context->ra_builder_shortcode_elm('RA User Activity'); 
						$context->ra_builder_shortcode_elm('RA User Wall'); 
						$context->ra_builder_shortcode_elm('RA User Followers'); 
						$context->ra_builder_shortcode_elm('RA User Questions'); 
						$context->ra_builder_shortcode_elm('RA User Answers'); 
												
						if(qa_opt('badge_active')){
							$context->ra_builder_shortcode_elm('RA User Badges'); 
							$context->ra_builder_shortcode_elm('RA Badge History'); 
						}
					} 
				
					if ($context->template == 'question'){ 
						$context->ra_builder_shortcode_elm('Q2A Question'); 
						$context->ra_builder_shortcode_elm('Q2A Answers'); 
					} 
				
				?>
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title"><?php ra_lang('Pagination'); ?></div>
					<div data-type="ra_widget" data-name="Q2A Pagination" class="item-content widget-c">
						<?php echo $context->do_shortcode('[ra_widget name="Q2A Pagination"]'); ?>
					</div>
				</div>	
				
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title"><?php ra_lang('Suggest'); ?></div>
					<div data-type="ra_widget" data-name="Q2A Suggest" class="item-content widget-c">
						<?php echo $context->do_shortcode('[ra_widget name="Q2A Suggest"]'); ?>
					</div>
				</div>	
			</li>
		</ul>
		<ul class="nav nav-list accordion-group">
			<li class="nav-header" id="html-elm"><i class="icon-html5"></i> 
				<?php ra_lang('Elements'); ?>
			</li>
			<li class="items">
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>						
					</div>
					<div class="item-title"><?php ra_lang('H1 Title'); ?></div>
					<div class="item-content ra-editable">
						<h1>I'm an editable heading</h1>
					</div>
				</div>
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
					</div>
					<div class="item-title"><?php ra_lang('H3 Title'); ?></div>
					<div class="item-content ra-editable">
						<h3>I'm an editable heading</h3>
					</div>
				</div>
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
					</div>
					<div class="item-title"><?php ra_lang('Paragraph'); ?></div>
					<div class="item-content ra-editable">
						<p>This is a paragraph, you can edit it...</p>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
					</div>
					<div class="item-title"><?php ra_lang('Blockquote'); ?></div>
					<div class="item-content ra-editable">
						    <blockquote>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
							<small>Someone famous <cite title="Source Title">Source Title</cite></small>
							</blockquote>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
					</div>
					<div class="item-title"><?php ra_lang('Custom HTML'); ?></div>
					<div class="item-content ra-editable">
						add custom html here..
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
					</div>
					<div class="item-title">Button</div>
					<div class="item-content ra-editable">
						<a class="btn btn-default btn-xs" href="#">Button</a>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
					</div>
					<div class="item-title">About Site</div>
					<div class="item-content ra-editable">
						<div class="about-us">
							<?php echo ra_logo(); ?>
							<p>
								Dude is an online q2a site where users can ask questions and answer others question. <br />
								User can earn badges and points by their activity. Dude is a great site for shareing and gaining knowledge.
								<br /><br />
								<a href="#" class="btn btn-default">Read more</a>
							</p>
						</div>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_html_control() ?>	
						<span class="tools tab-add btn btn-default btn-xs btn-warning"><i class="icon-plus"></i></span>
						<span class="tools tab-remove btn btn-default btn-xs btn-warning"><i class="icon-minus"></i></span>
					</div>
					<div class="item-title">Tab</div>
					<div class="item-content ra-editable">
						<div class="tabbable" id="ra-tabs">
							<ul class="nav nav-tabs">
								<li class="active">
									<a href="#tab1"	data-toggle="tab">Tab1</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab">Tab2</a>
								</li>
							</ul>
							<div class="tab-content">
								<div class="tab-pane active">
									<p>Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan helvetica.</p>
								</div>
								<div class="tab-pane" >
									<p>Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid. Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four loko farm-to-table craft beer twee.</p>
								</div>
							</div>
						</div>
					</div>
				</div>					
			
			</li>
		</ul>
		<ul class="nav nav-list accordion-group">
			<li class="nav-header" id="dynamic-elm"><i class="icon-puzzle"></i> 
				Widgets
			</li>
			<li class="items">
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title">RA Ask box</div>
					<div data-type="ra_widget" data-name="Ra Ask Box" class="item-content widget-c">			
						<?php echo $context->do_shortcode('[ra_widget name="Ra Ask Box"]'); ?>
					</div>
				</div>

				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title">RA Stats</div>
					<div data-type="ra_widget" data-name="Ra Stats" class="item-content widget-c">
						<?php echo $context->do_shortcode('[ra_widget name="Ra Stats"]'); ?>
					</div>
				</div>					
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title">RA Menu List</div>					
					<div data-type="ra_widget" data-name="Ra Menu List" class="item-content widget-c">	
						<?php echo $context->do_shortcode('[ra_widget name="Ra Menu List"]'); ?>
					</div>
				</div>					
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA Questions List</div>
					<div data-type="ra_widget" data-name="Ra Questions List" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra Questions List" limit="5"]'); ?>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<button class="tools remove btn btn-default btn-xs btn-danger"><i class="icon-trashcan"></i></button>
						<span class="tools drag btn btn-default btn-xs"><i class="icon-move"></i></span>
						<button class="tools param btn btn-default btn-xs btn-danger"><i class="icon-wrench"></i></button>
						<div class="param-field" style="width:120px">
							<div class="param-wrap">
								<input type="text" name="limit" placeholder="Name of ads" style="width:100px" />
								<button class="btn btn-small icon-checkmark"></button>
							</div>
						 </div>						
					</div>
					<div class="item-title">RA Ads</div>
					<div data-type="ra_widget" data-name="Ra Ads" data-limit="5" class="item-content widget-c ads-c">						
						Enter the name of ads by clicking wrench icon
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_control(); ?>
					</div>
					<div class="item-title">RA Cat Description</div>
					<div data-type="ra_widget" data-name="RA Cat Description" class="item-content widget-c">
						<?php echo $context->do_shortcode('[ra_widget name="RA Cat Description"]'); ?>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA Answers List</div>				
					<div data-type="ra_widget" data-name="Ra Answers List" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra Answers List" limit="5"]'); ?>
					</div>
				</div>					
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA Comments List</div>					
					<div data-type="ra_widget" data-name="Ra Comments List" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra Comments List" limit="5"]'); ?>
					</div>
				</div>					
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA New Users</div>					
					<div data-type="ra_widget" data-name="Ra New Users" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra New Users" limit="5"]'); ?>
					</div>
				</div>					
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA Top Users</div>					
					<div data-type="ra_widget" data-name="Ra Top Users" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra Top Users" limit="5"]'); ?>
					</div>
				</div>	
				<?php if(qa_opt('event_logger_to_database')){ ?>
					<div class="item parent ui-draggable">		
						<div class="config">					
							<?php ra_builder_widget_control(); ?>
						</div>
						<div class="item-title">RA Events</div>					
						<div data-type="ra_widget" data-name="Ra Events" data-limit="5" class="item-content widget-c">
							<?php echo $context->do_shortcode('[ra_widget name="Ra Events" limit="5"]'); ?>
						</div>
					</div>	
				<?php } ?>
				<?php $context->ra_builder_shortcode_elm('User Activity'); ?>
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA Tags List</div>					
					<div data-type="ra_widget" data-name="Ra Tags List" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra Tags List" limit="5"]'); ?>
					</div>
				</div>					
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">RA Cat List</div>					
					<div data-type="ra_widget" data-name="Ra Categories List" data-limit="5" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Ra Categories List" limit="5"]'); ?>
					</div>
				</div>	
				<div class="item parent ui-draggable">		
					<div class="config">					
						<?php ra_builder_widget_control(); ?>
					</div>
					<div class="item-title">Full Cat List</div>					
					<div data-type="ra_widget" data-name="Full Categories List" class="item-content widget-c">						
						<?php echo $context->do_shortcode('[ra_widget name="Full Categories List"]'); ?>
					</div>
				</div>	
				<?php foreach(qa_load_modules_with('widget', 'allow_template') as $k => $widget){ ?>
					<div class="item parent ui-draggable">		
						<div class="config">					
							<?php ra_builder_control(); ?>
						</div>
						<div class="item-title"><?php echo $k; ?></div>
						<div data-type="widget" data-name="<?php echo $k; ?>" class="item-content widget-c">
							<?php 
								echo $context->do_shortcode('[widget name="'.$k.'"]');
							?>
						</div>
					</div>
				<?php } ?>
			</li>
		</ul>
	</div>
</div>

<!-- CSS Modal -->
<div id="css-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Modal title</h4>
      </div>
	<div class="modal-body">
		<div class="alert alert-success">
            <button class="close icon-cancel" type="button"></button>
            <strong><?php ra_lang('CSS saved!'); ?></strong>, <?php ra_lang('close this modal or keep editing.'); ?>
        </div>
		<textarea class="css-editor" name="css-editor"></textarea>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php ra_lang('Close'); ?></button>
		<button id="save-css-editor" data-name="" class="btn btn-default btn-xs"><?php ra_lang('Save changes'); ?></button>
	</div>
  </div>
  </div>
</div>

<!-- HTML Modal -->
<!-- CSS Modal -->
<div id="html-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">HTML editor</h4>
      </div>
		<div class="modal-body">
			<div class="alert alert-success">
				<button class="close icon-cancel" type="button"></button>
				<strong><?php ra_lang('HTML saved!'); ?>!</strong>, <?php ra_lang('close this modal or keep editing'); ?>.
			</div>
			<textarea id="html-code-editor" class="css-editor" name="css-editor"></textarea>
		</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php ra_lang('Close'); ?></button>
		<button id="save-html-editor" class="btn btn-default btn-xs"><?php ra_lang('Save changes'); ?></button>
	</div>
     </div>
    </div>
  </div>
 
 <!-- JS Modal -->
<div id="js-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">JS editor</h4>
      </div>
		<div class="modal-body">
			<div class="alert alert-success">
				<button class="close icon-cancel" type="button"></button>
				<strong><?php ra_lang('JS saved!'); ?>!</strong>, <?php ra_lang('close this modal or keep editing'); ?>.
			</div>
			<textarea id="js-code-editor" class="css-editor" name="css-editor"></textarea>
		</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php ra_lang('Close'); ?></button>
		<button id="save-js-editor" class="btn btn-default btn-xs"><?php ra_lang('Save changes'); ?></button>
	</div>
     </div>
    </div>
  </div>