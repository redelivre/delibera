jQuery(document).ready(function() {
    jQuery('#submit').click(function() {
        if (jQuery('#delibera_commentform input[type=checkbox]:checked').length == 0) {
            jQuery('#nenhum-voto').show('slow').delay(5000).hide('slow');
            return false;
        }
    });
});