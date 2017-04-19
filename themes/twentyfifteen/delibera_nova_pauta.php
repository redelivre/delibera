<?php 
global $deliberaThemes;
                
get_header();
?>

<div id="delibera">
	<section id="content" class="content-area">
		<main id="main" class="site-main" role="main">
			<header class="page-header">
    		<?php
    			// Chama o cabeçalho que apresenta o sistema de discussão
    			get_delibera_header();
    		?>
			</header>
			<div class="entry-content">
			<?php 
            // chama o formulário de nova pauta
            if (is_user_logged_in())
                include $deliberaThemes->themeFilePath('form-nova-pauta.php');
        
    		?>
    		</div>
    	</main><!-- #content -->
    </section><!-- #container -->
</div>

<?php get_footer(); ?>
