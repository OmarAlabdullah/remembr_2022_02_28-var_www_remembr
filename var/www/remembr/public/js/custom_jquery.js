// JavaScript Document

$(document).ready(function(){

	$('.head_links ul li.notification ul li:first').append('<cite class="top_arrow"></cite>');
	$('.head_links ul li.message ul li:first').append('<cite class="top_arrow"></cite>');
	$('.head_links2 ul li.sitting ul li:first').append('<cite class="top_arrow"></cite>');
	$('.head_links2 ul li.page_name ul li:first').append('<cite class="top_arrow"></cite>');


	$('.styled[type=checkbox]').change(function(){
		if($(this).is(':checked')){
			$(this).parents('label').addClass('active');
		}else{
			$(this).parents('label').removeClass('active');
		}
	});

	$('a.view_comment').click(function(){
		//$(this).parents('.row_btm').find('.view_comment_open').slideToggle();
		$(this).parents('.row_content ul li').removeClass('active');
		return false;
	})

	$('a.more_link').click(function(){
		//$(this).parents('.row_content ul li').toggleClass('active');
		//$(this).parents('.view_comment_open').find('.read_more_box').slideToggle();
		//return false;
	})

	$('.row_content ul li .hover_img').hover(function(){
		$(this).parents('.row_content ul li').find('.star').css('display','block');
	},function(){
		$(this).parents('.row_content ul li').find('.star').css('display','none');
	});

	// var grd = $("#grid-content").vgrid();

	var hsort_flg = true;
	$('.star a.star_icon_hov').click(function(){
		$('#grid-content li').removeClass('height_big');
		$('#grid-content li').find('h1').remove();
		$(this).parents('.row_content ul li').toggleClass('height_big');
		$(this).parents('.row_content ul li').prepend('<h1>1</h1>');
		var cnt = 1;
		$('#grid-content li').each(function(){
			if($(this).find('h1').text() != '1'){
				cnt++;
				$(this).prepend('<h1>'+cnt+'</h1>');
			}
		});
		$("#grid-content").vgsort(function(a, b){
			var _a = parseInt($(a).find('h1').text());
			var _b = parseInt($(b).find('h1').text());
			var _c = hsort_flg ? 1 : -1 ;
			return (_a < _b) ? _c * -1 : _c ;
		});
		return false;
	})

	$('.row_content ul li').hover(function(){
		$(this).css('z-index','2');
		$(this).prev('li').css('z-index','1');
		$(this).next('li').css('z-index','1');
	});



});
