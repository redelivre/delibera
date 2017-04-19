<?php
	the_post();
	$situacao = delibera_get_situacao(get_the_ID());
?>
<?php get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<header class="page-header"><?php get_delibera_header(); ?></header>
		<?php load_template(__DIR__ . DIRECTORY_SEPARATOR . 'content-pauta.php', true); ?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
	
<?php get_footer(); ?>
