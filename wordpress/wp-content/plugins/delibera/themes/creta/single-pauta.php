<?php 
get_header(); 
$situacao = delibera_get_situacao(get_the_ID());
?>

<div id="delibera" class="situacao-<?php echo $situacao->slug; ?>">
    <div id="container">
    	<div id="content" role="main">
    		<?php
    		
    		get_delibera_header();
    		load_template(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'loop-pauta.php', true);
    		
    		?>
    	</div><!-- #content -->
    </div><!-- #container -->
</div>

<?php get_footer(); ?>
