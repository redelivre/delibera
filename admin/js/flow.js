/**
 * 
 */

function deliberaIsConfig()
{
	return delibera_admin_flow.post_id == '';
}

function deliberaFlowToggle(element)
{
	jQuery(element).parent().siblings('.dragbox-content').toggle();
}

function deliberaFlowRemove(element)
{
	var sel = confirm('do you want to delete this step?');
	if (sel) {
		jQuery(element).parent().parent().remove();
		deliberaUpdateFlow();
	}
}

var sortorder = '';
function deliberaUpdateFlow()
{
	sortorder = '';
	jQuery('.delibera-flow-panel').find('#delibera-flow-column2').each(function()
	{
		var itemorder = jQuery(this).sortable('toArray', { attribute: 'class'} );
		for (i = 0; i < itemorder.length; i++)
		{
			var tmp = itemorder[i].replace("dragbox ", "").replace(" clone", "");
			itemorder[i] = tmp;
		}
		sortorder += itemorder.toString();
	});
	jQuery('#delibera_flow').val(sortorder);
	jQuery('#delibera-flow-column2').trigger('deliberaUpdateFlow');
	if(deliberaIsConfig())
	{
		deliberaHideUsedModules()
	}
	else
	{
		updateDates();
	}
}

function deliberaUsedModule(id)
{
	if(jQuery('.delibera-flow-panel').find("#delibera-flow-column2").find("."+id).length > 1)
	{
		return true;
	}
	return false;
}

function deliberaHideUsedModules()
{
	/** remove after multiple of the same has implemented **/
	if(!deliberaIsConfig())
	{
		var boxes = jQuery('#delibera-flow-column2').find('.dragbox');
		jQuery('.delibera-flow-panel').find("#delibera-flow-column1").find('.dragbox').show();
		for (var i = 0; i < boxes.length; i++)
		{
		  var itemclass = jQuery(boxes[i]).attr('class').replace("dragbox ", "").replace(" clone", "");
		  //console.log(itemclass);
		  jQuery('.delibera-flow-panel').find("#delibera-flow-column1").find("."+itemclass).hide();
		}
	}
	/** END remove after multiple of the same has implemented **/
}

Date.prototype.addDays = function(days)
{
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
}

function updateDates()
{
	var boxes = jQuery('#delibera-flow-column2').find('.dragbox');
	var lastDate = new Date();
	for (var i = 0; i < boxes.length; i++)
	{
		var dateinput = jQuery(boxes[i]).find('input.hasDatepicker');
		var daysinput = jQuery(boxes[i]).find('input.delibera_flow_module_days');
		var daysToAdd = Number(jQuery(daysinput).val());
		lastDate = lastDate.addDays(daysToAdd);
		jQuery(dateinput).val( ('00' + lastDate.getDate()).slice(-2) + "/" + ('00' + (lastDate.getMonth()+1)).slice(-2) + "/" + lastDate.getFullYear());
	}
}

jQuery(document).ready(function() {
	//deliberaHideUsedModules();
	jQuery('.delibera-flow-panel').find('span.maxmin').click(function() {
		deliberaFlowToggle(this);
	});

	jQuery('.delibera-flow-panel').find('span.delete').click(function() {
		deliberaFlowRemove(this);
	});

	jQuery('.delibera-flow-column').sortable({
		connectWith : '.delibera-flow-column',
		handle : 'h2',
		cursor : 'move',
		placeholder : 'placeholder',
		forcePlaceholderSize : true,
		opacity : 0.4,
		helper : function(e, div) {
			copyHelper = div.clone().insertAfter(div);
			jQuery(copyHelper).find('span.maxmin').click(function() {
				deliberaFlowToggle(this);
			});
			jQuery(copyHelper).find('span.delete').click(function() {
				deliberaFlowRemove(this);
			});
			jQuery(copyHelper).find('.hasdatepicker:not([readonly])').removeAttr('id');
			jQuery(copyHelper).find('.hasdatepicker:not([readonly])').removeClass('hasDatepicker').datepicker({
				userLang	: 'pt-BR',
				americanMode: false,
			});
			div_copy = div.clone();
			return div_copy;
		},
		stop : function(event, ui) {
			copyHelper && copyHelper.remove();
			jQuery(ui.item).addClass('clone');
			jQuery(ui.item).find('h2').click();
			sortorder = '';
			jQuery(ui.item).find('.hasdatepicker:not([readonly])').removeClass('hasDatepicker').datepicker({
				userLang	: 'pt-BR',
				americanMode: false,
			});
			jQuery(ui.item).find("input,textarea").attr('disabled', false);
			deliberaUpdateFlow();
			if(!deliberaIsConfig() && ui.item.parent().attr('id') != 'delibera-flow-column1' ) {
				jQuery(ui.item).find('.dragbox-content').show();
			}
		}
	});
	jQuery('.delibera-flow-panel').find("#delibera-flow-column2").sortable({
		receive : function(e, ui) {
			copyHelper = null;
			var id = ui.item.attr('class').replace("dragbox ", "").replace(" clone", "");
			if(deliberaUsedModule(id))
			{
				ui.sender.sortable("cancel");
				ui.item.remove();
			}
			
		}
	});
	jQuery('.delibera-flow-panel').find("#delibera-flow-column1").sortable({
		receive : function(e, ui) {
			if(ui.item.hasClass("clone"))
			{
				ui.sender.sortable("cancel");
	        }
			copyHelper = null;
			var id = ui.item.attr('class').replace("dragbox ", "").replace(" clone", "");
			if(deliberaUsedModule(id))
			{
				ui.sender.sortable("cancel");
				ui.item.remove();
			}
		}
	});
	if(deliberaIsConfig())
	{
		jQuery('.delibera-flow-panel').find("#delibera-flow-column2").find("input,textarea").attr('disabled', 'true');
	}
	else
	{
		jQuery('.delibera-flow-panel').find("#delibera-flow-column1").find("input,textarea").attr('disabled', 'true');
	}
	
	jQuery('.delibera-flow-panel').find("input.dragbox-bt-save").click(function(){
		deliberaUpdateFlow();
		var data = {
            action : "delibera_save_flow",
            delibera_flow: sortorder,
            post_id: jQuery('#delibera-flow-postid').val(),
            nonce: jQuery('#_delibera-flow-nonce').val()
        };
		if(deliberaIsConfig())
		{
			jQuery('.delibera-flow-panel').find('#delibera-flow-column1').find('input:not(input[type=button], input[type=submit], input[type=reset]), textarea, select').each(function(){
				if(this.type == 'checkbox')
				{
					if(this.checked) data[this.name] = this.value;
				}
				else
				{
					data[this.name] = this.value;
				}
			});
		}
		else
		{
			jQuery('.delibera-flow-panel').find('#delibera-flow-column2').find('input:not(input[type=button], input[type=submit], input[type=reset]), textarea, select').each(function(){
				if(this.name.indexOf('[') > -1)
				{
					if(data[this.name] == null)
					{
						data[this.name] = [[
							this.id,
							this.value 
						]];
					}
					else
					{
						data[this.name].push([
							this.id,
							this.value
						]);
					}
				}
				else
				{
					if(this.type == 'checkbox')
					{
						if(this.checked) data[this.name] = this.value;
					}
					else
					{
						data[this.name] = this.value;
					}
				}
			});
		}
		
		jQuery.post(
			delibera_admin_flow.ajax_url, 
            data,
            function(response) {
            	if(response == 'ok')
            		alert("Saved");
            		//window.location.reload(true);
            	else
            		alert("Errors");
            }
        );
	});
});