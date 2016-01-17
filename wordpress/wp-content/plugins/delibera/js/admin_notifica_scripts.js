function switch_mensagem_box(lang)
{
	jQuery("tr[class*=div-delibera-mensagens]").each(function ()
	{
		jQuery(this).hide();
		jQuery(this).removeClass('active');
	});
	jQuery('a[class^=\'link-delibera-mensagens\']').each(function ()
	{
		jQuery(this).removeClass('active');
	});
	jQuery('.div-delibera-mensagens-'+lang).show();
	jQuery('.div-delibera-mensagens-'+lang).addClass('active');
	jQuery('#link-delibera-mensagens-'+lang).addClass('active');
	CheckNotificacoes();
}

function CheckNotificacoes()
{		
	jQuery("#notificacoes").each(function() {
		if(this.checked)
		{
			jQuery("#painel-notificacoes").show();
			jQuery("#delibera-mensagens-notificacoes-painel").show();
		}
		else
		{		
			jQuery("#painel-notificacoes").hide();
			jQuery("#delibera-mensagens-notificacoes-painel").hide();
		}		
	});
	jQuery(".checkbox-mensagem-notificacao").each(function()
	{
		var pos = this.id.indexOf("-enabled");
		var id = this.id.substr(0, pos); 
		if(this.checked)
		{
			jQuery("[id*=row-"+id+"].active").show();
		}
		else
		{
			jQuery("[id*=row-"+id+"]").hide();
		}		
	});
}

jQuery(document).ready(function() {
	jQuery("#notificacoes").click(function() {
		CheckNotificacoes();
	});
	jQuery(".checkbox-mensagem-notificacao").click(function() {
		CheckNotificacoes();
	});
	CheckNotificacoes();
});