;(function($){
	'use strict';

	$(function(){
		$('#submit').on('click', function(event) {
	        if ($('#delibera_commentform input[type=checkbox]:checked').length == 0) {
	            $('#nenhum-voto').show('slow').delay(5000).hide('slow');
	            return false;
	        }
	    });
	});
	
})(jQuery);

