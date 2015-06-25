function ra_save_layout() {
    // update post ajax query
    $('#save-builder, #clear-builder').click(function () {
		if(!$(this).is('.clear')){
			$('.edit-canvas .item-title').each(function () {
				$(this).remove();
			});			

			var content = {};
			$('.edit-canvas').each(function() {
				var name = $(this).data('name');
				var cloned = $(this).clone();
				cloned.find('[contenteditable="true"]').removeAttr('contenteditable');
				cloned.find('.html-modal-active').removeClass('html-modal-active');
				cloned.find('.widget-c').each(function () {
					var widget_name = $(this).attr('data-name');
					var type = $(this).data('type');
					var limit = $(this).data('limit');
					var shortcode = '['+type+' name="' + widget_name + '" '+(typeof limit != 'undefined' ? 'limit="'+limit+'"':'' )+']';
					$(this).empty().text(shortcode);
				});
				content[name] = cloned.html().replace(/\s+/g, " ");				
			});

		}else{
			if (confirm('Are you sure you want to clear layout of this page? it cannot be undone.')) {
				var content = {};
				$('.edit-canvas').each(function() {
					var name = $(this).data('name');
					
					content[name] = '';				
				});
			}else{
				return;
			}			
			
		}

		$.ajax({
            type: 'POST',
            data: {
				ra_ajax: true,
                action: 'save_builder_data',
                value: content
            },
            dataType: 'html',
            context: this,
            success: function (response) {
				if($(this).is('.clear')){
					console.log('All layout cleared');
				}else{
					console.log('Saved');
				}
                window.location.reload(false); 
            },
        });
    });
}

function ra_save_css_code() {
    $('#css-modal .alert .close, #html-modal .alert .close').click(function(){
		$(this).parent().fadeOut('fast');
	});
	
	$('#open-css-editor').click(function(){
		if($(this).is('.added')){
			$('.css-choose-block').show();
		}else{
			$('.edit-canvas').each(function() {
				var name = $(this).data('name');
				$('.css-choose-block').append('<a id="open-css-editor" href="#css-modal" role="button" data-toggle="modal" data-name="'+name+'" title="Insert CSS for '+name+'">'+name+'</a>');
			});
			$('.css-choose-block').show();
			$(this).addClass('added');
		}
		
	});
	
	$('.css-choose-block').click(function(){
		$(this).hide();
	});
	$('.css-choose-block').delegate('a', 'click', function(){
		var id = $(this).attr('href');
		var name = $(this).attr('data-name');
		$(id).find('#myModalLabel').text('CSS style for '+name);
		$(id).find('#save-css-editor').attr('data-name', name);
		$.ajax({
			type: 'GET',
			data: {
				ra_ajax: true,
				action: 'get_builder_css',
				name: name,
			},
			dataType: 'html',
			context: this,
			success: function (response) {				
				$(id).find('textarea').val(response);
			},
		});
	});
	
    $('#save-css-editor').click(function () {
		var name = $(this).attr('data-name');
		var content = $('#css-modal').find('textarea').val().trim();
		$.ajax({
			type: 'POST',
			data: {
				ra_ajax: true,
				action: 'save_builder_css',
				name: name,
				value: content
			},
			dataType: 'html',
			context: this,
			success: function (response) {				
				$('#css-modal').find('.alert').fadeIn(300);
			},
		});
    });
}

function ra_save_js_code() {
    $('#js-modal .alert .close').click(function(){
		$(this).parent().fadeOut('fast');
	});

	$('.builder-elem').delegate('a.open-js-modal', 'click', function(){
		var name = $('.edit-canvas:eq(0)').attr('data-name');
		
		$('#js-modal').find('#save-js-editor').attr('data-name', name);
		$.ajax({
			type: 'GET',
			data: {
				ra_ajax: true,
				action: 'get_builder_js',
				name: name,
			},
			dataType: 'html',
			context: this,
			success: function (response) {				
				$('#js-modal').find('textarea').val(response);
			},
		});
		$('#js-modal').modal('show');

	});
	
    $('#save-js-editor').click(function () {
		var name = $(this).attr('data-name');
		var content = $('#js-modal').find('textarea').val().trim();
		$.ajax({
			type: 'POST',
			data: {
				ra_ajax: true,
				action: 'save_builder_js',
				name: name,
				value: content
			},
			dataType: 'html',
			context: this,
			success: function (response) {				
				$('#js-modal').find('.alert').fadeIn(300);
			},
		});
    });
}
function ra_tabs() {
	if ($('.edit-canvas #ra-tabs').length >0){
		var id = $('.edit-canvas .tabbable').length;
		var t = $(".edit-canvas .tabbable").attr('id', 'ra-tabs-'+id);
		var i = 1;
		$('.edit-canvas .tab-pane').each(function(){
			var p = $(this).closest('.tabbable').attr('id');
			$(this).attr('id', p+'-pane'+i);
			$(this).closest('.tabbable').find('ul li:nth-child('+i+') a').attr('href', '#'+p+'-pane'+i);	
			i++;		
		});
	}
}
function ra_textarea_indent(){
	$(document).delegate('textarea', 'keydown', function(e) {
	  var keyCode = e.keyCode || e.which;

	  if (keyCode == 9) {
		e.preventDefault();
		var start = $(this).get(0).selectionStart;
		var end = $(this).get(0).selectionEnd;

		// set textarea value to: text before caret + tab + text after caret
		$(this).val($(this).val().substring(0, start)
					+ "\t"
					+ $(this).val().substring(end));

		// put caret at right position again
		$(this).get(0).selectionStart =
		$(this).get(0).selectionEnd = start + 1;
	  }
	});
}

function ra_tools(){
	$(".edit-canvas").delegate('.open-html-modal', 'click', function (e) {
		$('.html-modal-active').removeClass('html-modal-active');
		$(this).closest('.item').find('.item-content').addClass('html-modal-active');
		var html = $(this).closest('.item').find('.item-content').html();
		$('#html-modal').find('textarea').val(html);
		$('#html-modal').find('.alert').hide();

	});
	
	$("body").delegate('#save-html-editor', 'click', function (e) {
		var newhtml = $('#html-modal').find('textarea').val();
		$('.html-modal-active').html(newhtml);
		$('#html-modal').find('.alert').fadeIn(300);
	});
	
	
	$(".edit-canvas").delegate('.tab-add', 'click', function (e) {
		var target = $(this).closest('.item').find('.tabbable');
		var target_id = target.attr('id');
		var panel_n = target.find('.tab-content > div').length;
		$('<li><a href="#'+target_id+'-pane'+(panel_n+1)+'" data-toggle="tab">Tab'+(panel_n+1)+'</a></li>').appendTo(target.find('.nav-tabs'));
		$('<div id="'+target_id+'-pane'+(panel_n+1)+'" class="tab-pane"><p>Your Tab'+(panel_n+1)+' contents here</p></div>').appendTo(target.find('.tab-content'));
	});
	$(".edit-canvas").delegate('.tab-remove', 'click', function (e) {
		var target = $(this).closest('.item').find('.tabbable');
		$(target).find('.active').each(function(){
			$(this).animate({background:'red'},200).remove();
		});
	});
	$('body').delegate('.widget-tab .nav a', 'click', function (e) {
		e.preventDefault();
		$(this).tab('show');
    })

	$(".edit-canvas").delegate('.thumbnail-add', 'click', function () {
		var target = $(this).closest('.item').find('.thumbnails');	
		var object = target.children('li:last-child').clone();	
		if(target.find('li').length <4){
			$(object).appendTo(target);
			var n = target.find('li').length;
			target.find('li').each(function(){
				$(this).removeClass();
				$(this).addClass('span'+(12/n));
			});
		}else if(target.find('li').length == 4){
			$(target).append(object);
			$(target).append(target.children('li:last-child').clone());
			var n = target.find('li').length;
			target.find('li').each(function(){
				$(this).removeClass();
				$(this).addClass('span'+(12/n));
			});
		}else{
			alert('maximum six items');
		}
	});

	$(".edit-canvas").delegate('.media-add', 'click', function () {
		var target = $(this).closest('.item').find('.item-content');	
		var object = target.children('.media:last-child').clone();		
		$(object).appendTo(target);
	});		

	$(".edit-canvas").delegate('.pricing-add', 'click', function () {
		var target = $(this).closest('.item').find('.ra-pricing');	
		var object = target.children('.ra-pricing>div:last-child').clone();
		if(target.children('div').length <4){				
			$(object).appendTo(target);
			var n = target.children('div').length;
			target.children('div').each(function(){
				$(this).removeClass();
				$(this).addClass('span'+(12/n));
			});
		}else{
			alert('Maximum four tables');
		}
	});
	
	$(".edit-canvas").delegate('.timeline-add', 'click', function () {
		var target = $(this).closest('.item').find('.item-content');	
		var object = target.children('.ra-timeline:last-child').clone();
		$(object).appendTo(target);
	});	
}

function ra_create_grid() {
    $('.rows input.row-count, .rows select.ra-contain').bind('keyup change', function () {
        var e = 0;
        var t = "";
        var n = false;
        var r = $('.rows input.row-count').val().split(" ", 12);
        var c = $('.rows select.ra-contain').val();
        $.each(r, function (r, i) {
            if (!n) {
                if (parseInt(i) <= 0) n = true;
                e = e + parseInt(i);
                t += '<div class="col-md-' + i + ' column"></div>'
            }
        });
        if (c == 'yes') {
            var grid = '<div class="container"><div class="row">' + t + '</div></div>';
        } else if (c == 'no') {
            var grid =  '<div class="row">'+t+'</div>';
        } else {
            var grid = t;
        }
        if (e == 12 && !n) {
            $('.rows').find('.item-content').children().html(grid);
            $('.rows').find('.drag').css('display', 'inline-block');
        } else {
            $('.rows').find('.drag').hide();
        }
    })
}

function ra_config(e, t) {
    $(".edit-canvas").delegate(".config > a", "click", function (e) {
        e.preventDefault();
        var t = $(this).parent().next().next().children();
        $(this).toggleClass("active");
        t.toggleClass($(this).attr("rel"))
    });
    $(".edit-canvas").delegate(".config .dropdown-menu a", "click", function (e) {
        e.preventDefault();
        var t = $(this).parent().parent();
        var n = t.parent().parent().next().next().children();
        t.find("li").removeClass("active");
        $(this).parent().addClass("active");
        var r = "";
        t.find("a").each(function () {
            r += $(this).attr("rel") + " "
        });
        t.parent().removeClass("open");
        n.removeClass(r);
        n.addClass($(this).attr("rel"))
    })
}

function ra_remove() {
    $(".edit-canvas").on('click', '.remove', function (e) {
        e.preventDefault();        
		$(this).closest('.parent').css('background','red').fadeOut(300, function(){
			$(this).remove()
		});
    })
}

function ra_page_option_checkbox() {
    $('.page-options .checkbox input').each(function () {
        if ($(this).is(':checked')) {
            $($(this).data('toggle')).slideUp('fast');
        } else {
            $($(this).data('toggle')).slideDown('fast');
        }
    });
}

function ra_editor_carousel(){
	$('.edit-canvas .carousel .ra-editable').each(function(){
		$(this).removeClass('ra-editable');
	});
	$('.edit-canvas .carousel .config').each(function(){
		$(this).remove();
	});
}

function ra_param_button(){
	$('.edit-canvas').delegate('.param', 'click', function(){
		var val = $(this).closest('.parent').find('.widget-c').data('limit');
		$(this).next().find('input[name="limit"]').val(val);
		$(this).next().toggle();
	});	
	$('.edit-canvas').delegate('.param-field button', 'click', function(){
		var val = $(this).prev().val();
		var el = $(this).closest('.parent').find('.widget-c');
		el.data('limit', val);
		el.attr('data-limit', val);
		$(this).closest('.param-field').hide();
	});
}

function ra_edit_texts(){
	$('.enable-front-edit').click(function(){
		$('.ra-editable').each(function(){
			if($(this).is('.page-title'))
			$(this).attr('contenteditable','true').addClass('save');
			
			$(this).attr('contenteditable','true');
			if($(this).html().length==0)
				$(this).text('Click here to edit')
		});
	});
	

	//on blur save changed value
	
	$('.page-title').focusout(function(){
		if ($(this).is('.save')){
			$.ajax({
				type : "GET",
				dataType : "text",
				data: {
					ra_ajax: 'true',
					action: 'save_home_title',
					value: $(this).text()
				},
				error : function() {
					//alert("Sorry, The requested property could not be found.");
				}
			});
		}
	});
}

$(window).resize(function () {

    $(".builder-elem").css("height", $(window).height() - 45);
    $(".edit-canvas").css("min-height", $(window).height() - 160);
	$(".element-container").css("height", $(window).height() - 80);
});
$(document).ready(function ($) {
    $(".builder-elem").css("height", $(window).height() - 45);
    $(".element-container").css("height", $(window).height() - 80);
    $(".edit-canvas").css("min-height", $(window).height() - 160);
    $('.ra-row input.row-count').attr('value', '6 6');

    ra_page_option_checkbox();
    $('.page-options .checkbox input').click(function () {
        ra_page_option_checkbox();
    });

    $(".edit-canvas, .edit-canvas .column").sortable({
        connectWith: ".column",
        opacity: .35,
        placeholder: 'placeholder',
        handle: ".drag",
        start: function (e, ui) {
            ui.placeholder.height(100);
        }
    });
    $(".builder-elem .ra-row").draggable({
        connectToSortable: ".edit-canvas",
        helper: "clone",
        handle: ".drag",
        drag: function (e, t) {
            t.helper.width(158);
            t.helper.height(50);
			$(".builder-elem").hide();
        },
        stop: function (e, t) {
			$(".builder-elem").show();
            $(".edit-canvas .column").sortable({
				cancel: '.ra-editable, input',
                opacity: .35,
                placeholder: 'placeholder',
                connectWith: ".column",
                start: function (e, ui) {
                    ui.placeholder.height(ui.item.height());
					ui.helper.height(50);
                }
            })
        }
    });
    $(".builder-elem .item").draggable({
        connectToSortable: ".column",
        helper: "clone",
        handle: ".drag",
        drag: function (e, t) {
            t.helper.width(158);
			$(".builder-elem").hide();
        },
        stop: function () {            
			ra_tabs();
			$(".builder-elem").show();
        }
    });


    $(".nav-header").click(function () {
        $('.nav-header').removeClass('active');
        $('.element-container > ul > li').next().hide();
        $(this).next().slideDown();
        $(this).addClass('active');
    });
	
	$('.buttons .btn').click(function(){
		if($(this).is('.ra-update-post') || $(this).is('.ra-update-post-close')){
			$(this).addClass('btn-progress');
		}
	});

		
    $('.builder-elem #grid-elem.nav-header').click();
	$('.param-field').hide();
    ra_remove();
    ra_create_grid();
    ra_save_layout();
	ra_tools();	
	ra_config();
	ra_editor_carousel();
	ra_param_button();
	ra_edit_texts();
	ra_save_css_code();
	ra_save_js_code();
	ra_textarea_indent();
})
