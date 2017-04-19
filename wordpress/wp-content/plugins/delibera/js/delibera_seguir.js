jQuery(document).ready(function() {
    jQuery('#delibera_seguir').click(function(event) {

        event.preventDefault();

        if (jQuery('#delibera-seguir-text').is(':visible')) {
            var type = 'seguir';
        } else {
            var type = 'nao_seguir';
        }
        
        jQuery.post(
            delibera.ajax_url,
            {
                action : "delibera_seguir" ,
                seguir_id : delibera.post_id,
                type : type
            },
            function() {
                jQuery('#delibera-seguir-text').toggle();
                jQuery('#delibera-seguindo-text').toggle();
                
                jQuery('#delibera_seguir').toggleClass('seguir');
                jQuery('#delibera_seguir').toggleClass('seguindo');
            }
        );
    });
});