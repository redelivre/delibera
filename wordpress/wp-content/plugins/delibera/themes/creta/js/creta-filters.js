jQuery(document).ready(function() {
    jQuery('.status span').click(function() {
        jQuery(this).toggleClass('selected');
        
        if (jQuery(this).children('input').val() == 'on') {
            jQuery(this).children('input').val('');
        } else {
            jQuery(this).children('input').val('on');
        }
    });
});
