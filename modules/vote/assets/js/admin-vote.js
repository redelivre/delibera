function delibera_add_comment_input(element)
{
	var panel = jQuery(element).parent();
	panel.find('.delibera_comment_add_current').append(jQuery('<p><textarea name="delibera_comment_add_list[]" >'+panel.find('.delibera_comment_input_list').val()+'</textarea><a href="#" class="delibera_comment_input_bt_remove delibera-icon-cancel"></a></p>'));
	panel.find('.delibera_comment_input_list').val('');
	panel.find('.delibera_comment_input_list').focus();
}

function delibera_check_discussion()
{
	var boxes = jQuery('#delibera-flow-column2').find('.dragbox');
	for (var i = 0; i < boxes.length; i++)
	{
	  if(jQuery(boxes[i]).hasClass("emvotacao"))
	  {
		  jQuery('.delibera_comment_list_panel').show();
		  return false;
	  }
	  if(jQuery(boxes[i]).hasClass("discussao"))
	  {
		  jQuery('.delibera_comment_list_panel').hide();
		  return true;
	  }
	}
	return true;
}

jQuery(document).ready(function() {
	jQuery('.delibera_comment_input_bt_remove').live('click', function() {
		var sel = confirm('Do you want to delete this vote option?');
		if (sel) {
			jQuery(this).parents('p').remove();
		}
	    return false;
	});
	jQuery('#delibera-flow-column2').on('deliberaUpdateFlow', function(event, data)
	{
		delibera_check_discussion();
	});
	delibera_check_discussion();
	
	jQuery('.delibera-voto-modal-close').click(function (){
		jQuery(this).parent().parent().hide();
	});
	jQuery('.label-voto').click(function () {
		var id = jQuery(this).attr('id').replace('delibera-voto-option-', '');
		jQuery('#delibera-voto-option-' + id).show();
	});
});