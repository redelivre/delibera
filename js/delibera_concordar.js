jQuery(document).ready(function() {

	jQuery(".delibera-like").click(function() {
		var container = jQuery(this);
		if(jQuery(this).children('input[name="object_id"]').val() > 0)
		{
			jQuery.post(
				delibera.ajax_url,
				{
					action : "delibera_curtir",
					like_id : jQuery(this).children('input[name="object_id"]').val(),
					type : jQuery(this).children('input[name="type"]').val(),
				},
				function(response) {
					container.find('.delibera-icon-thumbs-up').toggleClass('active');
					jQuery(container).children('.delibera-like-count').text(response);
					if(parseInt(jQuery(container).children('.delibera-like-count').text()) > 0)
                    {
                    	jQuery(container).children('.delibera-like-count').show();
                    }
                    else
                    {
                    	jQuery(container).children('.delibera-like-count').hide();
                    }
				}
			);
		}
	});

	jQuery(".delibera-unlike").click(function() {
		var container = jQuery(this);
        if(jQuery(this).children('input[name="object_id"]').val() > 0)
		{
			jQuery.post(
				delibera.ajax_url,
				{
					action : "delibera_discordar",
					like_id : jQuery(this).children('input[name="object_id"]').val(),
					type : jQuery(this).children('input[name="type"]').val(),
				},
				function(response) {
                    container.find('.delibera-icon-thumbs-down').toggleClass('active')
					jQuery(container).children('.delibera-unlike-count').text(response);
                    if(parseInt(jQuery(container).children('.delibera-unlike-count').text(), 10) > 0)
                    {
                    	jQuery(container).children('.delibera-unlike-count').show();
                    }
                    else
                    {
                    	jQuery(container).children('.delibera-unlike-count').hide();
                    }
				}
			);
		}
	});
});
