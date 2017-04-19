<?php get_header(); ?>
		<div id="container">
			<div id="content" role="main">

				<?php
				// Chama o cabeçalho que apresenta o sistema de discussão
				get_delibera_header();
				
				delibera_filtros_gerar();
				
				
				
				//$args = get_tax_filtro($_REQUEST, array('post_type' => 'pauta'));
				
				
				?>
				
						
				<div id="lista-de-pautas">
					<?php
					// Chama o loop do arquivo
					//wp_reset_query();
					
					//echo count(query_posts($args));
					
					load_template(dirname(__FILE__).DIRECTORY_SEPARATOR.'delibera-loop-archive.php', true);
					
					?>
					
					
					<div id="nav-below" class="navigation">
						<?php if ( function_exists( 'wp_pagenavi' ) )
						{
							
							wp_pagenavi(array('query' => $wp_query)); 
						}
						?>
					</div><!-- #nav-below -->
					
				</div>
				
				
			</div><!-- #content -->
		</div><!-- #container -->

<?php
	get_footer();
?>
