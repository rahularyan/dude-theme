
/*
	RA Social
	Author: Rahul Aryan
	Website: http://www.rahularyan.com
	Licence: GPLv3
*/
$(document).ready(function(){

	$('.ra-social-add').click(function(){
		var count = ($('.ra-social-list').length);
		
		var html = $('.ra-social-list:last-child').clone();
		html.find('.site').attr('name', 'ra_social_links['+count+'][site]');
		html.find('.link').attr('name', 'ra_social_links['+count+'][link]');
		html.find('.icon').attr('name', 'ra_social_links['+count+'][icon]');
		$('.ra-social-append').append(html);
	
	});
	$('body').delegate('.ra-social-delete', 'click' ,function(){
		$(this).parent('.ra-social-list').remove();
	});
});