jQuery(document).ready(function() {
	
    jQuery(".delibera_like").click(function() {
        var container = jQuery(this);
        
        jQuery.post(
            delibera.ajax_url, 
            {
                action : "delibera_curtir",
                like_id : jQuery(this).children('input[name="object_id"]').val(),
                type : jQuery(this).children('input[name="type"]').val(),
            },
            function(response) {
                jQuery(container).parent().children(".delibera_unlike").remove();
                jQuery(container).parent().children('.delibera-like-count').text(response);
                jQuery(container).parent().children('.delibera-like-count').show();
                jQuery(container).remove();
            }
        );
    });
    
    jQuery(".delibera_unlike").click(function() {
        var container = jQuery(this);
        
        jQuery.post(
            delibera.ajax_url,
            {
                action : "delibera_discordar",
                like_id : jQuery(this).children('input[name="object_id"]').val(),
                type : jQuery(this).children('input[name="type"]').val(),
            },
            function(response) {
                jQuery(container).parent().children(".delibera_like").remove();
                jQuery(container).parent().children('.delibera-unlike-count').text(response);
                jQuery(container).parent().children('.delibera-unlike-count').show();
                jQuery(container).remove();
            }
        );
    });
});
