<?php
/*
Template Name: Authors Pautas
*/
get_header();
$id = $wp_query->query_vars['pautasfor'];
$user = get_user_by( 'id' , deliberaEncryptor('decrypt',$id) );

$per_page = isset( $_GET['per-page'] ) ?	esc_html( $_GET['per-page'] ) : '20' ;
$search = isset( $_GET['search'] ) ?	esc_html( $_GET['search'] ) : '' ;
$order	= isset( $_GET['order-by'] ) ?	esc_html( $_GET['order-by'] ) : '' ;

global $user_display;
?>
<div id="container">
	<div id="main-content" role="main">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div id="user_form_search" class="user_form_search">
					<div class="delibera-header">
						<h1 class="page-title"><span>Membros</span></h1>
						<div class="delibera-apresentacao">
							<p class="delibera-boasvindas">
							Listagem de todas as pautas e comentários dos participantes das discussões
							</p>
							<div class="author-content-actions">
								<a class="button all-authors" href="<?php echo get_site_url(); ?>/delibera/membros" ><?php _e('Ver todos os Membros' , 'delibera' ); ?></a>
							<form class="author-search" method="get" name="form">
								<input type="text" class="search-field" name="search" placeholder="Pesquisar por Pautas ..." value="<?php echo $search; ?>"/>
								<input type="submit" class="search-button"  value="Pesquisar"	/>
							</form>
							</div><!-- .author-content-actions -->
						</div><!-- .delibera-apresentacao -->
						<div class="author-controls">
						<form method="get">
							<label for="per-page"><?php echo __('Pautas por Página:' , 'delibera'); ?></label>
							<select id="per-page" name="per-page"	onchange='if(this.value != 0) { this.form.submit(); }' >
								<option value="5" <?php echo $per_page=='5' ? 'selected' : '' ;?> >5</option>
								<option value="10" <?php echo $per_page=='10' ? 'selected' : '' ;?> >10</option>
								<option value="20" <?php echo $per_page=='20' ? 'selected' : '' ;?> >20</option>
							</select>
						</form>
						</div><!-- .author-controls -->
					</div><!-- .delibera-header -->
					<div class="author-header">
						<?php echo get_avatar( $user->ID ); ?>
						<div class="author-data">
						<h1><?php echo $user->first_name ?></h1>
					<p><a class="button author-comment-button" href="<?php echo get_site_url(); ?>/delibera/<?php echo deliberaEncryptor('encrypt', $user->ID); ?>/comentarios" ><?php echo __('Ver todos os Comentários deste Membro' , 'delibera' ); ?></a></p>
						</div><!-- .author-data -->
					</div><!-- .author-header -->
<div class="filter-bar">
			<div class="title">
				<h2><?php echo 'Pautas deste Membro:'; ?></h2>
			</div>
		</div>

				<?php
				$args = array(
					'author'					=> $user->ID,
					'status'					=> 'approve',
					's'							 => $search,
					'posts_per_page'	=> $per_page,
					'post_type'			 => 'pauta',
					'paged'					 => get_query_var( 'paged' )
				);
				global $wp_query;
				$wp_query = new WP_Query($args);
				?>
				<div class="lista-de-pautas">
					<?php
					global $deliberaThemes;
					$deliberaThemes->archiveLoop();
					
					//pagination hack
					$big = 99999999; // need an unlikely integer
					
					$links = paginate_links(array(
						'base' => str_replace($big, '%#%', get_pagenum_link($big)),
						'format' => '?paged=%#%',
						'total' => $wp_query->max_num_pages,
						'current' => max(1, get_query_var('paged')),
						'type' => 'array',
						'prev_next' => false,
					));
		
					?>
					
					<?php if (!empty($links)) : ?>
						<nav class="navigation">
							<ol>
								<?php foreach ($links as $link) : ?>
									<li><?php echo $link; ?></li>
								<?php endforeach; ?>
							</ol>
						</nav>
					<?php endif; ?>
				</div>
			</main><!-- #main -->
		</div>
	</div><!-- #content -->
</div><!-- #container -->
<?php
get_footer();
?>
</body>
</html>
