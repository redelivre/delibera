<?php
function delibera_get_edit_comment_link( $comment_id = 0 )
{
	$comment = &get_comment( $comment_id );

	$userid = get_current_user_id();
	if($userid != $comment->user_id)
		return;

	$location = admin_url('comment.php?action=editcomment&amp;c=') . $comment->comment_ID;
	return apply_filters( 'get_edit_comment_link', $location );
}

function delibera_edit_comment_link( $link = null, $before = '', $after = '' )
{
	global $comment;

	$userid = get_current_user_id();
	if($userid != $comment->user_id)
		return;

	if ( null === $link )
		$link = __('Editar', delibera);

	$html = '<div class="delibera-edit-comment-button"><span class="delibera-edit-comment-button" onclick="';
	$html .= 'delibera_edit_comment_show(\''.$comment->comment_ID.'\');';
	$html .= '" >'.$link.'</span></div>';
	echo $html;
}

function delibera_get_comment_to_edit( $id ) {
	if ( !$comment = get_comment($id) )
		return false;

	$comment->comment_ID = (int) $comment->comment_ID;
	$comment->comment_post_ID = (int) $comment->comment_post_ID;

	$comment->comment_content = format_to_edit( $comment->comment_content );
	$comment->comment_content = apply_filters( 'comment_edit_pre', $comment->comment_content);

	$comment->comment_author = format_to_edit( $comment->comment_author );
	$comment->comment_author_email = format_to_edit( $comment->comment_author_email );
	$comment->comment_author_url = format_to_edit( $comment->comment_author_url );
	$comment->comment_author_url = esc_url($comment->comment_author_url);

	return $comment;
}

function delibera_update_comment($comment_id, $user_id, $text, $proposta)
{
	$arrcomment = array(
		'comment_ID' => intval($comment_id),
		'comment_content' => $text,
		'comment_date' => date("Y-m-d H:i:s")
	);
	wp_update_comment($arrcomment);
	
	$comment = get_comment($comment_id);
	
	$proposta_antes = get_comment_meta($comment_id, 'delibera_comment_tipo', true);
	if($proposta != $proposta_antes)
	{
		if($proposta == 'encaminhamento')
		{
			update_comment_meta($comment_id, 'delibera_comment_tipo', 'encaminhamento');
			$nencaminhamentos = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_encaminhamentos', true);
			$nencaminhamentos++;
			update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_encaminhamentos', $nencaminhamentos);
			$ndiscussoes = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_discussoes', true);
			$ndiscussoes--;
			update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_discussoes', $ndiscussoes);
		}
		else 
		{
			update_comment_meta($comment_id, 'delibera_comment_tipo', 'discussao');
			$ndiscussoes = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_discussoes', true);
			$ndiscussoes++;
			update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_discussoes', $ndiscussoes);
			$nencaminhamentos = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_encaminhamentos', true);
			$nencaminhamentos--;
			update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_encaminhamentos', $nencaminhamentos);
		}
	}
	
	return $text;
} 

function delibera_update_comment_callback()
{
	if(
		array_key_exists('comment_ID', $_POST) &&
		array_key_exists('user_id', $_POST) &&
		array_key_exists('text', $_POST) &&
		array_key_exists('proposta', $_POST)
	)
	{
		if(check_ajax_referer( "comment-edit-delibera-{$_POST['comment_ID']}-{$_POST['user_id']}", 'security' ))
		{
			echo delibera_update_comment($_POST['comment_ID'], $_POST['user_id'], $_POST['text'], $_POST['proposta']);
		}
	}
	die();
}
add_action('wp_ajax_delibera_update_comment', 'delibera_update_comment_callback');
add_action('wp_ajax_nopriv_delibera_update_comment', 'delibera_update_comment_callback');

function delibera_comment_edit_form()
{
	global $comment;
	$comment = delibera_get_comment_to_edit($comment->comment_ID);
	require_once(WP_CONTENT_DIR.'/../wp-admin/includes/template.php');
	include(__DIR__.'/delibera-edit-form-comment.php');
}

function delibera_delete_comment_link( $link = null, $before = '', $after = '' )
{
	global $comment;

	$userid = get_current_user_id();
	if($userid != $comment->user_id)
		return;

	if ( null === $link )
		$link = __('Deletar', delibera);

	$html = '<div id="delibera-delete-comment-button-'.$comment->comment_ID.'" class="delibera-delete-comment-button"><span class="delibera-delete-comment-button" >'.$link.'</span></div>';
	echo $html;
}

function delibera_delete_comment($comment_id, $user_id, $proposta)
{
	$comment = get_comment($comment_id);
	if($proposta == 'encaminhamento')
	{
		$nencaminhamentos = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_encaminhamentos', true);
		$nencaminhamentos--;
		update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_encaminhamentos', $nencaminhamentos);
	}
	else 
	{
		$ndiscussoes = get_post_meta($comment->comment_post_ID, 'delibera_numero_comments_discussoes', true);
		$ndiscussoes--;
		update_post_meta($comment->comment_post_ID, 'delibera_numero_comments_discussoes', $ndiscussoes);
	}
	return wp_delete_comment($comment_id);
}

function delibera_delete_comment_callback()
{
	if(
		array_key_exists('comment_ID', $_POST) &&
		array_key_exists('user_id', $_POST) &&
		array_key_exists('proposta', $_POST)
	)
	{
		if(check_ajax_referer( "comment-delete-delibera-{$_POST['comment_ID']}-{$_POST['user_id']}", 'security' ))
		{
			echo delibera_delete_comment($_POST['comment_ID'], $_POST['user_id'], $_POST['proposta']);
		}
	}
	die();
}
add_action('wp_ajax_delibera_delete_comment', 'delibera_delete_comment_callback');
add_action('wp_ajax_nopriv_delibera_delete_comment', 'delibera_delete_comment_callback');

?>