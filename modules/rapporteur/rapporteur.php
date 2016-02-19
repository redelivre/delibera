<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class DeliberaRapporteur
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
		
	}
	
	/**
	 * Register Tax for the module
	 */
	public function registerTax()
	{
		if(isset($opt['relatoria']) && $opt['relatoria'] == 'S')
		{
			if($opt['eleicao_relator'] == 'S')
			{
				if(term_exists('eleicaoredator', 'situacao', null) == false)
				{
					delibera_insert_term('Regime de Votação de Relator', 'situacao', array(
							'description'=> 'Pauta em Eleição de Relator',
							'slug' => 'eleicaoredator',
						),
						array(
							'qtrans_term_pt' => 'Regime de Votação de Relator',
							'qtrans_term_en' => 'Election of Rapporteur',
							'qtrans_term_es' => 'Elección del Relator',
						)
					);
				}
			}

			if(term_exists('relatoria', 'situacao', null) == false)
			{
				delibera_insert_term('Relatoria', 'situacao', array(
						'description'=> 'Pauta com encaminhamentos em Relatoria',
						'slug' => 'relatoria',
					),
					array(
						'qtrans_term_pt' => 'Relatoria',
						'qtrans_term_en' => 'Rapporteur',
						'qtrans_term_es' => 'Relator',
					)
				);
			}
		}
	}
	
	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['dias_relatoria'] = '2';
	    $opts['relatoria'] = 'N';
	    $opts['eleicao_relator'] = 'N';
	    $opts['dias_votacao_relator'] = '2';
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		$rows[] = array(
			"id" => "relatoria",
			"label" => __('Necessário relatoria da discussão das pautas?', 'delibera'),
			"content" => '<input type="checkbox" id="relatoria" name="relatoria" value="S" '.(htmlspecialchars_decode($opt['relatoria']) == 'S' ? 'checked="checked"' : '').' />'
		);
		$rows[] = array(
			"id" => "dias_relatoria",
			"label" => __('Prazo para relatoria:', 'delibera'),
			"content" => '<input type="text" name="dias_relatoria" id="dias_relatoria" value="'.htmlspecialchars_decode($opt['dias_relatoria']).'"/>'
		);
		/*$rows[] = array(
			"id" => "eleicao_relator",
			"label" => __('Necessário eleição de relator?', 'delibera'),
			"content" => '<input type="checkbox" name="eleicao_relator" value="S" '.(htmlspecialchars_decode($opt['eleicao_relator']) == 'S' ? 'checked="checked"' : '').' />'
		);
		$rows[] = array(
			"id" => "dias_votacao_relator",
			"label" => __('Prazo para eleição de relator:', 'delibera'),
			"content" => '<input type="text" name="dias_votacao_relator" id="dias_votacao_relator" value="'.htmlspecialchars_decode($opt['dias_votacao_relator']).'"/>'
		);*/
		return $rows;
	}
	
	/**
	 * Label to apply to button
	 * @param unknown $situation
	 */
	public function situationButtonText($situation)
	{
		if($situation == 'relatoria')
		{
			return __('Relatar', 'delibera');
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
		$now = strtotime(date('Y/m/d')." 11:59:59");
		
		$dias_relatoria = intval(htmlentities($options_plugin_delibera['dias_relatoria']));
		$dias_votacao_relator = intval(htmlentities($options_plugin_delibera['dias_votacao_relator']));

		if($options_plugin_delibera['relatoria'] == "S") // Adiciona prazo de relatoria se for necessário
		{
			//$dias_relatoria += $dias_discussao; // TODO issue #50
			if($options_plugin_delibera['eleicao_relator'] == "S") // Adiciona prazo de vatacao relator se for necessário
			{
				$dias_relatoria += $dias_votacao_relator;
				$dias_votacao_relator += $dias_discussao;
			}
		}
	
		$prazo_eleicao_relator_sugerido = strtotime("+$dias_votacao_relator days", $now);
		$prazo_relatoria_sugerido = strtotime("+$dias_relatoria days", $now);
	
		$prazo_eleicao_relator = date('d/m/Y', $prazo_eleicao_relator_sugerido);
		$prazo_relatoria = date('d/m/Y', $prazo_relatoria_sugerido);
		
		if(!($post->post_status == 'draft' ||
				$post->post_status == 'auto-draft' ||
				$post->post_status == 'pending'))
		{
			$prazo_eleicao_relator = array_key_exists("prazo_eleicao_relator", $custom) ?  $custom["prazo_eleicao_relator"][0] : $prazo_eleicao_relator;
			$prazo_relatoria = array_key_exists("prazo_relatoria", $custom) ?  $custom["prazo_relatoria"][0] : $prazo_relatoria;
		}
		
		if($options_plugin_delibera['relatoria'] == "S")
		{
			if($options_plugin_delibera['eleicao_relator'] == "S")
			{
			?>
				<p>
					<label for="prazo_eleicao_relator" class="label_prazo_eleicao_relator"><?php _e('Prazo para Eleição de Relator','delibera') ?>:</label>
					<input <?php echo $disable_edicao ?> id="prazo_eleicao_relator" name="prazo_eleicao_relator" class="prazo_eleicao_relator widefat hasdatepicker" value="<?php echo $prazo_eleicao_relator; ?>"/>
				</p>
			<?php
			}
			?>
			<p>
				<label for="prazo_relatoria" class="label_prazo_relatoria"><?php _e('Prazo para Relatoria','delibera') ?>:</label>
				<input <?php echo $disable_edicao ?> id="prazo_relatoria" name="prazo_relatoria" class="prazo_relatoria widefat hasdatepicker" value="<?php echo $prazo_relatoria; ?>"/>
			</p>
			<?php
		}
		
	}
	
	public function publishPauta($postID, $opt, $alterar)
	{
		if(!array_key_exists('relatoria', $opt) || $opt['relatoria'] == 'S' && $opt['flow'][0] == 'relatoria' )
		{
			if(!$alterar)
			{
				if($opt['eleicao_relator'] == 'S')
				{
					wp_set_object_terms($postID, 'eleicaoredator', 'situacao', false);
				}
				else 
				{
					wp_set_object_terms($postID, 'relatoria', 'situacao', false);
				}
			}
		
		}
	}
	
	function checkPostData($erros, $opt, $autosave)
	{
		if($opt['relatoria'] == 'S')
		{
			$value = $_POST['prazo_relatoria'];
			$valida = delibera_tratar_data($value);
            if(!$autosave && ($valida === false || $valida < 1))
            {
                $erros[] = __("É necessário definir corretamente o prazo para relatoria", "Delibera");
			}

			if($opt['eleicao_relator'] == 'S')
			{
				$value = $_POST['prazo__leicao_relator'];
				$valida = delibera_tratar_data($value);
				if(!$autosave && ($valida === false || $valida < 1))
				{
					$erros[] = __("É necessário definir corretamente o prazo para eleição de um relator", "delibera");
				}
			}

		}
		return $erros;
	}
	
	/**
	 *
	 * Retorna pautas em Relatoria ou Eleição para relator
	 * 
	 * @param array $filtro
	 */
	public static function getPautas($filtro = array())
	{
		return delibera_get_pautas_em($filtro, array('eleicaoredator', 'relatoria'));
	}
	
	public function savePostMetas($events_meta, $opt)
	{
		if(
			( // Se tem relatoria, tem que ter o prazo
				$opt['relatoria'] == 'N' ||
				array_key_exists('prazo_relatoria', $_POST)
			) &&
			( // Se tem relatoria, e é preciso eleger o relator, tem que ter o prazo para eleição
				$opt['relatoria'] == 'N' ||
				(
					$opt['eleicao_relator'] == 'N' ||
					array_key_exists('prazo_eleicao_relator', $_POST)
				)
			)
		)
		{
			$events_meta['prazo_relatoria'] = $opt['relatoria'] == 'S' ? $_POST['prazo_relatoria'] : date('d/m/Y');
			$events_meta['prazo_eleicao_relator'] = $opt['relatoria'] == 'S' && $opt['eleicao_relator'] == 'S' ? $_POST['prazo_eleicao_relator'] : date('d/m/Y');
		}
		
		return $events_meta;
	}
	
	public function createPautaAtFront($opt)
	{
		if($opt['relatoria'] == 'S')
		{
			$_POST['prazo_relatoria'] = date('d/m/Y', strtotime ('+'.$opt['dias_relatoria'].' DAYS'));
			if($opt['eleicao_relator'] == 'S')
			{
				$_POST['prazo_eleicao_relator'] = date('d/m/Y', strtotime ('+'.$opt['dias_votacao_relator'].' DAYS'));
			}
		}
	}
	
}
$DeliberaRapporteur = new DeliberaRapporteur();


