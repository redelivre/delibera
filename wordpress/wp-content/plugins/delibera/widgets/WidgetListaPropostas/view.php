<div id="lista-de-pautas">
<h2 class="list-title delibera-widget-list-title"><?php echo $title; ?></h2>
<?php
global $post;
foreach ( $wp_posts as $wp_post )
{
	$post = $wp_post;
	?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title delibera-widget-entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			<?php
			if($show_author == 1 || $show_date == 1 || $show_prazo == 1) :
			?>
			<div class="entry-meta delibera-widget-entry-meta">
				
				<?php
				if($show_author == 1) :
					?>
					<span class="entry-author author vcard delibera-widget-entry-author">
						<?php _e( 'Discussão criada por', 'delibera' ); ?>
						<a class="url fn n" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( __( 'Ver o perfil de %s', 'delibera' ), get_the_author() ); ?>">
							<?php the_author(); ?>
						</a>
					</span><!-- entry-author -->
					<?php
				endif;
				if($show_date == 1) :
				?>
				<span class="entry-date delibera-widget-entry-date">
					<?php echo ' ' . __( 'em', 'delibera' ) . ' '; ?>
					<?php the_date('m/d/y'); ?>
				</span><!-- .entry-date -->
				<?php 
				endif;
				if($show_prazo == 1) :
				?>
				<span class="entry-prazo delibera-widget-entry-prazo">
					<?php
						if ( delibera_get_prazo( $post->ID ) == -1 )
							_e( 'Prazo encerrado', 'delibera' );
						else
							printf( _n( 'Encerra em um dia', 'Encerra em %1$s dias', delibera_get_prazo( $post->ID ), 'delibera' ), number_format_i18n( delibera_get_prazo( $post->ID ) ) );
					?>
				</span><!-- .entry-prazo -->
				<?php
				endif; 
				?>
			</div><!-- .entry-meta -->
		<?php
		endif;
		if($show_summary == 1) : // Only display excerpts for archives and search. ?>
			<div class="entry-summary delibera-widget-entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
		<?php
		elseif($show_summary == 2) : ?>
			<div class="entry-content delibera-widget-entry-content">
				<?php //the_content( __( 'Continue lendo', 'delibera'), true );
					$content = $post->post_content;
					$content = apply_filters('the_content', $content);
					$content = str_replace(']]>', ']]>', $content);
					echo $content;
				?>
				<?php wp_link_pages( array( 'before' => '<div class="page-link delibera-widget-page-link">' . __( 'Páginas:', 'delibera' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
		<?php
		endif;
		if( count( get_the_category() ) || $show_comment_link == 1 || $show_situacao == 1 ) :
		?>
			<div class="entry-utility delibera-widget-entry-utility">
				<?php
				if ( count( get_the_category() ) ) : ?>
					<span class="cat-links delibera-widget-cat-links">
						<?php printf( __( 'Arquivado em', 'delibera' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
					</span>
					<span class="meta-sep">|</span>
				<?php
				endif;
				if($show_comment_link == 1) :
				?>
				<span class="comments-link delibera-widget-comments-link">
					<a href="<?php echo delibera_get_comments_link(); ?>">
					<?php
					_e( 'Discuta', 'delibera' );
					comments_number( '', ' ('. __( 'Um comentário', 'delibera' ) . ')', ' ('. __( '% comentários', 'delibera' ) . ')' );
					?>
					</a>
				</span>
				<?php
				endif;
				if($show_situacao == 1) :
				?>
				<span class="archive-situacao delibera-widget-archive-situacao">
					<?php echo delibera_get_situacao($post->ID)->name; ?>
				</span>
				<?php
				endif; 
				?>
			</div><!-- .entry-utility -->
			<?php
			endif; 
			?>
		</div><!-- #post-## -->
	
	<?php
}
?>
</div>