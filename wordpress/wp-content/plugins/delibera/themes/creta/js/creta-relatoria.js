jQuery(document).ready(function() {
    jQuery('.baseadoem-checkbox').click(function() {
        if (jQuery(this).is(':checked')) {
            if (jQuery('.new-encaminhamento').attr('id') != 'new-encaminhamento') {
                show_new_encaminhamento();
            }
            
            add_author_name(jQuery(this));
        }

        if (!jQuery(this).is(':checked')) {
            remove_author_name(jQuery(this));
        }
        
        if (!jQuery('.baseadoem-checkbox').is(':checked')) {
            hide_new_encaminhamento();
        }
    });
    
    jQuery('#new-encaminhamento-cancel').click(function() {
        hide_new_encaminhamento();
    });
    
    jQuery('#new-encaminhamento-save').click(function() {
        var baseadoem_list = [];
    
        jQuery('#baseadoem-list').children().each(function(index, element) {
            baseadoem_list.push(jQuery(element).attr('id').replace('reference_to_comment_', ''));
        });
    
        jQuery('#delibera-baseouseem').val(baseadoem_list.join(','));
    });
    
    jQuery('.usar-na-votacao').click(function() {
        jQuery('body').css('cursor', 'progress');
        var checkbox = jQuery(this);
        var data = {
            action : 'delibera_definir_votacao',
            comment_id : jQuery(this).val(),
            checked : jQuery(this).is(':checked') ? 1 : 0,
        };
        
        jQuery.post(
            delibera.ajax_url,
            data,
            function(response) {
                checkbox.siblings('.usar-na-votacao-feedback').show();
                checkbox.siblings('.usar-na-votacao-feedback').delay(1500).fadeOut('slow');
                jQuery('body').css('cursor', 'auto');
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
    jQuery('.new-encaminhamento').attr('id', 'new-encaminhamento');
    jQuery('#new-encaminhamento-cancel').show();
    jQuery('#baseadoem-title').show();
    jQuery('#reply-title').hide();
}

/**
 * Esconde a caixa para construção de um novo encaminhamento com
 * base em encaminhamentos anteriores.
 */
function hide_new_encaminhamento() {
    jQuery('.new-encaminhamento').attr('id', '');
    jQuery('#new-encaminhamento-cancel').hide();
    jQuery('#baseadoem-title').hide();
    jQuery('#reply-title').show();
    jQuery('.baseadoem-checkbox').removeAttr('checked');
    jQuery('#baseadoem-list').children().remove();
}

/**
 * Adiciona o nome do autor do encaminhamento
 * selecionado na caixa de construção de um novo
 * encaminhamento. 
 */
function add_author_name(checkbox) {
    author = checkbox.parent().parent().parent().parent().find('.url').text();
    comment_id = checkbox.val();
    new_element = jQuery('<span id="reference_to_comment_' + comment_id + '"></span>');
    
    if (jQuery('#baseadoem-list').children().length > 0) {
        new_element.append(', ');
    }
    
    new_element.append('<a href="#delibera-comment-' + comment_id + '">' + author + '</a>');
    
    jQuery('#baseadoem-list').append(new_element);
}

/**
 * Remove o nome do author da caixa de construção de
 * um novo encaminhamento.
 */
function remove_author_name(checkbox) {
    comment_id = checkbox.val();
    to_remove = jQuery('#baseadoem-list').find('#reference_to_comment_' + comment_id);
    
    if (jQuery('#baseadoem-list').children().first().is(to_remove)) {
        value = to_remove.next().text().replace(', ', '');
        to_remove.next().text(value);
    }
    
    to_remove.remove();
}
