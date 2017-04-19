jQuery(document).ready(function()
{
	  jQuery('.label-voto').expander({
		  slicePoint: 70,
		  expandText: '[leia o encaminhamento inteiro]',
		  expandPrefix: '... ',
		  userCollapseText: '[fechar visão completa]'   
	  });
	  	    
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

function delibera_edit_comment_show(comment)
{
	jQuery('#delibera-comment-text-'+comment).toggle();
	jQuery('#delibera-edit-comment-'+comment).toggle();
}
