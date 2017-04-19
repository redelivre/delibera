<?php get_header(); ?>

<section class="singles">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="container">
			<h1><?php the_title(); ?></h1>

			<article class="lead">
				<?php get_template_part('social'); ?>	
				<?php the_content(); ?>
			</article>			
		</div>
	<?php endwhile; ?>
</section>

<?php else : ?>
	<?php get_template_part('404'); ?>
<?php endif; ?>


<?php get_footer(); ?>