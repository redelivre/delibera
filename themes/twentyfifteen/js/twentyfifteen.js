;(function($){
	'use strict';

	$(function(){
		switch (delibera.situation) {
			case 'validacao':
				// controla os botões para aprovar ou rejeitar a entrada de uma pauta para discussao
				$('#painel_validacao .btn').on('click', function() {
					$('#delibera_validacao').val($(this).hasClass('btn-success') ? 'S' : 'N');
				});
				break;
			case 'emvotacao':
				// ajusta o estilo do botão de votação dos encaminhamentos de uma pauta
				$('.encaminhamentos .submit #submit').addClass('btn btn-success');
				break;
			case 'comresolucao':
				$('.comentario_coluna2').hide();
				break;
			default:
		}
	    
	    // adiciona classes no botão de responder um comentário gerado pelo WP
	    $('.comment-reply-link').addClass('btn btn-mini btn-info');
	    $('.delibera_before_fields > .form-submit > #submit').addClass('btn btn-info');
	    $('.submit-edit-comment-button').addClass('bottom');
	    $('.submit-edit-comment-button-text').addClass('btn btn-info');
	    $('.delibera-edit-comment-form').addClass('clearfix');
	});
	
})(jQuery);