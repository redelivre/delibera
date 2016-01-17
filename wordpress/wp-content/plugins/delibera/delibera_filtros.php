<?php

function delibera_filtros_gerar()
{
    global $deliberaThemes;
    
	?>
	<div id="filtro-horizontal">
		<h4><?php _e( 'Filtros de conteúdo', 'delibera' ); ?><span id="delibera-filtros-archive-mostrar" onclick="delibera_filtros_archive_mostrar()" class="delibera-filtros-mostrar" style="display: none" title="Mostrar Filtros" ></span><span id="delibera-filtros-archive-esconder" onclick="delibera_filtros_archive_esconder()" class="delibera-filtros-esconder" title="Esconder Filtros"></span></h4>
		<form action="<?php the_permalink(); ?>" id="form-filtro" method="post">
		<?php
			delibera_filtros_get_filtros('tema');
		?>
		<div style="clear:both;"> </div>
		<span id="form-filtro-button"><?php _e("Recarregar", 'delibera')?></span>
		</form>  
	</div><!-- #filtro -->
	<script type="text/javascript">
	//<![CDATA[
		jQuery.extend({
		  getUrlVars: function(){
		    var vars = [], hash;
		    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		    for(var i = 0; i < hashes.length; i++)
		    {
		      hash = hashes[i].split('=');
		      vars.push(hash[0]);
		      vars[hash[0]] = hash[1];
		    }
		    return vars;
		  },
		  getUrlVar: function(name){
		    return jQuery.getUrlVars()[name];
		  }
		});
		function delibera_filtros_archive_mostrar()
		{
			document.getElementById('form-filtro').style.display = 'block';
			document.getElementById('delibera-filtros-archive-esconder').style.display = 'block';
			document.getElementById('delibera-filtros-archive-mostrar').style.display = 'none';
			return false;
		}
		function delibera_filtros_archive_esconder()
		{
			document.getElementById('form-filtro').style.display = 'none';
			document.getElementById('delibera-filtros-archive-mostrar').style.display = 'block';
			document.getElementById('delibera-filtros-archive-esconder').style.display = 'none';
			return false;
		}
		function overlay_filtro(id)
		{
			var html = '<div id="'+id+'">';
			<?php
			
			$iloader = $deliberaThemes->getThemeDir() . "/images/ajax-loader.gif";
			$iloader_padrao = $deliberaThemes->getThemeUrl() . "/images/ajax-loader.gif";
			if(file_exists($iloader))
			{
			?>
				html += '<img id="overlay_filtros" src="'+'<?php echo $iloader; ?>" ';
			<?php
			}
			else
			{
			?>
				html += '<img id="overlay_filtros" src="'+'<?php echo $iloader_padrao; ?>" ';
			<?php
			}
			?>
			html += 'alt="<?php _e("Carregando","delibera") ?>&hellip;"';
			html += '</div>';
			jQuery('#'+id).replaceWith(html);
		}
		function delibera_update_posts()
		{
			var tema_filtro = new Array();
			jQuery("input[name='tema_filtro[]']:checked").each(function() {tema_filtro.push(jQuery(this).val());});
			
			var data =
			{
				action: 'delibera_filtros_archive',
				tema_filtro: tema_filtro
			};
			 
	        jQuery.ajax({
	        		type: 'POST',
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
	        		data: data,
	        		success: function(response)
				        {
				            jQuery('#lista-de-pautas').replaceWith(response); 
				            delibera_update_pager(); 
				        },
				    beforeSend: function()
				        {
				        	overlay_filtro('lista-de-pautas');
				        }, 
	        });
		}
		jQuery(document).ready(function()
		{
			jQuery('#form-filtro-button').click(function()
			{
				delibera_update_posts();
			});
			delibera_update_posts();
		});
		function delibera_update_pager()
		{
			jQuery('.page').click(function()
			{
				var tema_filtro = new Array();
				jQuery("input[name='tema_filtro[]']:checked").each(function() {tema_filtro.push(jQuery(this).val());});
				var paged = this.text;
				var data =
				{
					action: 'delibera_filtros_archive',
					tema_filtro: tema_filtro,
					paged: paged
				};
				 
		        jQuery.ajax({
		        		type: 'POST',
						url: '<?php echo admin_url('admin-ajax.php'); ?>',
		        		data: data,
		        		success: function(response)
					        {
					            jQuery('#lista-de-pautas').replaceWith(response); 
					            delibera_update_pager();
					        },
					    beforeSend: function()
					        {
					        	overlay_filtro('lista-de-pautas');
					        }, 
		        });
		        return false;
			});
		}
	//]]>
	</script>
	<?php
}

function delibera_filtros_get_filtros($tax, $value = false, $linha = "<br/>")
{
	$terms = get_terms($tax, array('hide_empty' => 0, 'filtro' => true));
	?>
	<label class="form-filtro-ultimos-chebox-label-todos"><input type="checkbox"
			name="<?php echo "{$tax}_todos[]"; ?>"
			value="todos" class="form-filtro-ultimos-chebox-todos"
			autocomplete="off"
			id="form-filtro-ultimos-todos-<?php echo "{$tax}"; ?>"
			onclick="jQuery('input[name=\'<?php echo $tax; ?>_filtro[]\']').prop('checked', this.checked);"
		/><?php _e("Marcar todos", 'delibera'); ?></label>
		
	<div class="form-filtro-ultimos-div">
	<?php
	foreach ($terms as $term)
	{
		
		?>
				<span class="form-filtro-ultimos-chebox-span">
				<label class="form-filtro-ultimos-chebox-label"><input type="checkbox" name="<?php echo "{$tax}_filtro[]"; ?>" value="<?php echo $term->slug; ?>" class="form-filtro-ultimos-chebox" autocomplete="off" /><?php _e($term->name, 'delibera'); ?></label><?php echo $linha; ?>
				</span>
				<?php
		
	}
	?>
		
	</div>
	<?php
	if($value !== false)
	{
		if(is_array($value))
		{
			$values = $value;
			foreach ($values as $value)
			{
				?>
					<script type="text/javascript">
					//<![CDATA[
						jQuery("input[value='<?php echo $value; ?>']").attr('checked', true);
					//]]>
					</script>
				<?php
			}
		}
		else 
		{
		?>
			<script type="text/javascript">
			//<![CDATA[
				jQuery("input[value='<?php echo $value; ?>']").attr('checked', true);
			//]]>
			</script>
		<?php
		}
	}
}

function delibera_filtros_archive_callback()
{
	global $wp_query, $deliberaThemes;
	
	$action = new stdClass();
	$action->canQuery = true;
	
	$args = delibera_filtros_get_tax_filtro($_POST, array('post_type' => 'pauta', 'post_status' => 'publish'));
	
	$paged = ( array_key_exists('paged', $_POST) && $_POST['paged'] > 0 ) ? $_POST['paged'] : 1;
	$args['paged'] = $paged;
	
	$args = apply_filters('delibera_filtros_archive_callback_filter', $args);
	
	query_posts($args);
	?>
	<div id="lista-de-pautas">
		<?php $deliberaThemes->archiveLoop(); // Chama o loop do arquivo ?>
		
		<div id="nav-below" class="navigation">
			<?php if ( function_exists( 'wp_pagenavi' ) ) wp_pagenavi(); ?>
		</div><!-- #nav-below -->
		
	</div>
	<?php
	die();
}
add_action('wp_ajax_delibera_filtros_archive', 'delibera_filtros_archive_callback');
add_action('wp_ajax_nopriv_delibera_filtros_archive', 'delibera_filtros_archive_callback');

/**
 *
 * Retorna array com o filtro de tax para ser usado nas querys do WP
 * @param array $dados Exemplo: $_POST deve conter a chave (array)tema_filtro para tema
 * @param array $args Query
 */
function delibera_filtros_get_tax_filtro($dados, $args, $field = "slug")
{
	$tax_query = array();

	if(array_key_exists('tema_filtro', $dados) && is_array($dados['tema_filtro']))
	{
		$terms = array();
		foreach ($dados['tema_filtro'] as $tema)
		{
			$terms[] = $tema;
		}
		if(count($terms) > 0)
		{
			$tax_query[] = array(
					'taxonomy' => 'tema',
					'field' => $field,
					'terms' => $terms,
					//'operator' => 'OR'
			);
		}
	}

	if(!array_key_exists("tax_query", $args))
	{
		$args["tax_query"] = array();
	}

	$args["tax_query"] = array_merge($args["tax_query"], $tax_query);

	if(count($args["tax_query"]) > 1)
	{
		$args["tax_query"]['relation'] = 'AND';
	}

	return $args;
}

function delibera_filtros_scripts()
{
	if(is_pauta())
	{
		wp_enqueue_script('ui-tooltip',WP_CONTENT_URL.'/plugins/delibera/js/jquery.ui.tooltip.js', array( 'jquery', 'jquery-ui-widget'));
	}
}
add_action( 'wp_print_scripts', 'delibera_filtros_scripts' );

?>