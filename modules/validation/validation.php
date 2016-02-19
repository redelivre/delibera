<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class DeliberaValidation
{
	
	public function __construct()
	{
		add_action('delibera_situacao_register', array($this, 'registerTax'));
		add_filter('delibera_get_main_config', array($this, 'getMainConfig'));
		add_filter('delivera_config_page_rows', array($this, 'configPageRows'), 10, 2);
		add_filter('delibera_situation_button_text', array($this, 'situationButtonText'));
		add_action('delibera_topic_meta', array($this, 'topicMeta'), 10, 5);
		add_action('delibera_publish_pauta', array($this, 'publishPauta'), 10, 3);
		add_filter('delibera_check_post_data', array($this, 'checkPostData'), 10, 3);
		add_filter('delibera_save_post_metas', array($this, 'savePostMetas'), 10, 2);
		add_action('delibera_create_pauta_frontend', array($this, 'createPautaAtFront'));
		
		add_shortcode( 'delibera_lista_de_propostas', array($this, 'replacePropostas' ));
	}
	
	/**
	 * Register Tax for the module
	 */
	public function registerTax()
	{
		if(term_exists('validacao', 'situacao', null) == false)
		{
			delibera_insert_term('Proposta de Pauta', 'situacao',
				array(
					'description'=> 'Pauta em Validação',
					'slug' => 'validacao',
				),
				array(
					'qtrans_term_pt' => 'Proposta de Pauta',
					'qtrans_term_en' => 'Proposed Topic',
					'qtrans_term_es' => 'Agenda Propuesta',
				)
			);
		}
		if(term_exists('naovalidada', 'situacao', null) == false)
		{
			delibera_insert_term('Pauta Recusada', 'situacao',
				array(
					'description'=> 'Pauta não Validação',
					'slug' => 'naovalidada',
				),
				array(
					'qtrans_term_pt' => 'Pauta Recusada',
					'qtrans_term_en' => 'Rejected Topic',
					'qtrans_term_es' => 'Agenda Rechazada',
				)
			);
		}
	}
	
	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['minimo_validacao'] = '10';
		$opts['dias_validacao'] = '5';
		$opts['validacao'] = 'S';
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		$rows[] = array(
				"id" => "validacao",
				"label" => __('É necessário validação das pautas?', 'delibera'),
				"content" => '<input type="checkbox" name="validacao" id="validacao" value="S" '.(htmlspecialchars_decode($opt['validacao']) == 'S' ? 'checked="checked"' : '').' />'
		);
		$rows[] = array(
				"id" => "minimo_validacao",
				"label" => __('Mínimo de validações para uma pauta:', 'delibera'),
				"content" => '<input type="text" name="minimo_validacao" id="minimo_validacao" value="'.htmlspecialchars_decode($opt['minimo_validacao']).'"/>'
		);
		
		$rows[] = array(
				"id" => "dias_validacao",
				"label" => __('Dias para validação da pauta:', 'delibera'),
				"content" => '<input type="text" name="dias_validacao" id="dias_validacao" value="'.htmlspecialchars_decode($opt['dias_validacao']).'"/>'
		);
		return $rows;
	}
	
	/**
	 * Label to apply to button
	 * @param unknown $situation
	 */
	public function situationButtonText($situation)
	{
		if($situation == 'validacao')
		{
			return __('Votar', 'delibera');
		}
		
		return $situation;
	}
	
	/**
	 * 
	 * Post Meta Fields display
	 * 
	 * @param \WP_Post $post
	 * @param array $custom post custom fields
	 * @param array $options_plugin_delibera Delibera options array
	 * @param WP_Term $situacao
	 * @param bool $disable_edicao
	 * 
	 */
	public function topicMeta($post, $custom, $options_plugin_delibera, $situacao, $disable_edicao)
	{
		$validacoes = array_key_exists("numero_validacoes", $custom) ?  $custom["numero_validacoes"][0] : 0;
		
		$min_validacoes = array_key_exists("min_validacoes", $custom) ?  $custom["min_validacoes"][0] : htmlentities($options_plugin_delibera['minimo_validacao']);
		
		$dias_validacao = intval(htmlentities($options_plugin_delibera['dias_validacao']));
		
		$now = strtotime(date('Y/m/d')." 11:59:59");
		
		$prazo_validacao_sugerido = strtotime("+$dias_validacao days", $now);
		
		$prazo_validacao = date('d/m/Y', $prazo_validacao_sugerido);
		
		if(!($post->post_status == 'draft' ||
				$post->post_status == 'auto-draft' ||
				$post->post_status == 'pending'))
		{
			$prazo_validacao = array_key_exists("prazo_validacao", $custom) ?  $custom["prazo_validacao"][0] : $prazo_validacao;
		}
		
		if($options_plugin_delibera['validacao'] == "S")
		{
		?>
			<p>
				<label for="min_validacoes" class="label_min_validacoes"><?php _e('Mínimo de Validações','delibera'); ?>:</label>
				<input <?php echo $disable_edicao ?> id="min_validacoes" name="min_validacoes" class="min_validacoes widefat" value="<?php echo $min_validacoes; ?>"/>
			</p>
			<p>
				<label for="prazo_validacao" class="label_prazo_validacao"><?php _e('Prazo para Validação','delibera') ?>:</label>
				<input <?php echo $disable_edicao ?> id="prazo_validacao" name="prazo_validacao" class="prazo_validacao widefat hasdatepicker" value="<?php echo $prazo_validacao; ?>"/>
			</p>
		<?php
		}
		
	}
	
	public function publishPauta($postID, $opt, $alterar)
	{
		if(!array_key_exists('validacao', $opt) || $opt['validacao'] == 'S' && $opt['flow'][0] == 'validacao' )
		{
			if(!$alterar)
			{
				wp_set_object_terms($postID, 'validacao', 'situacao', false);
			}
		
		}
		$events_meta = array();
		$events_meta['delibera_numero_comments_validacoes'] = 0;
		$events_meta['numero_validacoes'] = 0;
		
		foreach ($events_meta as $key => $value) // Buscar dados
		{
			if(get_post_meta($postID, $key, true)) // Se já existe
			{
				update_post_meta($postID, $key, $value); // Atualiza
			}
			else
			{
				add_post_meta($postID, $key, $value, true); // Senão, cria
			}
		}
	}
	
	function checkPostData($erros, $opt, $autosave)
	{
		if($opt['validacao'] == 'S')
		{
			$value = $_POST['prazo_validacao'];
			$valida = delibera_tratar_data($value);
			if(!$autosave && ($valida === false || $valida < 1))
			{
				$erros[] = __("É necessário definir corretamente o prazo de validação", "delibera");
			}
			
			$value = (int)$_POST['min_validacoes'];
			$valida = is_int($value) && $value > 0;
			if(!$autosave && ($valida === false))
			{
				$erros[] = __("É necessário definir corretamente o número mínimo de validações", "delibera");
			}
		}
		return $erros;
	}
	
	/**
	 *
	 * Retorna pautas em Validação
	 * @param array $filtro
	 */
	public static function getPropostas($filtro = array())
	{
		return self::getPautas($filtro);
	}
	
	/**
	 *
	 * Retorna pautas em Validação
	 * @param array $filtro
	 */
	public static function getPautas($filtro = array())
	{
		return delibera_get_pautas_em($filtro, 'validacao');
	}
	
	public function replacePropostas($matches)
	{
		global $wp_posts;
		$temp = explode(',', $matches[1]); // configurações da shorttag
		$count = count($temp);
	
		$param = array(); // TODO Tratar Parametros
	
		$html = DeliberaValidation::getPropostas($param);
	
		$wp_posts = $html;
		global $post;
		$old = $post;
		echo '<div id="lista-de-pautas">';
		foreach ( $wp_posts as $wp_post )
		{
			$post = $wp_post;
			include 'delibera_loop_pauta.php';
		}
		echo '</div>';
		$post = $old;
	
		return ''; // Retornar código da representação
	}
	
	public function savePostMetas($events_meta, $opt)
	{
		if( // Se tem validação, tem que ter o prazo
			$opt['validacao'] == 'N' ||
			(array_key_exists('prazo_validacao', $_POST) && array_key_exists('min_validacoes', $_POST) )
		)
		{
			$events_meta['prazo_validacao'] = $opt['validacao'] == 'S' ? $_POST['prazo_validacao'] : date('d/m/Y');
			$events_meta['min_validacoes'] = $opt['validacao'] == 'S' ? $_POST['min_validacoes'] : 10;
		}
		
		return $events_meta;
	}
	
	public function createPautaAtFront($opt)
	{
		if($opt['validacao'] == 'S'){
			$_POST['prazo_validacao'] = date('d/m/Y', strtotime ('+'.$opt['dias_validacao'].' DAYS'));
			$_POST['min_validacoes'] = $opt['minimo_validacao'];
		}
	}
	
}
$DeliberaValidation = new DeliberaValidation();


