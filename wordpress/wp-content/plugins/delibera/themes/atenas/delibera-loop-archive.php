<?php

/*
 * O loop padrão do archive.php
 * 
 * Por enquanto, ele está apenas alinhado para funcionar com o Delibera. A ideia é deixa-lo específico
 * o suficiente pra trabalhar com datas, categorias, tags e até taxonomias, antes de, quem sabe, separar
 * os arquivos.
 * 
 */

?>

<?php
	if ( have_posts() ) : while ( have_posts() ) : the_post();
?>


		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<div class="entry-meta">
				
				<span class="entry-author author vcard">
					<?php _e( 'Discussão criada por', 'delibera' ); ?>
					<a class="url fn n" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( __( 'Ver o perfil de %s', 'delibera' ), get_the_author() ); ?>">
						<?php the_author(); ?>
					</a>
				</span><!-- entry-date -->
				
				<span class="entry-date">
					<?php echo ' ' . __( 'em', 'delibera' ) . ' '; ?>
					<?php the_date('m/d/y'); ?>
				</span><!-- .entry-date -->
			
				<span class="entry-prazo">
					<?php
						if ( delibera_get_prazo( $post->ID ) == -1 )
							_e( 'Prazo encerrado', 'delibera' );
						else
							printf( _n( 'Encerra em um dia', 'Encerra em %1$s dias', delibera_get_prazo( $post->ID ), 'delibera' ), number_format_i18n( delibera_get_prazo( $post->ID ) ) );
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
				<?php endif; ?>
				<?php
					/*
					$tags_list = get_the_tag_list( '', ', ' );
					if ( $tags_list ):
				?>
					<span class="tag-links">
						<?php printf( __( 'Palavras-chave', 'delibera' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
					</span>
					<span class="meta-sep">|</span>
				<?php endif;
				*/
				?>
				<?php
				if(comments_open(get_the_ID()) && is_user_logged_in())
				{
				?>
					<span class="comments-link">
						<a href="<?php echo delibera_get_comments_link(); ?>">
						<?php
						_e( 'Discuta', 'delibera' );
						comments_number( '', ' ('. __( 'Um comentário', 'delibera' ) . ')', ' ('. __( '% comentários', 'delibera' ) . ')' );
						?>
						</a>
					</span>
				<?php
				}
				elseif(delibera_comments_is_open(get_the_ID()) && !is_user_logged_in())
				{
				?>
					<span class="comments-link">
						<a href="<?php echo wp_login_url( delibera_get_comment_link());?>">
							<?php _e( 'Discuta', 'delibera' ); ?>
							<?php comments_number( '', '('. __( 'Um comentário', 'delibera' ) . ')', '('. __( '% comentários', 'delibera' ) . ')' ); ?> 
						</a>
					</span>
				<?php
				}
				?>
				<span class="archive-situacao">
					<?php echo delibera_get_situacao($post->ID)->name; ?>
				</span>
			</div><!-- .entry-utility -->
		</div><!-- #post-## -->

		<?php comments_template( '', true ); ?>


<?php endwhile; endif; ?>
