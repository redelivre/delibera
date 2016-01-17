<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

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
				<?php //the_content( __( 'Continue lendo', 'delibera'), true );
					$content = $post->post_content;
					$content = apply_filters('the_content', $content);
					$content = str_replace(']]>', ']]>', $content);
					echo $content;
				?>
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
				<span class="comments-link">
					<a href="<?php echo delibera_get_comments_link(); ?>">
					<?php
					_e( 'Discuta', 'delibera' );
					comments_number( '', ' ('. __( 'Um comentário', 'delibera' ) . ')', ' ('. __( '% comentários', 'delibera' ) . ')' );
					?>
					</a>
				</span>
				<span class="archive-situacao">
					<?php echo delibera_get_situacao($post->ID)->name; ?>
				</span>
			</div><!-- .entry-utility -->
		</div><!-- #post-## -->
