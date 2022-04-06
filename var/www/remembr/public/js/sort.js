// JavaScript Document

$(document).ready(function() {
	$('.sort_list a').click(function() {
		$(this).css('outline','none');
		$('.sort_list .current').removeClass('current');
		$(this).parent().addClass('current');
		
		var filterVal = $(this).text().toLowerCase().replace(' ','-');
				
		if(filterVal == 'show-all') {
			$('ul#grid-content li.hidden').fadeIn('slow').removeClass('hidden');
		} else {
			
			$('ul#grid-content li').each(function() {
				if(!$(this).hasClass(filterVal)) {
					$(this).fadeOut('normal').addClass('hidden');
				} else {
					$(this).fadeIn('slow').removeClass('hidden');
				}
			});
		}
		$(window).resize();
		return false;
	});
});
