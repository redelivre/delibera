<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

abstract class ModuleBase
{

	/**
	 * 
	 * @var array List of of topic status
	 */
	public $situacao = array();
	
	/**
	 *
	 * @var array list of module flows
	 */
	protected $flows = array();
	
	/**
	 * 
	 * @var string Name of module deadline metadata
	 */
	protected $prazo_meta = 'prazo';
	
	/**
	 * 
	 * @var array List of pair shotcode name => method
	 */
	protected $shortcodes = array();
	
	/**
	 * Config days to make new deadline
	 * @var array
	 */
	protected $days = array();
	
	/**
	 * Display priority
	 * @var int
	 */
	public $priority = 0;
	
	public function __construct()
	{
		add_filter('delibera_register_flow_module', array($this, 'registerFlowModule'), $this->priority);
		add_action('delibera_situacao_register', array($this, 'registerTax'));
		add_filter('delibera_get_config', array($this, 'getMainConfig'));
		//add_filter('delivera_config_page_rows', array($this, 'configPageRows'), 10, 2);
		add_filter('delibera_situation_button_text', array($this, 'situationButtonText'));
		//add_action('delibera_topic_meta', array($this, 'topicMeta'), 10, 5);
		add_action('delibera_publish_pauta', array($this, 'publishPauta'), 10, 2);
		//add_filter('delibera_check_post_data', array($this, 'checkPostData'), 10, 3);
		//add_filter('delibera_save_post_metas', array($this, 'savePostMetas'), 10, 2);
		add_action('delibera_create_pauta_frontend', array($this, 'createPautaAtFront'));
		
		foreach ($this->shortcodes as $name => $function)
		{
			add_shortcode( $name,  array( $this, $function ));
		}
	}
	
	/**
	 * Register situacao objects for flow treat
	 * @param array $modules
	 */
	public function registerFlowModule($modules)
	{
		foreach ($this->flows as $situacao)
		{
			$modules[$situacao] = $this;
		}
		return $modules;
	}
	
	/**
	 * Register Tax for the module
	 */
	abstract public function registerTax();
	
	/**
	 * Initial situation on module begins
	 * @param int $post_id
	 */
	abstract public function initModule($post_id);
	
	/**
	 * Append configurations
	 * @param array $opts
	 */
	abstract public function getMainConfig($opts);
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	abstract public function configPageRows($rows, $opt);
	
	/**
	 * Label to apply to button
	 * @param unknown $situation
	 */
	abstract public function situationButtonText($situation);
	
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
	abstract public function topicMeta($post, $custom, $options_plugin_delibera, $situacao, $disable_edicao);
	
	/**
	 * When the topic is published
	 * @param int $postID
	 * @param array $opt delibera configs
	 */
	abstract public function publishPauta($postID, $opt);
	
	/**
	 * Validate topic required data
	 * @param array $errors erros report array
	 * @param array $opt Delibera configs
	 * @param bool $autosave is autosave?
	 * @return array erros report array append if needed
	 */
	abstract public function checkPostData($errors, $opt, $autosave);
	
	/**
	 * Save topic metadata
	 * @param array $events_meta
	 * @param array $opt Delibera configs
	 *
	 * @return array events_meta to be save on the topic
	 */
	abstract public function savePostMetas($events_meta, $opt, $post_id = false);
	
	/**
	 * Treat postback of frotend topic
	 * @param array $opt Delibera configs
	 */
	abstract public function createPautaAtFront($opt);
	
	/**
	 * Generate deadline date
	 * @param array $options_plugin_delibera Delibera configs
	 */
	abstract public function generateDeadline($options_plugin_delibera);
	
	/**
	 * Return this module deadline for the current post
	 * @param int $post_id
	 * @return mixed|string deadline date
	 */
	public function getDeadline($post_id = false)
	{
		if( empty($this->prazo_meta) ) return -1; // Do not have prazo
		
		if($post_id == false)
		{
			$post_id = get_the_ID();
		}
		if(is_array($this->prazo_meta))
		{
			$situacao = delibera_get_situacao($post_id);
			$situacao = $situacao->slug;
			
			return !empty($situacao) && array_key_exists($situacao, $this->prazo_meta) ? get_post_meta($post_id, $this->prazo_meta[$situacao], true) : $this->generateDeadline(delibera_get_config());
		}
		$deadline = get_post_meta($post_id, $this->prazo_meta, true);
		
		if(empty($deadline))
		{
			return $this->generateDeadline(delibera_get_config());
		}
		
		return $deadline;
	}
	
	/**
	 *
	 * Retorn topic at module situation
	 * @param array $filtro
	 */
	public static function getPautas($filtro = array())
	{
		return delibera_get_pautas_em($filtro, $this->situacao);
	}
	
	/**
	 * Trigger module deadline event
	 * @param array $args in form: array('post_id' => $post_id, 'prazo' => $prazo)
	 */
	abstract public static function deadline($args);
	
	/**
	 * Create new deadline events calendar
	 * @param int $post_id
	 * @param int $appendDays number of day to append or false to get config option default
	 */
	public function newDeadline($post_id, $appendDays = 0)
	{
		if(get_post_status($post_id) == 'publish')
		{
			if($appendDays == false)
			{
				$opts = delibera_get_config();
				$appendDays = $opts['dias_novo_prazo'];
			}
			$prazos = $this->prazo_meta;
			if(is_string($this->prazo_meta))
			{
				$prazos = array($this->prazo_meta);
			}
			foreach ($prazos as $prazo)
			{
				$prazo_date = get_post_meta($post_id, $prazo, true);
				if( ! empty($prazo_date) )
				{
					if($appendDays > 0)
					{
						$dateTime = \DateTime::createFromFormat('d/m/Y', $prazo_date);
						$dateTime->add(new \DateInterval('P'.$appendDays.'D'));
						$prazo_date = $dateTime->format('d/m/Y');
						update_post_meta($post_id, $prazo, $prazo_date);
					}
					
					\Delibera\Cron::del($post_id, array(get_class($this), 'deadline'));
					\Delibera\Cron::del($post_id, 'delibera_notificar_fim_prazo');
					
					$cron = get_option('delibera-cron');
					
					\Delibera\Cron::add(
						delibera_tratar_data($prazo_date),
						array(get_class($this), 'deadline'),
						array(
							'post_ID' => $post_id,
							'prazo' => $prazo_date
						)
					);
					\Delibera\Cron::add(
						strtotime("-1 day", delibera_tratar_data($prazo_date)),
						'delibera_notificar_fim_prazo',
						array(
							'post_ID' => $post_id,
							$prazo => $prazo_date
						)
					);
				}
				else 
				{
					/*$msn = "empty date on $post_id: ".print_r($this, true)."Dates: ".print_r($prazos, true);
					throw new \Exception($msn);*/
					// empty date! Meaning: no activated deadline, then do nothing
				}
			}
		}
	}
	
	public function getDeadlineDays()
	{
		$days = 0;
		$opt = delibera_get_config();
		foreach ($this->days as $day)
		{
			if(array_key_exists($day, $opt))
			{
				$days += $opt[$day];
			}
		}
		return $days;
	}
	
	/**
	 * Return comment list Title
	 */
	abstract public function getCommentListLabel();
	
}