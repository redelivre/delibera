function delibera_basearse_remove(val,id)
{
	jQuery('#painel-baseouseem-item-'+val).remove();
	jQuery('#'+id).attr('checked', false);
	var text = jQuery('#delibera-baseouseem').val();
	text = text.replace('[delibera_basear id="'+val+'"],', '');
	jQuery('#delibera-baseouseem').val(text);
}
function delibera_basearse(checkbox)
{
	var dom_checkbox = jQuery(checkbox);
	var link = jQuery('#delibera-div-comment-header-'+dom_checkbox.val()+' .vcard .fn a');
	var autor = link.text();
	if(dom_checkbox.attr("checked"))
	{
		jQuery('#delibera-baseouseem').val(jQuery('#delibera-baseouseem').val()+'[delibera_basear id="'+dom_checkbox.val()+'" autor="'+autor+'"],');
		var hrefs = document.location.href.split('#');
		var site_href = hrefs[0];
		var img_excluir = '<div class="painel-baseouseem-button-remove" onclick="delibera_basearse_remove(\''+dom_checkbox.val()+'\', \''+checkbox.id+'\');" ></div>'; 
		jQuery('#painel-baseouseem').append('<div id="painel-baseouseem-item-'+dom_checkbox.val()+'" class="painel-baseouseem-item" ><div id="painel-baseouseem-link-'+dom_checkbox.val()+'" class="painel-baseouseem-link" ><a href="'+site_href+'#delibera-comment-'+dom_checkbox.val()+'">@'+autor+'</a>'+img_excluir+'</div>, </div>');
	}
	else
	{
		jQuery('#painel-baseouseem-item-'+dom_checkbox.val()).remove();
		var text = jQuery('#delibera-baseouseem').val();
		text = text.replace('[delibera_basear id="'+dom_checkbox.val()+'" autor="'+autor+'"],', '');
		jQuery('#delibera-baseouseem').val(text);
	}
}
jQuery(document).ready(function(){
	jQuery('input[name=\'baseadoem-checkbox[]\']').each(function(){
		jQuery(this).click(function (){delibera_basearse(this);});
	});
});