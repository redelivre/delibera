<?php 
get_header(); 
$situacao = delibera_get_situacao(get_the_ID());
?>

<div id="delibera" class="situacao-<?php echo $situacao->slug; ?>">
    <div id="container">
    	<div id="content" role="main">
    		<?php
    		
    		get_delibera_header();
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
</div>

<?php get_footer(); ?>
