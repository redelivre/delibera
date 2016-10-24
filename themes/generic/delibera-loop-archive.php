<?php
/*
* O loop padrão do archive.php
*
* Por enquanto, ele está apenas alinhado para funcionar com o Delibera. A ideia é deixa-lo específico
* o suficiente pra trabalhar com datas, categorias, tags e até taxonomias, antes de, quem sabe, separar
* os arquivos.
*
*/

if ( have_posts() ) : while ( have_posts() ) : the_post();
$status_pauta = delibera_get_situacao($post->ID)->slug;
$temas = wp_get_post_terms(get_the_ID(), 'tema');
?>

<div id="post-<?php the_ID(); ?>" <?php post_class($status_pauta); ?>>
	<?php if (!empty($temas)) : ?>
	<ul class="meta meta-temas">
		<li class="delibera-tema-entry-title"><?php _e('Tema(as)', 'delibera'); ?>:</li>
		<?php $size = count($temas) - 1; ?>
		<?php foreach ($temas as $key => $tema) : ?>
		<li>
			<a href="<?php echo get_term_link($tema);?>"><?php echo $tema->name; ?></a>
			<?php echo ($key != $size) ? ',' : ''; ?>
		</li>
		<?php endforeach; ?>
    </ul>
	<?php endif; ?>
	<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
	<div class="entry-blame">
		<div class="entry-author">
			<?php echo get_avatar( get_post(), 85 ); ?>
			<span class="author-text"><?php _e( 'Por', 'delibera' ); ?></span>
			<span class="author vcard">
				<a class="url fn n" href="<?php echo \Delibera\Member\MemberPath::getAuthorPautasUrl(get_the_author_meta( 'ID' )) ; ?>" title="<?php printf( __( 'Ver o perfil de %s', 'delibera' ), get_the_author() ); ?>">
					<?php the_author(); ?>
				</a>
			</span>
		</div><!-- .entry-author -->
		<div class="entry-date">
			<?php the_date(); ?>
		</div>
	</div> <!-- entry-blame -->
	<div class="entry-meta">

		<span class="button archive-situacao">
			<?php echo delibera_get_situacao($post->ID)->name; ?>
		</span>

		<span class="entry-prazo">
			<?php
			if ( \Delibera\Flow::getDeadlineDays( $post->ID ) == -1 )
			_e( 'Prazo encerrado', 'delibera' );
			else
			printf( _n( 'Por mais <br><span class="numero">1</span> dia', 'Por mais <br><span class="numero">%1$s</span> dias', \Delibera\Flow::getDeadlineDays( $post->ID ), 'delibera' ), number_format_i18n( \Delibera\Flow::getDeadlineDays( $post->ID ) ) );
			?>
		</span><!-- .entry-prazo -->

	</div><!-- .entry-meta -->

	<?php if ( is_archive() || is_search() ) : // Only display excerpts for archives and search. ?>
		<div class="entry-content">
			<?php the_excerpt(); ?>
		</div><!-- .entry-summary -->
	<?php else : ?>
		<div class="entry-content">
			<?php the_content( __( 'Continue lendo' ), 'delibera' ); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Páginas:', 'delibera' ), 'after' => '</div>' ) ); ?>
		</div><!-- .entry-content -->
	<?php endif; ?>
	<div class="entry-utility">
		<?php if ( count( get_the_category() ) ) : ?>
			<span class="cat-links">
				<?php printf( __( 'Arquivado em', 'delibera' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
			</span>
			<span class="meta-sep">|</span>
		<?php endif;
		$tags_list = get_the_tag_list( '', ', ' );
		if ( $tags_list ):
			?>
			<span class="tag-links">
				<?php printf( __( 'Palavras-chave', 'delibera' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
			</span>
			<span class="meta-sep">|</span>
		<?php endif; ?>
		<div id="delibera-comment-botoes-<?php echo get_the_ID(); ?>" class="delibera-comment-botoes">
				<div class="archive-botoes"><?php
				echo delibera_gerar_curtir($post->ID, 'pauta');
				echo delibera_gerar_discordar($post->ID, 'pauta');?>
				</div>
			<div class="group-button-like">
			</div><?php
			if(function_exists('social_buttons'))
			{
				echo social_buttons(get_permalink(), get_the_title());
			}
			?>
			<span class="comments-link button">
				<a href="<?php echo delibera_get_comments_link(); ?>">
					<?php
					// validacao,discussao,relatoria,emvotacao,comresolucao
					switch ($status_pauta) {
						case 'validacao':
						$labelButton = 'decida';
						break;
						case 'discussao':
						$labelButton = 'comente';
						break;
						case 'relatoria':
						$labelButton = 'comente';
						break;
						case 'emvotacao':
						$labelButton = 'Vote';
						break;
	
						default:
						$labelButton = 'ver pauta';
						break;
					}
					_e( $labelButton, 'delibera' );
					?>
					<?php //comments_number( '', '('. __( '1', 'delibera' ) . ')', '('. __( '%', 'delibera' ) . ')' ); ?>
				</a>
			</span>
		</div><!-- .delibera-comment-botoes -->
	</div><!-- .entry-utility -->
</div><!-- #post-## -->

<?php comments_template( '', true ); ?>

<?php endwhile; endif; ?>
