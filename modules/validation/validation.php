<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class Validation extends \Delibera\Modules\ModuleBase
{
	/**
	 *
	 * @var array List of of topic status
	 */
	public $situacao = array('validacao', 'naovalidada');
	
	/**
	 *
	 * @var array list of module flows
	 */
	protected $flows = array('validacao');
	
	/**
	 *
	 * @var String Name of module deadline metadata
	 */
	protected $prazo_meta = 'prazo_validacao';
	
	/**
	 * Config days to make new deadline
	 * @var array
	 */
	protected $days = array('dias_validacao');
	
	/**
	 *
	 * @var array List of pair shotcode name => method
	 */
	protected $shortcodes = array('delibera_lista_de_propostas' => 'replacePropostas' );
	
	/**
	 * Display priority
	 * @var int
	 */
	public $priority = 1;
	
	public function __construct()
	{
		add_action('delibera_pauta_recusada', array('\Delibera\Cron', 'del'));
		parent::__construct();
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
	 * 
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::initModule()
	 */
	public function initModule($post_id)
	{
		wp_set_object_terms($post_id, 'validacao', 'situacao', false);
		$this->newDeadline($post_id);
	}

	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['minimo_validacao'] = '10';
		$opts['dias_validacao'] = '5';
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		$rows[] = array(
				"id" => "minimo_validacao",
				"label" => __('Mínimo de validações para uma pauta:', 'delibera'),
				"content" => '<input type="text" name="minimo_validacao" id="minimo_validacao" value="'.htmlspecialchars_decode($opt['minimo_validacao']).'" autocomplete="off" />'
		);
		
		$rows[] = array(
				"id" => "dias_validacao",
				"label" => __('Dias para validação da pauta:', 'delibera'),
				"content" => '<input type="text" name="dias_validacao" id="dias_validacao" value="'.htmlspecialchars_decode($opt['dias_validacao']).'" autocomplete="off" />'
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
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::generateDeadline()
	 */
	public function generateDeadline($options_plugin_delibera)
	{
		$dias_validacao = intval(htmlentities($options_plugin_delibera['dias_validacao']));
		
		$prazo_validacao_sugerido = strtotime("+$dias_validacao days", delibera_tratar_data(\Delibera\Flow::getLastDeadline('valicacao')));
		
		return date('d/m/Y', $prazo_validacao_sugerido);
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
		
		$prazo_validacao = $this->generateDeadline($options_plugin_delibera);
		
		if(!($post->post_status == 'draft' ||
				$post->post_status == 'auto-draft' ||
				$post->post_status == 'pending'))
		{
			$prazo_validacao = array_key_exists("prazo_validacao", $custom) ?  $custom["prazo_validacao"][0] : $prazo_validacao;
		}
		
		?>
			<p>
				<label  class="label_min_validacoes"><?php _e('Mínimo de Validações','delibera'); ?>:</label>
				<input <?php echo $disable_edicao ?> name="min_validacoes" class="min_validacoes widefat" value="<?php echo $min_validacoes; ?>"/>
			</p>
			<p>
				<label class="label_prazo_validacao"><?php _e('Prazo para Validação','delibera') ?>:</label>
				<input <?php echo $disable_edicao ?> name="prazo_validacao" class="prazo_validacao widefat hasdatepicker" value="<?php echo $prazo_validacao; ?>"/>
			</p>
		<?php
		
	}
	
	/**
	 * When the topic is published
	 * @param int $postID
	 * @param array $opt delibera configs
	 * @param bool $alterar has been altered
	 */
	public function publishPauta($postID, $opt)
	{
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
	
	/**
	 * Validate topic required data 
	 * @param array $errors erros report array
	 * @param array $opt Delibera configs
	 * @param bool $autosave is autosave?
	 * @return array erros report array append if needed
	 */
	public function checkPostData($errors, $opt, $autosave)
	{
		$value = $_POST['prazo_validacao'];
		$valida = delibera_tratar_data($value);
		if(!$autosave && (empty($value) ||  $valida === false || $valida < 1) )
		{
			$errors[] = __("É necessário definir corretamente o prazo de validação", "delibera");
		}
		
		$value = (int)$_POST['min_validacoes'];
		$valida = is_int($value) && $value > 0;
		if(!$autosave && ($valida === false))
		{
			$errors[] = __("É necessário definir corretamente o número mínimo de validações", "delibera");
		}
		return $errors;
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
	
	/**
	 * Save topic metadata
	 * @param array $events_meta
	 * @param array $opt Delibera configs
	 * 
	 * @return array events_meta to be save on the topic
	 */
	public function savePostMetas($events_meta, $opt, $post_id = false)
	{
		if( // Se tem validação, tem que ter o prazo
			(array_key_exists('prazo_validacao', $_POST) && array_key_exists('min_validacoes', $_POST) )
		)
		{
			$events_meta['prazo_validacao'] = sanitize_text_field($_POST['prazo_validacao']);
			$events_meta['min_validacoes'] = sanitize_text_field($_POST['min_validacoes']);
		}
		
		return $events_meta;
	}
	
	/**
	 * Treat postback of frotend topic
	 * @param array $opt Delibera configs
	 */
	public function createPautaAtFront($opt)
	{
		$_POST['prazo_validacao'] = $this->generateDeadline($opt);
		$_POST['min_validacoes'] = $opt['minimo_validacao'];
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::deadline()
	 */
	public static function deadline($args)
	{
		$situacao = delibera_get_situacao($args['post_ID']);
		if($situacao->slug == 'validacao')
		{
			self::marcarNaovalidada($args['post_ID']);
		}
	}
	
	/**
	 * No min validation recived
	 * @param int $postID
	 */
	public function marcarNaovalidada($postID)
	{
		wp_set_object_terms($postID, 'naovalidada', 'situacao', false);
		if(has_action('delibera_pauta_recusada'))
		{
			do_action('delibera_pauta_recusada', $postID);
		}
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::getCommentListLabel()
	 */
	public function getCommentListLabel()
	{
		return __('Validação da Pauta', 'delibera');
	}
	
}
$DeliberaValidation = new \Delibera\Modules\Validation();


