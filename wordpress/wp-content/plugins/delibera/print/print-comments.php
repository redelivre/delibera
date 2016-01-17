<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.7 Plugin: WP-Print 2.50										|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Printer Friendly Comments Template										|
|	- wp-content/plugins/wp-print/print-comments.php					|
|																							|
+----------------------------------------------------------------+
*/
?>


<?php if($comments) : ?>
	<?php global $text_direction; ?>
	<span style='float:<?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>' id='comments_controls'><?php print_comments_number(); ?> (<a  href="#" onclick="javascript:document.getElementById('comments_box').style.display = 'block'; return false;"><?php _e('Mostrar', 'delibera'); ?></a> | <a href="#" onclick="javascript:document.getElementById('comments_box').style.display = 'none'; return false;"><?php _e('Esconder', 'delibera'); ?></a>)</span>
	<div id="comments_box">
		<?php
			$opts = delibera_get_config();
			$validacao = delibera_comment_number($post->ID, 'validacao');
			$discussao = delibera_comment_number($post->ID, 'discussao');
			$encaminhamento = delibera_comment_number($post->ID, 'encaminhamento');
			$voto = delibera_comment_number($post->ID, 'voto');
			//$resolucao = delibera_comment_number($post->ID, 'resolucao'); TODO Número de resoluções, baseado no mínimo de votos, ou marcação especial
		?>
		<p id="CommentTitle"><?php print_comments_number(); ?> <?php _e('para', 'delibera'); ?> "<?php the_title(); ?>"</p>
		<?php
			if($validacao > 0)
			{
			 	?><p id="CommentTitle"><?php echo sprintf(_n('%s Validação', '%s Validações', $validacao, 'delibera'), number_format_i18n($validacao)); ?></p><?php
			 	$comments_tmp = delibera_comments_filter_portipo($comments, array('validacao'));
			 	include('print_comment_template.php');
			}
			if($discussao > 0)
			{
				?><p id="CommentTitle"><?php
					echo sprintf(_n('%s Opnião', '%s Opniões', $discussao, 'delibera'), number_format_i18n($discussao));
					if($encaminhamento > 0)
					{
						echo sprintf(_n(', %s Proposta de Encaminhamento', ', %s Propostas de Encaminhamentos', $encaminhamento, 'delibera'), number_format_i18n($encaminhamento));
					}
				?></p><?php
				$comments_tmp = delibera_comments_filter_portipo($comments, array('discussao', 'encaminhamento', 'resolucao'));
				include('print_comment_template.php');
			}
			if($voto > 0)
			{
				?><p id="CommentTitle"><?php echo sprintf(_n('%s Voto', '%s Votos', $voto, 'delibera'), number_format_i18n($voto)); ?></p><?php
				$comments_tmp = delibera_comments_filter_portipo($comments, array('voto'));
				include('print_comment_template.php');
			}
			if($encaminhamento > 0)
			{
				define('RESOLUCOES', true);
				?><p id="CommentTitle"><?php echo 'Votação' ?></p><?php
				$comments_tmp = delibera_comments_filter_portipo($comments, array('encaminhamento', 'resolucao'));
				include('print_comment_template.php');
			}
			
			/*if($resolucao > 0)
			{
				?><p id="CommentTitle"><?php echo sprintf(_n('%s Resolução', '%s Resoluções', $resolucao, 'delibera'), number_format_i18n($resolucao)); ?></p><?php
			}*/ ?>
		<hr class="Divider" style="text-align: center;" />
	</div>
<?php endif; ?>