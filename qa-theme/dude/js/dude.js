function dropdown_override() {
	$('.main-menu .dropdown').hover(function() {
			$(this).stop(true, true).addClass('open');

	}, function() {

			$(this).stop(true, true).removeClass('open');

	});

	$('.main-menu .dropdown > a').click(function(){
		
			location.href = this.href;
	});

}
function admin_option_tab(){
	
	$('#theme_option_tab a:first').tab('show');
	
	$('.path-popover').popover({'placement':'bottom'});

	$('body').on('click', function (e) {
		$('.path-popover').each(function () {
			if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
				$(this).popover('hide');
			}
		});
	});
}
function ra_favorite_click()
{
	$('#main').delegate( '.fav-btn', 'click', function() {
		ra_ajax_loading(this);
		var ens 	=	$(this).data('id').split('_');
		var code	=	$(this).data('code');
		var elem	=	$(this);
		qa_ajax_post('favorite', {entitytype:ens[1], entityid:ens[2], favorite:parseInt(ens[3]), code:code},
			function (lines) {
				if (lines[0]=='1'){
					
					elem.parent().empty().html(lines.slice(1).join("\n"));
					$('.fav-btn').tooltip({placement:'bottom'});
				}else if (lines[0]=='0') {
					alert(lines[1]);
					//ra_remove_process(elem);
				} else
					qa_ajax_error();
			}
		);
		
		//ra_process(elem, false);
		
		return false;
	});
}

function ra_process(elem, inside)
{
	$(elem).addClass('.process');
}
function ra_remove_process(elem, inside)
{
	$(elem).removeClass('.process');
}
function ra_admin_colorpicker(){
	if ($('.ra_colorpicker').length > 0)
	$('.ra_colorpicker').spectrum({
		showInput: true,
		preferredFormat: "hex",
		clickoutFiresChange: true
	});
	
}


// for expanding of comment
function ra_expand_comment(){
	$('.comments').delegate('.show-full-comment', 'click', function(){
		if($('body').find('.comment-expanded').length>0){
			$('.comment-expanded').prev().show();
			$('.comment-expanded').attr('style', '').removeClass('comment-expanded');
		}
		
		$(this).next().addClass('comment-expanded').animate({'height': $(this).next().find('.initial-height').height()}, 300);
		$(this).hide();
	});
}

function profile_priv_expand(){
	$('.show-priv').click(function(e){
		e.preventDefault();
		$('.all-priv').slideToggle(300);
	});

}

function ra_vote_click(){
	$('body').delegate('.vote-up, .vote-down', 'click', function(){
		ra_ajax_loading(this);
		if (typeof ($(this).data('id')) != 'undefined'){
			var ens=$(this).data('id').split('_');
			var parent = $(this).parent();
			var postid=ens[1];
			var vote=parseInt(ens[2]);
			var code=$(this).data('code');
			var anchor=ens[3];
			
			qa_ajax_post('vote', {postid:postid, vote:vote, code:code},
				function(lines) {
					if (lines[0]=='1') {
						qa_set_inner_html(document.getElementById('voting_'+postid), 'voting', lines.slice(1).join("\n"));
						$('.voting a').tooltip({placement:'bottom'});
						

					} else if (lines[0]=='0') {						
						ra_alert(lines[1]);					
					} else
						qa_ajax_error();
				}
			);	
		}
		return false;
	});	
}


function ra_alert($mesasge){
	if($('#ra-alert').length > 0)
		$('#ra-alert').remove();
	var html = '<div id="ra-alert" class="alert fade in"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>'+$mesasge+'</div>';
	$(html).appendTo('body');
	$('#ra-alert').css({left:($(window).width()/2 - $('#ra-alert').width()/2)}).animate({top:'50px'},300);
}

function ra_error(){
	if($('#ra-alert').length > 0){
		$('#ra-alert').css('left',($(window).width()/2) - ($('#ra-alert').width()/2));
		$('#ra-alert').fadeIn(300).animate({'top':'54px'},500);
	}
}

function ra_ajax_loading($elm){
	var position = $($elm).offset();
	var html = '<div id="ajax-loading"></div>';	
	$(html).appendTo('body').ajaxStart(function () {
		$('#ajax-loading').css(position);
		$(this).show();
	});

	$("#ajax-loading").ajaxStop(function () {
		$(this).remove();
	});
}

function ra_toggle_editor(){	
	$( '#q_doanswer' ).on('click', function(event) {
		event.preventDefault();
		$('html, body').animate({
			scrollTop: $('#anew').offset().top
		}, 500);
	});
}

function ra_upload_cover(){
	$('#upload-cover').one( 'click', function(){
		$.ajax({
            type: 'POST',
            data: {
				ra_ajax: true,
                action: 'upload_cover',
            },
            dataType: 'html',
            context: this,
            success: function (response) {
				$('body').append(response);
				$('#cover-uploader').modal('show');

            },
        });
	});
}

function ra_is_scroll_to(elem) {
	var docViewTop = $(window).scrollTop(); //num of pixels hidden above current screen
	var docViewBottom = docViewTop + $(window).height();

	var elemTop = $(elem).offset().top; //num of pixels above the elem
	var elemBottom = elemTop + $(elem).height();

	return ((elemTop <= docViewTop));
}

function ra_user_popover(){	
	$('body').on('mouseenter', '.avatar[data-handle]', function( event ) {
		
		if($('.user-popover').is(':visible'))
			$('.user-popover').hide();

		var handle = $(this).data('handle');
		var userid = $(this).data('id');
		var offset = $(this).offset();
		var $this = $(this);
		
		popover_time = setTimeout(function(){
			if($('body').find('#'+userid+'_popover').length == 0 && (handle.length > 0)){
			$this.addClass('mouseover');
				$.ajax({
					type: 'POST',
					data: {
						ra_ajax: true,
						action: 'user_popover',
						handle: handle,
					},
					dataType: 'html',
					context: $this,
					success: function (response) {
						$('body').append(response);
						$('#'+userid+'_popover').position({my: 'center bottom',at: 'center top', of:$this, collision: 'fit flip'});
						$('#'+userid+'_popover').show();
						$this.delay(500).queue(function() {$this.removeClass('mouseover'); $this.dequeue();});
					},
				});
			}else{
				//if($('.user-popover').is(':visible'))
					//$('.user-popover').hide();
				//$(this).addClass('mouseover');	
				$('#'+userid+'_popover').removeAttr('style');
				$('#'+userid+'_popover').position({my: 'center bottom',at: 'center top', of:$this, collision: 'fit flip'});
				$('#'+userid+'_popover').show();
			}
		},500);
	}).on('mouseleave', '.avatar[data-handle]', function( event ) {
		clearTimeout(popover_time);
		var userid = $(this).data('id');
		$('#'+userid+'_popover').hide();
		$(this).removeClass('mouseover');
	});
}

function ra_admin_height_adjust(){

}

function ra_ads_multi_text(){
	$(document).ready(function(){

		$('.ra-multitext-add').click(function(){
			var count = ($(this).parent().find('.ra-multitext-list').length);
			
			var html = $(this).parent().find('.ra-multitext-list:last-child').clone();
			var id = $(this).parent().find('.ra-multitext-list:last-child').data('id');
			html.find('.name').attr('name', id+'['+count+'][name]');
			html.find('.code').attr('name', id+'['+count+'][code]');
			$(this).parent().find('.ra-multitext-append').append(html);
		
		});
		$('body').delegate('.ra-multitext-delete', 'click' ,function(){
			$(this).parent('.ra-multitext-list').remove();
		});
	});
}

function ra_quick_answer(){
	$('.quick-answer').keyup(function(e){
		if(e.keyCode == 13)
		{
			$(this).trigger("enterKey");
		}
	});
	$('.quick-answer').bind("enterKey",function(e){
			var p = $(this).parent();
			$.ajax({
				type : "POST",
				dataType : "text",
				data: {
					ra_ajax: true,
					action: 'add_answer',
					a_questionid: p.data('id'),
					a_content: $(this).val(),
					a_notify: '1',
					a_doadd: '1',
					a_editor: 'WYSIWYG Editor',
					code: p.data('code'),
					//value: $(this).text()
				},
				beforeSend:function (){$(this).prop('disabled', true)},
				context:this,
				success: function (response) {				
					$(this).hide();
					p.find('p').hide();
					p.append('<p class="success">Your answer has been posted successfully</p>');
				},
				error : function() {
					$(this).hide();
					p.find('p').hide();
					p.append('<p class="error">We are unable to post your answer</p>');
				}
			});

	});
}
function show_badge_source(){
	$('.badge-user-block .icon-chevron-down').click(function(){
		$(this).next().toggle();
	});
}
function ra_float_vote(){
	$(window).scroll(function(){
	var st = $(this).scrollTop();

	$('.vote-float').each(function(){
		var $this = $(this), 
			offset = $this.offset(),
			h = $this.height(),
			$float = $this.find('.vote-c'),
			floatH = $float.height(),
			topFloat = ra_nav_fixed ? '45' : 0;
		if(st >= offset.top-topFloat && st < offset.top + h-topFloat - floatH){
			$float.css({'position':'fixed', 'top':topFloat+'px'});
		}else{
			$float.css({'position':'absolute', 'top':0});
		}
	})
	});
}
$(document).ready(function(){
	$('.ra-tip, .builder-elem .buttons button').tooltip({placement:'bottom'});
	$('.fav-btn').tooltip({placement:'top'});
	
	if(!$('body').is('.qa-template-admin'))
		$('#dl-menu' ).dlmenu();
	
	ra_expand_comment();
	dropdown_override();
	admin_option_tab();
	ra_admin_height_adjust();
	ra_favorite_click();
	ra_vote_click();
	ra_admin_colorpicker();
	profile_priv_expand();
	ra_error();
	ra_toggle_editor();
	ra_upload_cover();
	ra_user_popover();
	ra_ads_multi_text();
	ra_quick_answer();
	ra_float_vote();
	show_badge_source();

	
	if ((typeof qa_wysiwyg_editor_config == 'object') && $('body').hasClass('qa-template-question'))
		qa_ckeditor_a_content=CKEDITOR.replace('a_content', window.qa_wysiwyg_editor_config);
		
	$('.social-buttons').one('mouseenter', function(){
		Socialite.load($(this)[0]);
	});
});