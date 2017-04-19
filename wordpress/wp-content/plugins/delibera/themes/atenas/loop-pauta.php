<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
					<div id="leader">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), 70 ); ?>
						<h1 class="entry-title"><?php the_title(); ?></h1>

						<div class="entry-meta">
							<div class="entry-situacao">
								<?php printf( __( 'Situação da pauta', 'delibera' ).': %s', delibera_get_situacao($post->ID)->name );?>
							</div><!-- .entry-situacao -->
							<div class="entry-author">
								<?php _e( 'Discussão criada por', 'delibera' ); ?>
								<span class="author vcard">
									<a class="url fn n" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( __( 'Ver o perfil de %s', 'delibera' ), get_the_author() ); ?>">
										<?php the_author(); ?>
									</a>
								</span>
							</div><!-- .entry-author -->
							<div class="entry-comment">
								<?php if(comments_open(get_the_ID()) && is_user_logged_in())
								{?>
									<a href="#delibera-comments">
										<?php _e( 'Discuta', 'delibera' ); ?>
										<?php comments_number( '', '('. __( 'Um comentário', 'delibera' ) . ')', '('. __( '% comentários', 'delibera' ) . ')' ); ?> 
									</a>
								<?php
								}
								elseif(delibera_comments_is_open(get_the_ID()) && !is_user_logged_in())
								{
								?>
									<a href="<?php echo wp_login_url( get_post_type() == "pauta" ? get_permalink() : delibera_get_comment_link());?>#delibera-comments">
										<?php _e( 'Discuta', 'delibera' ); ?>
										<?php comments_number( '', '('. __( 'Um comentário', 'delibera' ) . ')', '('. __( '% comentários', 'delibera' ) . ')' ); ?> 
									</a>
								<?php
								}
								?>
							</div><!-- .entry-comment -->
							
							<div class="entry-attachment">
							</div><!-- .entry-attachment -->
							
							<div class="entry-prazo">
							
								<?php
								if ( delibera_get_prazo( $post->ID ) == -1 )
									_e( 'Prazo encerrado', 'delibera' );
								else
									printf( _n( 'Encerra em um dia', 'Encerra em %1$s dias', delibera_get_prazo( $post->ID ), 'delibera' ), number_format_i18n( delibera_get_prazo( $post->ID ) ) );
								?>
							</div><!-- .entry-prazo -->
							<div class="entry-print">
								<?php echo delibera_get_print_link(); ?>
							</div><!-- .entry-print -->
							<div class="entry-seguir">
								<?php echo delibera_gerar_seguir($post->ID); ?>
							</div><!-- .entry-print -->
						</div><!-- .entry-meta -->
					</div><!-- #leader -->

					<div class="entry-content">
						<?php the_content(); ?>
					</div><!-- .entry-content -->
					
					<div class="entry-utility">
						<div class="entry-share">
							<div class="share-twitter">
								<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
								<a href="http://twitter.com/share" class="twitter-share-button">Tweet</a>
							</div><!-- share-twitter -->
							
							<div class="share-facebook">
								<div id="fb-root"></div>
								<?php
								$lang = 'pt_BR';
								if(function_exists('qtrans_getLanguage'))
								{
									$sitelang = qtrans_getLanguage();
									
									switch ( $sitelang ) {
										case 'en':
											$lang = 'en_US';
											break;
										case 'pt' :
											$lang = 'pt_BR';
											break;
										case 'es' :
											$lang = 'es_ES';
											break;
									}
								}
								
								?>
								<script src="http://connect.facebook.net/<?php echo $lang; ?>/all.js#appId=221052707924641&amp;xfbml=1"></script>
								<fb:like href="<?php the_permalink() ?>" send="false" layout="button_count" width="90" show_faces="false" font="lucida grande" action="recommend"></fb:like>
							</div>
							
							<div class="share-this">
								<?php //sharethis_button(); ?>
							</div>
						</div>
						<?php
						
						$situacao = delibera_get_situacao($post->ID);
						
						if ($situacao->slug != 'validacao') {
							if(comments_open(get_the_ID()) && is_user_logged_in())
							{
							?>
								<div class="entry-respond">
									<a href="<?php get_permalink() ?>#respond" class="comment-reply-link"><?php _e( 'Responder', 'delibera' ); ?></a>
								</div><!-- .entry-respond -->
							<?php
							}
							elseif(delibera_comments_is_open(get_the_ID()) && !is_user_logged_in())
							{
								?>
								<div class="entry-respond">
									<a href="<?php echo wp_login_url( get_post_type() == "pauta" ? get_permalink() : delibera_get_comment_link());?>#respond" class="comment-reply-link"><?php _e( 'Responder', 'delibera' ); ?></a>
								</div><!-- .entry-respond -->
							<?php
							}
						}
						?>
						
						
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->
				
				<?php comments_template( '', true ); ?>

<?php endwhile; // end of the loop. ?>