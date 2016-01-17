<form name="delibera-edit-comment-<?php echo $comment->comment_ID;?>" action="comment.php" method="post" id="delibera-edit-comment-<?php echo $comment->comment_ID;?>" style="display:none;" class="delibera-edit-comment-form" >
<div id="div-delibera-edit-comment-<?php echo $comment->comment_ID; ?>" class="delibera-edit-comment">
<?php
	//$rows = get_option('default_post_edit_rows');
	//if (($rows < 3) || ($rows > 100))
	
    if (!isset($class)) {
        $class = '';
    }
    
    if (!isset($tab_index)) {
        $tab_index = '';
    }
	
	$rows = 4;
	$id = "textarea-delibera-edit-comment-{$comment->comment_ID}";
	$the_editor = apply_filters('the_editor', "<div id='editorcontainer-delibera-edit-comment-".$comment->comment_ID."'><textarea rows='$rows'$class cols='80' name='$id' tabindex='$tab_index' id='$id'>%s</textarea></div>\n");
	$the_editor_content = apply_filters('the_editor_content', $comment->comment_content);

	printf($the_editor, $the_editor_content);
?>
</div>

<?php
do_action('add_meta_boxes', 'comment', $comment);
do_action('add_meta_boxes_comment', $comment);

do_meta_boxes('comment', 'normal', $comment);
?>

<div id="delibera-publishing-action">
	<div id="submit-edit-comment-button-<?php echo $comment->comment_ID;?>" class="submit-edit-comment-button" ><span class="submit-edit-comment-button-text"><?php echo __('Atualizar','delibera')?></span></div>
	<script type="text/javascript">
		jQuery(document).ready(function ()
	    {
	        jQuery("#submit-edit-comment-button-<?php echo $comment->comment_ID;?>")
	            .click(function ()
	            {
					jQuery.post("<?php echo home_url( "/" );?>/wp-admin/admin-ajax.php", 
						{
							action : "delibera_update_comment" ,
						    comment_ID : "<?php echo $comment->comment_ID;?>",
						    user_id: "<?php echo get_current_user_id(); ?>",
						    text: jQuery('#textarea-delibera-edit-comment-<?php echo $comment->comment_ID;?>').val(),
						    proposta: jQuery('input[name=delibera_encaminha]:checked', '#delibera-edit-comment-<?php echo $comment->comment_ID;?>').val() == 'S' ? 'encaminhamento' : 'discussao', 
							security: "<?php echo wp_create_nonce("comment-edit-delibera-{$comment->comment_ID}-".get_current_user_id()); ?>"
						},
						function(response)
						{
							location.reload();
						}
					);
	            });
	        jQuery("#delibera-delete-comment-button-<?php echo $comment->comment_ID;?>")
            .click(function ()
            {
		        if (confirm("<?php _e('Tem certeza que deseja remover este item?'); ?>")) {
    				jQuery.post("<?php echo home_url( "/" );?>/wp-admin/admin-ajax.php", 
    					{
    						action : "delibera_delete_comment" ,
    					    comment_ID : "<?php echo $comment->comment_ID;?>",
    					    user_id: "<?php echo get_current_user_id(); ?>",
    					    proposta: jQuery('input[name=delibera_encaminha]:checked', '#delibera-edit-comment-<?php echo $comment->comment_ID;?>').val() == 'S' ? 'encaminhamento' : 'discussao',
    						security: "<?php echo wp_create_nonce("comment-delete-delibera-{$comment->comment_ID}-".get_current_user_id()); ?>"
    					},
    					function(response)
    					{
    						jQuery('#delibera-comment-<?php echo $comment->comment_ID;?>').remove();
    					}
    				);
    			}
            });
	    });
	</script>
</div>

</form>

<script type="text/javascript">
try
{
	document.post.name.focus();
}
catch(e){}
</script>
