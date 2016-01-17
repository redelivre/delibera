<?php get_header(); ?>

<div class="grid">
	<div class="col-1-3 text-center">
		<a href="/delibera/"><img src="<?php bloginfo('template_url'); ?>/public/img/icon-delibera.png" alt="Delibera"></a>
		<h2><a href="/delibera/"> Sobre o Delibera</a></h2>
	</div>

	<div class="col-1-3 text-center">
	<a href="/delibera/"><img src="<?php bloginfo('template_url'); ?>/public/img/icon-pautas.png" alt="Delibera"></a>
		<h2><a href="/pauta/"> Todas as Pautas</a></h2>
	</div>

	<div class="col-1-3 text-center">
		<a href="/delibera/"><img src="<?php bloginfo('template_url'); ?>/public/img/icon-kaleb.png" alt="Delibera"></a>
		<h2><a href="/sobre/"> Sobre Mim</a></h2>
	</div>
</div>


<section class="posts-delibera col-2-3">
	<?php query_posts(array('post_type' => 'pauta', 'posts_per_page' => 10));?>		

	<h2 class="h2"> Pautas em Destaques </h2>

	<?php while (have_posts()) : the_post(); ?>

		<article>
			<small><?php the_time('d/m/Y') ?></small>
			<h3><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
			<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php echo limit_chars(get_the_excerpt(), 350); ?></a>
		</article>
	<?php endwhile; ?>

	<?php wp_reset_query(); ?>
</section>


<aside class="col-1-3">
	<h2 class="h2"> Veja Também </h2>
	<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar')) : ?>
	<?php endif; ?>
</aside>

<?php get_footer(); ?>