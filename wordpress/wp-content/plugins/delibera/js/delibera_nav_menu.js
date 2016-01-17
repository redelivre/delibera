jQuery(document).ready(
	function()
	{
		jQuery('[value="delibera-menu-item"]').each(
			function()
			{
				var idf = jQuery(this).attr('id');
				var id = idf.substr(idf.lastIndexOf('-') + 1);
				delibera_custom_nav_menu(id);
			}
		);
	}
);
function delibera_custom_nav_menu( id )
{
	jQuery('#menu-item-'+id+' .item-type').text('Delibera')
	jQuery('#menu-item-'+id+' .field-url').hide();
}
function delibera_addMenuItemToBottom_processMethod( menuMarkup, req )
{
	jQuery(menuMarkup).hideAdvancedMenuItemFields().appendTo( wpNavMenu.targetList );
	var idf = jQuery(menuMarkup).attr('id');
	var id = idf.substr(idf.lastIndexOf('-') + 1);
	delibera_custom_nav_menu(id);
}
