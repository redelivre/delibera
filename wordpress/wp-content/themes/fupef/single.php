<?php get_header(); ?>

<section class="singles">
	<?php if (have_posts()) : ?>

		<div class="container">

			<?php while (have_posts()) : the_post();  ?>
				<article role="article">
					<div class="col-3-4">
						<h1><?php the_title(); ?></h1>
						<div class="info"><?php the_time('d.m.Y') ?> | <?php the_category(', ') ?></div>
						<div class="lead">
							<?php the_content(); ?>
						</div>						
					</div>
				</article>
			<?php endwhile; ?>

			<aside class="col-1-4 right sidebar" role="complementary">
				<?php dynamic_sidebar('Sidebar Páginas');?>				
			</aside>
		</div>
	</section>

<?php else : ?>
	<?php get_template_part('404'); ?>
<?php endif; ?>

<?php get_footer(); ?>