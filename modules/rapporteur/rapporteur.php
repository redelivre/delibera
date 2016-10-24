<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class Rapporteur extends \Delibera\Modules\ModuleBase
{
	/**
	 * List of of topic status
	 * @var array
	 */
	public $situacao = array('relatoria', 'eleicao_relator');
	
	/**
	 * 
	 * @var array list of module flows
	 */
	protected $flows = array('relatoria');
	
	/**
	 * Name of module deadline metadata
	 * @var array situacao a => deadline_a
	 */
	protected $prazo_meta = array('relatoria' => 'prazo_relatoria', 'eleicao_relator' => 'prazo_eleicao_relator');
	
	/**
	 * Config days to make new deadline
	 * @var array
	 */
	protected $days = array('dias_relatoria', 'dias_votacao_relator');
	
	/**
	 * Display priority
	 * @var int
	 */
	public $priority = 3;
	
	/**
	 * Register Tax for the module
	 */
	public function registerTax()
	{
		/*if(term_exists('eleicao_redator', 'situacao', null) == false) // TODO create election
		{
			delibera_insert_term('Regime de Votação de Relator', 'situacao', array(
					'description'=> 'Pauta em Eleição de Relator',
					'slug' => 'eleicao_redator',
				),
				array(
					'qtrans_term_pt' => 'Regime de Votação de Relator',
					'qtrans_term_en' => 'Election of Rapporteur',
					'qtrans_term_es' => 'Elección del Relator',
				)
			);
		}*/

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
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::initModule()
	 */
	public function initModule($post_id)
	{
		$opts = delibera_get_config();
		if($opts['eleicao_relator'] == 'S')
		{
			wp_set_object_terms($post_id, 'eleicao_redator', 'situacao', false); //Mudar situação para Votação
		}
		else
		{
			wp_set_object_terms($post_id, 'relatoria', 'situacao', false); //Mudar situação para Votação
		}
		$this->newDeadline($post_id);
	}
	
	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['dias_relatoria'] = '2';
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
			"id" => "dias_relatoria",
			"label" => __('Prazo para relatoria:', 'delibera'),
			"content" => '<input type="text" name="dias_relatoria" id="dias_relatoria" value="'.htmlspecialchars_decode($opt['dias_relatoria']).'" autocomplete="off" />'
		);
		/*$rows[] = array(
			"id" => "eleicao_relator",
			"label" => __('Necessário eleição de relator?', 'delibera'),
			"content" => '<input type="checkbox" name="eleicao_relator" value="S" '.(htmlspecialchars_decode($opt['eleicao_relator']) != 'N' ? 'checked="checked"' : '').' autocomplete="off"  />'
		);
		$rows[] = array(
			"id" => "dias_votacao_relator",
			"label" => __('Prazo para eleição de relator:', 'delibera'),
			"content" => '<input type="text" name="dias_votacao_relator" id="dias_votacao_relator" value="'.htmlspecialchars_decode($opt['dias_votacao_relator']).'" autocomplete="off" />'
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
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::generateDeadline()
	 */
	public function generateDeadline($options_plugin_delibera)
	{
		$dias_relatoria = intval(htmlentities($options_plugin_delibera['dias_relatoria']));
		$dias_votacao_relator = intval(htmlentities($options_plugin_delibera['dias_votacao_relator']));
		
		//$dias_relatoria += $dias_discussao; // TODO issue #50
		if($options_plugin_delibera['eleicao_relator'] == "S") // Adiciona prazo de vatacao relator se for necessário
		{
			$dias_relatoria += $dias_votacao_relator;
		}
		
		$prazo_eleicao_relator_sugerido = strtotime("+$dias_votacao_relator days", delibera_tratar_data(\Delibera\Flow::getLastDeadline('relatoria')));
		$prazo_relatoria_sugerido = strtotime("+$dias_relatoria days", delibera_tratar_data(\Delibera\Flow::getLastDeadline('relatoria')));
		
		$prazo_eleicao_relator = date('d/m/Y', $prazo_eleicao_relator_sugerido);
		
		return date('d/m/Y', $prazo_relatoria_sugerido);
		
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
		$dias_votacao_relator = intval(htmlentities($options_plugin_delibera['dias_votacao_relator']));
		
		$prazo_eleicao_relator_sugerido = strtotime("+$dias_votacao_relator days", delibera_tratar_data(\Delibera\Flow::getLastDeadline('relatoria', $post->ID)));
		
		$prazo_eleicao_relator = date('d/m/Y', $prazo_eleicao_relator_sugerido);
		
		$prazo_relatoria = $this->generateDeadline($options_plugin_delibera);
		
		if(!($post->post_status == 'draft' ||
				$post->post_status == 'auto-draft' ||
				$post->post_status == 'pending'))
		{
			$prazo_eleicao_relator = array_key_exists("prazo_eleicao_relator", $custom) ?  $custom["prazo_eleicao_relator"][0] : $prazo_eleicao_relator;
			$prazo_relatoria = array_key_exists("prazo_relatoria", $custom) ?  $custom["prazo_relatoria"][0] : $prazo_relatoria;
		}
		
		if($options_plugin_delibera['eleicao_relator'] == "S")
		{ //TODO remove display none when have election
		?>
			<p style="display: none;">
				<label class="label_prazo_eleicao_relator"><?php _e('Prazo para Eleição de Relator','delibera') ?>:</label>
				<input <?php echo $disable_edicao ?> name="prazo_eleicao_relator" class="prazo_eleicao_relator widefat hasdatepicker" value="<?php echo $prazo_eleicao_relator; ?>"/>
			</p>
		<?php
		}
		?>
		<p>
			<label class="label_prazo_relatoria"><?php _e('Prazo para Relatoria','delibera') ?>:</label>
			<input <?php echo $disable_edicao ?> name="prazo_relatoria" class="prazo_relatoria widefat hasdatepicker" value="<?php echo $prazo_relatoria; ?>"/>
		</p>
		<?php
		
	}
	
	public function publishPauta($postID, $opt)
	{
		
	}
	
	function checkPostData($errors, $opt, $autosave)
	{
		$value = $_POST ['prazo_relatoria'];
		$valida = delibera_tratar_data ( $value );
		if(!$autosave && (empty($value) ||  $valida === false || $valida < 1) )
		{
			$errors [] = __ ( "É necessário definir corretamente o prazo para relatoria", "Delibera" );
		}
		
		if ($opt ['eleicao_relator'] == 'S')
		{
			$value = $_POST ['prazo_eleicao_relator'];
			$valida = delibera_tratar_data ( $value );
			if(!$autosave && (empty($value) ||  $valida === false || $valida < 1) )
			{
				$errors [] = __ ( "É necessário definir corretamente o prazo para eleição de um relator", "delibera" );
			}
		}

		return $errors;
	}
	
	public function savePostMetas($events_meta, $opt, $post_id = false)
	{
		if(
			// Se tem relatoria, tem que ter o prazo
			array_key_exists('prazo_relatoria', $_POST)
			&&
			( // Se tem relatoria, e é preciso eleger o relator, tem que ter o prazo para eleição
				$opt['eleicao_relator'] == 'N' ||
				array_key_exists('prazo_eleicao_relator', $_POST)
			)
		)
		{
			$events_meta['prazo_relatoria'] = sanitize_text_field($_POST['prazo_relatoria']);
			$events_meta['prazo_eleicao_relator'] = $opt['eleicao_relator'] == 'S' ? sanitize_text_field($_POST['prazo_eleicao_relator']) : date('d/m/Y');
		}
		
		return $events_meta;
	}
	
	public function createPautaAtFront($opt)
	{
		$_POST['prazo_relatoria'] = $this->generateDeadline($opt);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::deadline()
	 */
	public static function deadline($args)
	{
		$situacao = delibera_get_situacao($args['post_ID']);
		$post_id = $args['post_ID'];
		if($situacao->slug == 'relatoria')
		{
			if(count(delibera_get_comments_encaminhamentos($post_id)) > 0)
			{
				//wp_set_object_terms($post_id, 'emvotacao', 'situacao', false); //Mudar situação para Votação
				\Delibera\Flow::next($post_id);
				
				//delibera_notificar_situacao($post_id);
				
				if(has_action('delibera_relatoria_concluida'))
				{
					do_action('delibera_relatoria_concluida', $post_id);
				}
			}
			else
			{
				$rapporteur = \Delibera\Flow::getCurrentModule($post_id);
				$rapporteur->newDeadline($post_id, false);
			}
		}
		elseif($situacao->slug == 'eleicao_relator')
		{
			//TODO eleicao relator deadline
			wp_set_object_terms($post_id, 'relatoria', 'situacao', false);
			$this->newDeadline($post_id);
		}
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::getCommentListLabel()
	 */
	public function getCommentListLabel()
	{
		return __('Relatoria da Pauta', 'delibera');
	}
	
}
$DeliberaRapporteur = new \Delibera\Modules\Rapporteur();


