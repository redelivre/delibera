jQuery(document).ready(function(){
	/* This code is executed after the DOM has been completely loaded */

	/* The number of event sections / years with events */
	var tot=jQuery('.event').length;
	
	jQuery('.eventList li').click(function(e){
			showWindow('<div>'+jQuery(this).find('div.timeline_content').html()+'</div>');
	});
	
	/* Each event section is 320 px wide */
	var timelineWidth = 320*tot;
	var screenWidth = jQuery(document).width();
	
	jQuery('#timelineScroll').width(timelineWidth);
	
	/* If the timeline is wider than the screen show the slider: */
	if(timelineWidth > screenWidth)
	{
		showScroll(timelineWidth, screenWidth, tot, '', 'h');
		showScroll(timelineWidth, screenWidth, tot, 'h', '');
	}
	
});

function showScroll(timelineWidth, screenWidth, tot, prefix, side)
{
	jQuery('#'+prefix+'scroll,#'+prefix+'slider').show();
	jQuery('#'+prefix+'centered,#'+prefix+'slider').width(120*tot);
	
	/* Making the scrollbar draggable: */
	jQuery('#'+prefix+'bar').width((120/320)*screenWidth).draggable({

		containment: 'parent',
		drag: function(e, ui) {

			if(!this.elem)
			{
				/* This section is executed only the first time the function is run for performance */
				
				this.elem = jQuery('#timelineScroll');
				
				/* The difference between the slider's width and its container: */
				this.maxSlide = ui.helper.parent().width()-ui.helper.width();

				/* The difference between the timeline's width and its container */
				this.cWidth = this.elem.width()-this.elem.parent().width();
				this.highlight = jQuery('#'+prefix+'highlight');
				
			}
			
			/* Translating each movement of the slider to the timeline: */
			this.elem.css({marginLeft:'-'+((ui.position.left/this.maxSlide)*this.cWidth)+'px'});
			
			/* Moving the highlight: */
			this.highlight.css('left',ui.position.left);
			var sidebar = jQuery('#'+side+'bar');
			sidebar.css('left',ui.position.left);
			var highlightside = jQuery('#'+side+'highlight');
			highlightside.css('left',ui.position.left);
		}
	});
	
	jQuery('#'+prefix+'highlight').width((120/320)*screenWidth-3);
}

function removeWindowBox(e)
{
	if (e.keyCode == 27)
	{
		jQuery('#overlay').remove();
		jQuery('#windowBox').remove();
	}
};

function showWindow(data)
{
	/* Each event contains a set of hidden divs that hold
	   additional information about the event: */
	   
	var title = jQuery('.timeline_title',data).text();
	var date = jQuery('.timeline_date',data).text();
	var body = jQuery('.timeline_body',data).html();
	
	jQuery('<div id="overlay" >').css({
								
		width:jQuery(document).width(),
		height:jQuery(document).height(),
		opacity:0.6
		
	}).appendTo('body').click(function(){
		
		jQuery(this).remove();
		jQuery('#windowBox').remove();
		
	});
	
	jQuery('#closebutton').click(function()
	{
		jQuery('#overlay').remove();
		jQuery('#windowBox').remove();
	});
	
	jQuery(document).keyup(function(e){
		removeWindowBox(e);
	});

	
	jQuery('body').append('<div id="windowBox"><div id="closebutton" style="display: inline; float: right">X</div><div id="titleDiv">'+title+'</div>'+body+'<div id="date">'+date+'</div></div>');

	jQuery('#windowBox').css({
		width:500,
		height:350,
		left: (jQuery(window).width() - 500)/2,
		top: (jQuery(window).height() - 350)/2
	});

	
}