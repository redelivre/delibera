/**
 * 
 */

function divi_child_teste()
{
    var ajaxurl = ET_Builder_Module_Formulario.ajax_url;

    var $that = jQuery(this);
    var data = {
        'action': 'check_user_logged_in'
    };

    jQuery.post(ajaxurl, data,function(response){ // aja
	    if(response == '0') {
		    alert('Antes de interagir você deve se cadastrar');
		    if(typeof ajax_login_toggle_panel == 'function')
		    {
		    	ajax_login_toggle_panel();
		    }
		    else
		    {
		    	window.location = ET_Builder_Module_Formulario.url;
		    }	
	    }
    });
}