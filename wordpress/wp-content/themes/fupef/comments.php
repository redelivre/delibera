<?php if( 'comments.php' == basename( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) return false; // dont access this file directly ?>

<?php if( !empty( $post->post_password ) and $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] !== $post->post_password ) return false; // if the post is protected and a pass was given ?>



<?php
function enhanced_comments( $comment, $args, $depth )
{
	$GLOBALS[ 'comment' ] = $comment;

	?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<h3><a href="#"><?php comment_author_link(); ?></a> |</h3>
			<h4><?php comment_time( 'd/m/Y' ); ?></h4>
			<?php if( '0' == $comment->comment_approved ) : ?><p class="comment-wait">Seu comentário está aguardando moderação.</p><?php endif; ?>
			<?php comment_text(); ?>
			<?php comment_reply_link( array( 'depth' => $depth, 'max_depth' => $args[ 'max_depth' ], 'reply_text' => 'Responder' ) ); ?>
			<?php edit_comment_link( '| Editar' ); ?>
		</li>
	<?php
}
?>



<div class="comentarioSingle">

	

	<?php if( $comments ) : //print_r( $comments ); ?>
	
	<h2><strong>Comentários</strong> <?php comments_number( '', '(1)', '(%)' ); ?></h2>
	
		<ul>
			<?php wp_list_comments('callback=enhanced_comments'); ?>
		</ul>

		<?php if(function_exists('previous_comments_link') and function_exists('next_comments_link')) : ?>
			<div class="verTodosComentarios">
				<div class="alignleft"><?php previous_comments_link() ?></div>
				<div class="alignright"><?php next_comments_link() ?></div>
			</div>
		<?php endif; ?>

	<?php endif; ?>



	<?php if( 'open' == $post->comment_status ) : ?>
		<div class="facaComentario" id="respond">

			<?php if( get_option( 'comment_registration' ) && !$user_ID ) : ?>
				<h2>Você precisa estar logado para fazer um comentário.</h2>

			<?php else : ?>
				<h2>Faça seu comentário</h2>

				<form action="<?php bloginfo('url'); ?>/wp-comments-post.php" method="post">

					<?php if( function_exists( 'comment_id_fields' ) ) comment_id_fields(); ?>

					<?php if( $user_ID ) : ?>
						<div class="boxUserComent">
							<a href="<?php bloginfo( 'url' ); ?>/wp-admin/profile.php" title="Perfil"><?php print $user_identity; ?></a> (<?php wp_loginout(); ?>)
						</div>

					<?php else : ?>
						<div class="boxUserComent">
							Nome<br />
							<input type="text" name="author" id="author" value="<?php print $comment_author; ?>" tabindex="8" /> <div class="campoObrigatorio">*obrigatório</div>
						</div>

						<div class="boxUserComent">
							E-mail<br />
							<input type="text" name="email" id="email" value="<?php print $comment_author_email; ?>" tabindex="9" /> <div class="campoObrigatorio">*obrigatório</div>
						</div>

						<div class="boxUserComent">
							Website<br />
							<input type="text" name="url" id="url" value="<?php print $comment_author_url; ?>" tabindex="10" />
						</div>
					<?php endif; ?>

					<textarea name="comment" id="comment" tabindex="11" rows="5" cols="5"></textarea>

					<?php do_action( 'comment_form', $post->ID ); ?>
					<input type="hidden" name="comment_post_ID" value="<?php print $id; ?>" />
					<button type="submit" name="submit" tabindex="12"></button>
					<?php cancel_comment_reply_link( 'cancelar' ) ?>

				</form>

			<?php endif; ?>

		</div>

	<?php endif; ?>

</div>
