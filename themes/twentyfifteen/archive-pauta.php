<?php
	global $wp_query;
	
	$options = delibera_get_config();
	$big = 99999999; // need an unlikely integer

	wp_enqueue_script('twentyfifteen-filters', $deliberaThemes->getThemeUrl() . '/js/twentyfifteen-filters.js', array('jquery'));

	if (!isset($_GET['situacao_filtro']))
		$_GET['situacao_filtro'] = array();
	if (!isset($_GET['tema_filtro']))
		$_GET['tema_filtro'] = array();
	
	$links = paginate_links(array(
		'base' => str_replace($big, '%#%', get_pagenum_link($big)),
		'format' => '?paged=%#%',
		'total' => $wp_query->max_num_pages,
		'current' => max(1, get_query_var('paged')),
		'type' => 'array',
		'prev_next' => false,
	));
	
	get_header(); 
?>
<div id="delibera">
	<section id="content" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php if ( have_posts() ) : ?>
			<header class="page-header">
			<?php 
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
				get_delibera_header(); 
			?>
				<div class="clearfix">
					<div class="filters widget-area alignleft">
						<?php if (is_user_logged_in() && isset($options['criar_pauta_pelo_front_end']) && $options['criar_pauta_pelo_front_end'] == 'S') : ?>
							<p><a href="<?php echo site_url('nova-pauta'); ?>" class="btn btn-info">Criar uma nova pauta</a></p>
						<?php endif; ?>
						
						<h2>Filtrar Pautas</h2>
						<form>
							<ul class="status">
								<?php foreach (get_terms('situacao') as $situacao) : ?>
									<li>
										<span class="<?php echo (isset($_GET['situacao_filtro'][$situacao->slug]) && $_GET['situacao_filtro'][$situacao->slug] == 'on') ? 'selected' : ''; ?>">
											<?php echo $situacao->name; ?>
											<input type="hidden" name="situacao_filtro[<?php echo $situacao->slug; ?>]" value="<?php echo (isset($_GET['situacao_filtro'][$situacao->slug]) && $_GET['situacao_filtro'][$situacao->slug] == 'on') ? 'on' : ''; ?>" />
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
							<ul>
								<?php foreach (get_terms('tema') as $tema) : ?>
									<li><label class="checkbox"><input type="checkbox" name="tema_filtro[<?php echo $tema->slug; ?>]" <?php echo (isset($_GET['tema_filtro'][$tema->slug]) && $_GET['tema_filtro'][$tema->slug] == 'on') ? ' checked="checked" ' : ''; ?> /><?php echo $tema->name; ?></label></li>
								<?php endforeach; ?>
							</ul>
							<div class="textright">
								<button type="submit" class="btn">Filtrar</button>
							</div>
						</form>
					</div>
				</div>
			</header><!-- .page-header -->
		<?php
			while ( have_posts() ) : the_post();
				load_template(__DIR__ . '/content-pauta-archive.php', true);
			endwhile;
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
	<?php else :
		get_template_part( 'content', 'none' );

	endif;
?>
		</main><!-- .site-main -->
	</section><!-- .content-area -->
</div>
<?php get_footer(); ?>