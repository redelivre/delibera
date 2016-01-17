<?php get_header(); ?>

<section class="archive">
	<?php if (have_posts()) : ?>

		<header>
			<h2 class="h2"><?php single_cat_title(); ?></h2>
			<p><?php echo category_description(); ?></p>
		</header>

		<div class="container">
			<?php while (have_posts()) : the_post(); ?>
				<article>
					<a href="<?php the_permalink(); ?>">
						<?php if (has_post_thumbnail()) : ?>						
							<figure>
								<?php the_post_thumbnail(); ?>
							</figure>
						<?php endif; ?>
						<div class="info"><?php the_time('d.m.Y') ?></div>
						<h3><?php the_title(); ?></h3>
						<p><?php echo limit_chars(get_the_excerpt(), 240); ?></p>
					</a>
				</article>
			<?php endwhile; ?>
		</div>

		<?php echo paginacao(); ?>
	</section>

<?php else : ?>
	<?php get_template_part('404'); ?>
<?php endif; ?>

<?php get_footer(); ?>