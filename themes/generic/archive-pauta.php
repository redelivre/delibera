<?php get_header();
	$options_plugin_delibera = delibera_get_config();
?>
<div id="container">
	<div id="main-content" role="main">
		<?php
		// Chama o cabeçalho que apresenta o sistema de discussão
		get_delibera_header();
		?>
		<div class="filter-bar">
			<div class="title">
				<h2><?php echo _e('Todas as discussões', 'delibera'); ?></h2>
			</div>
			<div class="actions">
				<a href="<?php echo $options_plugin_delibera['url_nova_pauta'] == '' ? '/wp-admin/post-new.php?post_type=pauta' : $options_plugin_delibera['url_nova_pauta']; ?>" class="button"><?php echo $options_plugin_delibera['titulo_nova_pauta']; ?></a>
			</div>
		</div>

		<div class="lista-de-pautas">
			<?php
			global $deliberaThemes;
			$deliberaThemes->archiveLoop();

			$default_flow = isset($options_plugin_delibera['delibera_flow']) ? $options_plugin_delibera['delibera_flow'] : array();
			$default_flow = apply_filters('delibera_flow_list', $default_flow);
			?>

			<?php
			global $wp_query;
			$big = 99999999; // need an unlikely integer
			
			$links = paginate_links(array(
				'base' => str_replace($big, '%#%', get_pagenum_link($big)),
				'format' => '?paged=%#%',
				'total' => $wp_query->max_num_pages,
				'current' => max(1, get_query_var('paged')),
				'type' => 'array',
				'prev_next' => false,
			));

			?>
			
			<?php if (!empty($links)) : ?>
				<nav class="navigation">
					<ol>
						<?php foreach ($links as $link) : ?>
							<li><?php echo $link; ?></li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>
		</div>
		<div class="banner-ciclo">
				<h3 class="title">Ciclo de vida de uma pauta</h3>
				<p class="description">
					Entenda como funciona o ciclo de pautas dentro do Delibera, <br>abaixo os possíveis ciclos.
				</p>
				<ul class="ciclos"><?php
				$i = 1;
				foreach ($default_flow as $situacao)
				{
					switch($situacao)
					{
						case 'validacao':?>
						<li class="validacao"><?php echo $i; ?><br>Validação</li><?php
						break;
						case 'discussao': ?>
						<li class="discussao"><?php echo $i; ?><br>Discussão</li><?php
						break;
						case 'relatoria':
						case 'eleicao_relator': ?>
						<li class="relatoria"><?php echo $i; ?><br>Relatoria</li><?php
						break;
						case 'emvotacao': ?>
						<li class="emvotacao"><?php echo $i; ?><br>Votação</li><?php
						break;
						case 'naovalidada':
						case 'comresolucao': ?>
						<li class="comresolucao"><?php echo $i; ?><br>Resolução</li><?php
						break;
					}
					$i++;
				}?>
			</ul>
		</div>
	</div><!-- #content -->
</div><!-- #container -->

<?php
get_footer();
?>
