<?php

/**
* Baseado no comments-template
*/

/**
* HTML comment list class.
*
* @package WordPress
* @uses Walker
* @since 2.7.0
*/
class Delibera_Walker_Comment extends Walker_Comment
{
	/**
	* @see Walker::start_el()
	* @since 2.7.0
	*
	* @param string $output Passed by reference. Used to append additional content.
	* @param object $comment Comment data object.
	* @param int $depth Depth of comment in reference to parents.
	* @param array $args
	*/
	function start_el(&$output, $comment, $depth = 0, $args = array(), $current_object_id = 0)
	{
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		$args['avatar_size'] = '85';

		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $comment, $args, $depth);
			return;
		}

		$GLOBALS['comment'] = $comment;

		$tipo = get_comment_meta($comment->comment_ID, "delibera_comment_tipo", true);
		$situacao = delibera_get_situacao($comment->comment_post_ID);

		extract($args, EXTR_SKIP);

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}

		ob_start();
		?>
		<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? "delibera-comment-div-$tipo" : "parent delibera-comment-div-$tipo") ?> id="delibera-comment-<?php comment_ID() ?>">

		<?php if ( 'div' != $args['style'] ) : ?>
			<div id="delibera-div-comment-<?php comment_ID() ?>" class="delibera-comment-body delibera-comment-<?php echo $tipo; ?>">
			<?php endif; ?>
			<div id="delibera-div-comment-header-<?php comment_ID() ?>" class="delibera-comment-header">
				<div class="delibera-comment-author vcard">
					<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
					<?php
					//$url = get_author_posts_url($comment->user_id);
					// XXX colocar hash
					$url = \Delibera\Member\MemberPath::getAuthorPautasUrl($comment->user_id) ;
					//print_r($comment);
					$autor_link = "<a href='$url' rel='external nofollow' class='url'>$comment->comment_author</a>";
					printf('<cite class="fn">%s</cite><span class="delibera-says"></span>', $autor_link);
					?>
				</div>
				<?php if ($comment->comment_approved == '0') : ?>
					<em class="delibera-comment-awaiting-moderation"><?php _e('Seu comentário está aguardando moderação.', 'delibera') ?></em>
					<br />
				<?php endif; ?>

				<div class="delibera-comment-meta commentmetadata">
					<a href="<?php echo htmlspecialchars( delibera_get_comment_link( $comment->comment_ID ) ) ?>">
						<?php

						$time = mysql2date( 'G', $comment->comment_date );

						$time_diff = time() - $time;

						if ( $time_diff > 0 && $time_diff < 30*24*60*60 )
						printf( '&nbsp;' . __( 'há %s', 'delibera' ), human_time_diff( mysql2date( 'U', $comment->comment_date, true ) ) );
						else
						echo '&nbsp;' .	__( 'em', 'delibera' ) . '&nbsp;' .	get_comment_date();

						?>
					</a>
					&nbsp;

				</div>
				<?php
				if ($situacao->slug == "discussao" || $situacao->slug == "relatoria")
				{
					$display_check = $tipo == "encaminhamento"? '' : 'style="display:none;"';
					?>
					<!--span id="checkbox-encaminhamento-<?php echo $comment->comment_ID ?>" class="checkbox-encaminhamento" <?php echo $display_check; ?>><span class="encaminhamento-figura"></span><label class="encaminhamento-label"><?php _e('Encaminhamento','delibera'); ?></label></span-->
					<?php
				}
				?>
			</div>

			<?php
			if($situacao->slug == 'relatoria' && current_user_can('relatoria'))
			{
				$baseouseem = get_comment_meta($comment->comment_ID, 'delibera-baseouseem', true);
				if(strlen($baseouseem) > 0)
				{
					$baseouseem_elements = "";
					foreach (explode(',', $baseouseem) as $baseouseem_element)
					{
						$baseouseem_elements .= do_shortcode($baseouseem_element);
					}
					echo '<div id="comment-painel-baseouseem" class="comment-painel-baseouseem clearfix"><label id="painel-baseouseem-label" class="painel-baseouseem-label" >'.__('Baseado na(s) proposta(s) de:', 'delibera').'&nbsp;</label>'.$baseouseem_elements.'</div>';
				}
			}
			comment_text();

			echo '<div class="group-button-like">';
			echo delibera_gerar_curtir($comment, 'comment');
			echo delibera_gerar_discordar($comment, 'comment');
			echo '</div>';


			delibera_comment_edit_form();
			if ($tipo == "encaminhamento" && current_user_can('relatoria') && (/*$situacao->slug == "discussao" || TODO Opção de baseamento na discussão */ $situacao->slug == "relatoria"))
			{
				?>
				<div class="baseadoem-checkbox-div"><label class="baseadoem-checkbox-label"><input id="baseadoem-checkbox-<?php echo $comment->comment_ID; ?>" type="checkbox" name="baseadoem-checkbox[]" value="<?php echo $comment->comment_ID; ?>" class="baseadoem-checkbox" autocomplete="off" /><?php _e('basear-se nesta proposta?', 'delibera'); ?></label></div>
				<?php
			}
			if(delibera_comments_is_open($comment->comment_post_ID))
			{
				?>
				<div class="reply">
					<?php
					if(is_user_logged_in())
					{
						if($situacao->slug == 'discussao' )
						{
							$args['reply_text'] = '<i class="delibera-icon-reply"></i>';
							echo get_comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));
						}
						if($situacao->slug == 'validacao')
						{
							?>
							<div class="entry-respond">
								<a href="<?php delibera_get_comment_link();?>#respond" class="comment-reply-link"><i class="delibera-icon-reply"></i></a>
							</div>
							<?php
						}
					}
					else
					{
						?>
						<div class="entry-respond">
							<a href="<?php echo wp_login_url(delibera_get_comment_link());?>#respond" class="comment-reply-link"><i class="delibera-icon-reply"></i></a>
						</div><!-- .entry-respond -->
						<?php
					}
					?>
				</div>
				<?php
				if($situacao->slug == 'discussao' || ($situacao->slug == 'relatoria' && current_user_can('relatoria')))
				{
					//TODO gerar por função esse botão?>
					<div id="submit-edit-comment-button-<?php echo $comment->comment_ID;?>" class="submit-edit-comment-button" style="display: none" >
						<span class="submit-edit-comment-button-text"><i class="delibera-icon-ok"></i></span>
					</div>
					<?php
					delibera_edit_comment_link( '<i class="delibera-icon-edit"></i>','&nbsp;&nbsp;', '' );
					delibera_delete_comment_link( '<i class="delibera-icon-trash"></i>','&nbsp;&nbsp;', '' );
				}
				?>
				<?php
			}
			?>
			<?php if ( 'div' != $args['style'] ) : ?>
			</div>
		<?php endif;
		$output .= ob_get_clean();
	}
}
