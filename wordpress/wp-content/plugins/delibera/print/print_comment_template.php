<?php
$comment_count = 1;
foreach ($comments_tmp as $comment) :
	$ncurtiu = delibera_numero_curtir($comment->comment_ID, 'comment');
	$tipo = delibera_get_comment_type($comment);
	?>
	<div class="print-comment-body <?php echo $tipo?>">
		<p class="CommentDate">
			<strong>#<?php
				echo number_format_i18n($comment_count).' ';
				if($tipo == 'resolucao' && !defined('RESOLUCOES')) $tipo = 'encaminhamento';
				delibera_get_comment_type_label($comment, $tipo, TRUE);
			?></strong> <?php
				_e('de', 'delibera').' ';
			?> <u><?php comment_author(); ?></u><?php
				_e(' em ', 'delibera');
				comment_date(sprintf(__('%s @ %s', 'delibera'), get_option('date_format'), get_option('time_format')));
				if($ncurtiu > 0)
				{
					echo ", $ncurtiu "._n('concordou', 'concordaram', $ncurtiu, 'delibera');
					echo " (".delibera_get_quem_curtiu($comment->comment_ID, 'comment', 'string').")";
				}
			?>
		</p>
		<div class="CommentContent">
			<?php if ($comment->comment_approved == '0') : ?>
				<p><em><?php _e('Seu comentário está aguardando moderação.', 'delibera'); ?></em></p>
			<?php endif; ?>
			<?php print_comments_content(); ?>
		</div>
	</div>
	<?php $comment_count++; ?>
<?php endforeach; ?>