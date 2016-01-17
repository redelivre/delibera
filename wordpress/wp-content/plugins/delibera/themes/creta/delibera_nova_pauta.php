<?php 
global $deliberaThemes;
                
get_header();
?>

<div id="delibera">
    <div id="container">
    	<div id="content" role="main">
    		<?php

    		// Chama o cabeçalho que apresenta o sistema de discussão
    		get_delibera_header();
		
            // chama o formulário de nova pauta
            if (is_user_logged_in()) {
                include $deliberaThemes->themeFilePath('form-nova-pauta.php');
            }
        
    		?>
    	</div><!-- #content -->
    </div><!-- #container -->
</div>

<?php get_footer(); ?>
