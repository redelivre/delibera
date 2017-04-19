;(function($){
	'use strict';

	$(function(){
		$('.baseadoem-checkbox').on('click', function() {
	        if ($(this).is(':checked')) {
	            if ($('.new-encaminhamento').attr('id') != 'new-encaminhamento')
	                show_new_encaminhamento();
	            
	            add_author_name($(this));
	        }

	        if (!$(this).is(':checked'))
	            remove_author_name($(this));
	        
	        if (!$('.baseadoem-checkbox').is(':checked'))
	            hide_new_encaminhamento();
	    });
	    
	    $('#new-encaminhamento-cancel').on('click', function() {
	        hide_new_encaminhamento();
	    });
	    
	    $('#new-encaminhamento-save').on('click', function() {
	        var baseadoem_list = [];
	    
	        $('#baseadoem-list').children().each(function(index, element) {
	            baseadoem_list.push($(element).attr('id').replace('reference_to_comment_', ''));
	        });
	    
	        $('#delibera-baseouseem').val(baseadoem_list.join(','));
	    });
	    
	    $('.usar-na-votacao').on('click', function() {
	    	var checkbox = $(this);
	    	
	        $('body').css('cursor', 'progress');
	        
	        $.post(
	            delibera.ajax_url,
	            {
		            action : 'delibera_definir_votacao',
		            comment_id : $(this).val(),
		            checked : $(this).is(':checked') ? 1 : 0,
		        },
	            function(response) {
	                checkbox.siblings('.usar-na-votacao-feedback').show();
	                checkbox.siblings('.usar-na-votacao-feedback').delay(1500).fadeOut('slow');
	                
	                $('body').css('cursor', 'auto');
	            }
	        );
	    });
	});
	
	/**
	 * Exibe a caixa para que o relator construa com 
	 * um novo encaminhamento com base em encaminhamentos
	 * enviados pelos demais usuários.
	 */
	function show_new_encaminhamento() {
		$('.new-encaminhamento').attr('id', 'new-encaminhamento');
		$('#new-encaminhamento-cancel, #baseadoem-title').show();
		$('#reply-title').hide();
	}
	
	/**
	 * Esconde a caixa para construção de um novo encaminhamento com
	 * base em encaminhamentos anteriores.
	 */
	function hide_new_encaminhamento() {
		$('.new-encaminhamento').attr('id', '');
		$('#new-encaminhamento-cancel, #baseadoem-title').hide();
		$('#reply-title').show();
		$('.baseadoem-checkbox').removeAttr('checked');
		$('#baseadoem-list').children().remove();
	}
	
	/**
	 * Adiciona o nome do autor do encaminhamento
	 * selecionado na caixa de construção de um novo
	 * encaminhamento. 
	 */
	function add_author_name(checkbox) {
	    author = checkbox.parent().parent().parent().parent().find('.url').text();
	    comment_id = checkbox.val();
	    new_element = $('<span id="reference_to_comment_' + comment_id + '"></span>');
	    
	    if ($('#baseadoem-list').children().length > 0)
	        new_element.append(', ');
	    
	    new_element.append('<a href="#delibera-comment-' + comment_id + '">' + author + '</a>');
	    
	    $('#baseadoem-list').append(new_element);
	}
	
	/**
	 * Remove o nome do author da caixa de construção de
	 * um novo encaminhamento.
	 */
	function remove_author_name(checkbox) {
	    comment_id = checkbox.val();
	    to_remove = $('#baseadoem-list').find('#reference_to_comment_' + comment_id);
	    
	    if ($('#baseadoem-list').children().first().is(to_remove)) {
	        value = to_remove.next().text().replace(', ', '');
	        to_remove.next().text(value);
	    }
	    
	    to_remove.remove();
	}
	
})(jQuery);