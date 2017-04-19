;(function($){
	'use strict';

	$(function(){
		$('.status span').on('click', function(){
			$(this).toggleClass('selected');
			
			$(this).children('input').val() == 'on' ? $(this).children('input').val('') : $(this).children('input').val('on');
		});
	});
	
})(jQuery);

