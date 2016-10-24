jQuery(document).ready(function()
{
	  if (window.location.href.indexOf('#comment') > 0 && document.referrer == window.location.href.substring(0,window.location.href.indexOf('#')))
	  {
  		jQuery("#mensagem-confirma-voto").show();
  		jQuery("#mensagem-confirma-voto").fadeOut(5000);
	  }

	  //Abrir hide de comentário se tiver no link
	  var hash = location.hash.slice(1);
	  	if(hash != null && hash != '')
	  	{
	  		var comment = hash.replace('delibera-comment-', '');
	  		jQuery('#showhide-comment-part-text-'+comment).hide();
	  		jQuery('#showhide_comment'+comment).show();
		}

    delibera_setpdf_size("#pauta-pdf-content");

    jQuery(window).resize(function(){
        delibera_setpdf_size("#pauta-pdf-content");
    });
    if (typeof Socialite != 'undefined')
    	Socialite.load();
});

function delibera_setpdf_size(element) {
    jQuery(element).height(jQuery(window).height() - 50);
}

function delibera_showhide(comment)
{ // Hide the "view" div.
	jQuery('#showhide-comment-part-text-'+comment).slideToggle(400);
	jQuery('#showhide_comment'+comment).slideToggle(400);

	return false;
}

function delibera_edit_comment_show(button, comment)
{
	jQuery('#delibera-comment-text-'+comment).toggle();
	jQuery('#delibera-edit-comment-'+comment).toggle();
	jQuery('#submit-edit-comment-button-'+comment).toggle();
	if(jQuery(button).html() == '<i class="delibera-icon-edit"></i>')
	{
		jQuery(button).html('<i class="delibera-icon-cancel"></i>'); // TODO translate
	}
	else
	{
		jQuery(button).html('<i class="delibera-icon-edit"></i>'); // TODO translate
	}
}
