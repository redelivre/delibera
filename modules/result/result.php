<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class Result extends \Delibera\Modules\ModuleBase
{
	/**
	 *
	 * @var array List of of topic status
	 */
	public $situacao = array('comresolucao');
	
	/**
	 *
	 * @var array list of module flows
	 */
	protected $flows = array('comresolucao');
	
	/**
	 *
	 * @var String Name of module deadline metadata
	 */
	protected $prazo_meta = '';
	
	/**
	 *
	 * @var array List of pair shotcode name => method
	 */
	protected $shortcodes = array('delibera_lista_de_resolucoes' => 'replaceResolucoes' );
	
	/**
	 * Display priority
	 * @var int
	 */
	public $priority = 5;
	
	/**
	 * Register Tax for the module
	 */
	public function registerTax()
	{
		if(term_exists('comresolucao', 'situacao', null) == false)
		{
			delibera_insert_term('Resolução', 'situacao', array(
					'description'=> 'Pauta com resoluções aprovadas',
					'slug' => 'comresolucao',
				),
				array(
					'qtrans_term_pt' => 'Resolução',
					'qtrans_term_en' => 'Resolution',
					'qtrans_term_es' => 'Resolución',
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
		wp_set_object_terms($post_id, 'comresolucao', 'situacao', false);
	}

	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		return $rows;
	}
	
	/**
	 * Label to apply to button
	 * @param unknown $situation
	 */
	public function situationButtonText($situation)
	{
		if($situation == 'comresolucao')
		{
			return '';//__('', 'delibera');
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
		//No deadline
		return false;
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
		
	}
	
	/**
	 * When the topic is published
	 * @param int $postID
	 * @param array $opt delibera configs
	 * @param bool $alterar has been altered
	 */
	public function publishPauta($postID, $opt)
	{
		
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
		
		return $errors;
	}
	
	/**
	 *
	 * Retorna pautas já resolvidas
	 * @param array $filtro
	 */
	public static function getResolucoes($filtro = array())
	{
		return self::getPautas($filtro);
	}
	
	/**
	 * Shortcut for list of Results
	 * @param array $matches regext results
	 * @return string
	 */
	public function replaceResolucoes($matches)
	{
		$temp = explode(',', $matches[1]); // configurações da shorttag
	    $count = count($temp);
	
	    $param = array(); // TODO Tratar Parametros
	
	    $html = $this->getResolucoes($param);
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
		return $events_meta;
	}
	
	/**
	 * Treat postback of frotend topic
	 * @param array $opt Delibera configs
	 */
	public function createPautaAtFront($opt)
	{
		
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::deadline()
	 */
	public static function deadline($args)
	{
		$post_id = $args['post_ID'];
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::getCommentListLabel()
	 */
	public function getCommentListLabel()
	{
		return __('Resolução da Pauta', 'delibera');
	}
	
}
$DeliberaResult = new \Delibera\Modules\Result();


