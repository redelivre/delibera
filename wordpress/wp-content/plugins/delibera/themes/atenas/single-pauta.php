<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">
			
				<?php
				
				// Chama o cabeçalho que apresenta o sistema de discussão
				get_delibera_header();
				
				// Chama o loop
				//get_template_part( 'loop', 'pauta' );
				load_template(dirname(__FILE__).DIRECTORY_SEPARATOR.'loop-pauta.php', true);
				
				?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>
