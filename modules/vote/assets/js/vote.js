jQuery(document).ready(function() {
	jQuery('.delibera-voto-modal-close').click(function (){
		jQuery(this).parent().parent().hide();
	});
	jQuery('.delibera-voto-bt-read').click(function () {
		var id = jQuery(this).attr('id').replace('delibera-voto-bt-read-', '');
		jQuery('#delibera-voto-modal-' + id).show();
	});
});