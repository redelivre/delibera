<?php get_header(); ?>

<div id="container">
	<div id="main-content" role="main">
		<?php
		// Chama o cabeçalho que apresenta o sistema de discussão
		get_delibera_header();

		// Chama o loop
		//get_template_part( 'loop', 'pauta' );
		if(file_exists(get_stylesheet_directory()."/content-pauta.php"))
		{
			load_template(get_stylesheet_directory()."/content-pauta.php");
		}
		else
		{
			global $DeliberaThemes;
			load_template($DeliberaThemes->themeFilePath('content-pauta.php'), true);
		}
		?>
	</div><!-- #content -->
</div><!-- #container -->

<?php get_footer(); ?>
