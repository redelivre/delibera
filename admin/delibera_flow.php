<?php
/**
 * Manange topic flow
 */

// PHP 5.3 and later:
namespace Delibera;

class Flow
{
	
	protected $flow = array();
	protected $deadlines = array();
	
	public function __construct()
	{
		add_filter('delibera_get_main_config', array($this, 'getMainConfig'));
		//add_filter('delivera_config_page_rows', array($this, 'configPageRows'), 10, 2);
		add_filter('delibera-pre-main-config-save', array($this, 'preMainConfigSave'));
		add_action('delibera_topic_meta', array($this, 'topicMeta'), 10, 5);
		add_filter('delibera_save_post_metas', array($this, 'savePostMetas'), 1, 3);
		add_action('delibera_publish_pauta', array($this, 'publishPauta'), 10, 2);
		add_filter('delibera_flow_list', array($this, 'filterFlowList'));
		add_filter('delibera_check_post_data', array($this, 'checkPostData'), 1000, 3);
		add_action('delibera_save_post', array($this, 'savePost'), 1000, 3);
		//if(is_super_admin()) // TODO load after init
		{
			add_action('delibera_menu_itens', array($this, 'addMenu'));
		}
		
		add_action( 'admin_print_scripts', array($this, 'adminScripts') );
		
		add_action('wp_ajax_delibera_save_flow', array($this, 'saveFlowCallback'));
		
	}
	
	/**
	 * Append defaults configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['delibera_flow'] = array('validacao', 'discussao', 'relatoria', 'emvotacao', 'comresolucao');
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		$rows[] = array(
				"id" => "delibera_flow",
				"label" => __('Fluxo padrão de uma pauta?', 'delibera'),
				"content" => '<input type="text" name="delibera_flow" id="delibera_flow" value="'.implode(',', array_map("htmlspecialchars", $opt['delibera_flow']) ).'"/>'
		);
		return $rows;
	}
	
	/**
	 * Filter main config option before save
	 * @param unknown $opts
	 */
	public function preMainConfigSave($opts)
	{
		$save_opts = delibera_get_config();
		
		if(array_key_exists('delibera_flow', $opts) && is_string($opts['delibera_flow']) && strlen($opts['delibera_flow']) > 1)
		{
			$opts['delibera_flow'] = explode(',', trim($opts['delibera_flow']));
		}
		
		if( empty($opts['delibera_flow']) || (is_string($opts['delibera_flow']) && strlen($opts['delibera_flow']) < 2 ) )
		{
			if(empty($save_opts['delibera_flow']))
			{
				$opts = $this->getMainConfig($opts);
			}
			else 
			{
				$opts['delibera_flow'] = $save_opts['delibera_flow'];
			}
		}
		return $opts;
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
	 */
	public function topicMeta($post, $custom, $options_plugin_delibera, $situacao, $disable_edicao)
	{
		$flow = implode(',', array_map("htmlspecialchars", $this->get($post->ID)) ); 
		/*?>
			<p>
				<label for="delibera_flow" class="label_flow"><?php _e('Fluxo da Pauta','delibera'); ?>:</label>
				<input <?php echo $disable_edicao ?> id="delibera_flow" name="delibera_flow" class="delibera_flow widefat" value="<?php echo $flow; ?>"/>
			</p>
		<?php*/
		$this->confPage();
	}
	
	/**
	 * get topic flow sequence
	 * @param string $post_id
	 */
	public function get($post_id = false)
	{
		$options_plugin_delibera = delibera_get_config();
		
		$default_flow = isset($options_plugin_delibera['delibera_flow']) ? $options_plugin_delibera['delibera_flow'] : array();
		$default_flow = apply_filters('delibera_flow_list', $default_flow);
		
		if($post_id == false)
		{
			$post_id = get_the_ID();
			if($post_id == false)
			{
				return $default_flow;
			}
		}
		
		if(array_key_exists($post_id, $this->flow)) return $this->flow[$post_id];
		
		$flow = get_post_meta($post_id, 'delibera_flow', true);
		if(is_array($flow) && count($flow) > 0)
		{
			$flow = apply_filters('delibera_flow_list', $flow);
			$this->flow[$post_id] = $flow;
			return $flow;
		}
		else 
		{
			$this->flow[$post_id] = $default_flow;
			return $default_flow;
		}
	}
	
	/**
	 * List of Modules and each situation for get information about the module, like deadline
	 * 
	 * @return \Delibera\Modules\ModuleBase[]
	 */
	public function getFlowModules()
	{
		$modules = array();
		/* Modules need to register to make part of flow
		 * Form: $modules['situacao'] = ModuleObject;
		 */
		$modules = apply_filters('delibera_register_flow_module', $modules);
		return $modules;
	}
	
	/**
	 * Get the last deadline before current module
	 * @param string $situacao
	 * @param int $post_id
	 * 
	 * @return string date (dd/mm/YYYY)
	 */
	public static function getLastDeadline($situacao, $post_id = false)
	{
		global $DeliberaFlow;
		if(is_object($situacao))
		{
			$situacao = $situacao->slug;
		}
		
		if($post_id == false)
		{
			$post_id = get_the_ID();
		}
		$flow = $DeliberaFlow->get($post_id);
		$modules = $DeliberaFlow->getFlowModules();
		
		$now = array_search($situacao, $flow);
		if(($now - 1) >= 0 && array_key_exists($now - 1, $flow) && array_key_exists($flow[$now - 1], $modules) && method_exists($modules[$flow[$now - 1]], 'getDeadline'))
		{
			return $modules[$flow[$now - 1]]->getDeadline();
		}
		else 
		{
			return date('d/m/Y');
		}
	}
	
	/**
	 * Save post meta filter
	 * @param array $events_meta metas to save
	 * @param array $opt delibera config options
	 * 
	 * @return array return filtered $events_meta array
	 */
	public function savePostMetas($events_meta, $opt, $post_id)
	{
		if(array_key_exists('delibera_flow', $_POST) )
		{
			$flow = $_POST['delibera_flow'];
			
			if(is_string($flow))
			{
				$flow = explode(',', trim($_POST['delibera_flow']));
			}
			$events_meta['delibera_flow'] = $flow;
			
			$modules = $this->getFlowModules();
			foreach ($flow as $situacao)
			{
				if(array_key_exists($situacao, $modules))
				{
					$events_meta = $modules[$situacao]->savePostMetas($events_meta, $opt);
				}
			}
		}
		return $events_meta;
	}
	
	/**
	 * Create a new date triggers for current module
	 * @param int $post_id
	 * @param string $appendDays number of day to append or false to get config option default
	 */
	public function newDeadline($post_id, $appendDays = false)
	{
		$module = $this->getCurrentModule($post_id);
		$module->newDeadline($post_id, $appendDays);
	}
	
	/**
	 * Action when pauta is saved
	 * @param int $post_id
	 * @param \WP_Post $post
	 * @param array $opt delibera config options
	 */
	public function savePost($post_id, $post, $opt)
	{
		$this->newDeadline($post_id, 0);
	}
	
	/**
	 * When the topic is published
	 * @param int $postID
	 * @param array $opt delibera configs
	 * @param bool $alterar has been altered
	 */
	public function publishPauta($postID, $opt)
	{
		/**
		 * Update flow meta after publish because is before save metas  
		 */
		if(array_key_exists('delibera_flow', $_POST))
		{
			$flow = array();
			if(is_array($_POST['delibera_flow']))
			{
				$flow = array_map("strip_tags", $_POST['delibera_flow']);
			}
			else 
			{
				$flow = explode(',', trim(strip_tags($_POST['delibera_flow'])));
			}
			update_post_meta($postID, 'delibera_flow', $flow);
			//file_put_contents('/tmp/publish.log', date('Ymd H:i:s').$postID."\n", FILE_APPEND);
			self::reabrirPauta($postID, false);
		}
	}
	
	/**
	 * Return Current Flow Module
	 * @param int $post_id
	 * @return \Delibera\Modules\ModuleBase
	 */
	public static function getCurrentModule($post_id)
	{
		global $DeliberaFlow;
		
		$flow = $DeliberaFlow->get($post_id);
		$situacao = delibera_get_situacao($post_id);
		$current = array_search($situacao->slug, $flow);
		$modules = $DeliberaFlow->getFlowModules(); //TODO cache?
		
		if($current === false)
		{
			$situacoes = array();
			foreach ($modules as $module)
			{
				foreach ($module->situacao as $sit)
				{
					$situacoes[$sit] = $module;
				}
			}
			if (array_key_exists($situacao->slug, $situacoes))
			{
				return $situacoes[$situacao->slug];
			}
			$current = 0;
		}
		if(array_key_exists($current, $flow) && array_key_exists($flow[$current], $modules))
		{
			return $modules[$flow[$current]];
		}
		else 
		{
			return array_shift($modules);
		}
	}
	
	/**
	 * Go to the next module on flow
	 * @param string $post_id
	 */
	public static function next($post_id = false)
	{
		global $DeliberaFlow;
		
		$flow = $DeliberaFlow->get($post_id);
		$situacao = delibera_get_situacao($post_id);
		$current = array_search($situacao->slug, $flow);
		$modules = $DeliberaFlow->getFlowModules(); //TODO cache?
		
		if(array_key_exists($current+1, $flow))
		{
			$modules[$flow[$current+1]]->initModule($post_id);
		}
		else 
		{
			//TODO the end?
		}
	}
	
	/**
	 * Trigger module deadline
	 * @param int $post_id
	 */
	public static function forcarFimPrazo($post_id)
	{
		if(is_object($post_id)) $post_id = $post_id->ID;
		
		$current = \Delibera\Flow::getCurrentModule($post_id);
		\Delibera\Cron::del($post_id);
		call_user_func(array(get_class($current), 'deadline'), array('post_ID' => $post_id, 'prazo' => date('d/m/Y'), 'force' => true) );
	}
	
	/**
	 * Reopen finished topic
	 * @param int $postID
	 * @param bool $new_deadline_days if is true will add days to new dateline
	 */
	public static function reabrirPauta($postID, $new_deadline_days = false)
	{
		global $DeliberaFlow;
		$flow = $DeliberaFlow->get($postID);
		$modules = $DeliberaFlow->getFlowModules();
		$modules[$flow[0]]->initModule($postID);
		if($new_deadline_days) $modules[$flow[0]]->newDeadline($postID, false);
	}
	
	/**
	 * Check if module has bean remove or altered
	 * @param array $flows
	 * @return array
	 */
	public function filterFlowList($flow)
	{
		if(is_array($flow))
		{
			$modules = $this->getFlowModules();
			$flow = array_values(array_intersect($flow, array_keys($modules)));
			return $flow;
		}
		else 
		{
			return array();
		}
	}
	
	/**
	 * Return module deadline days for the current post (until 1 minute, we return 1)
	 * @param int $post_id
	 * @return mixed|string deadline date
	 */
	public static function getDeadlineDays($post_id = false, &$data = null)
	{
		$module = \Delibera\Flow::getCurrentModule($post_id);
	
		$deadline = $module->getDeadline($post_id);
		
		if($deadline == -1) return $deadline;
	
		$dateTimeNow = new \DateTime();
		$deadlineDate = \DateTime::createFromFormat('d/m/Y H:i:s', $deadline." 23:59:59");
		
		$diff = $dateTimeNow->diff($deadlineDate);
		if(!is_null($data))
		{
			$data = $deadlineDate->format('d/m/Y');
		}
		if($diff->d > 0)
		{
			return $diff->format('%r%a');
		}
		if($diff->d < 1 && ($diff->i || $diff->h || $diff->s)) 
		{
			return  1;
		}
		else 
		{
			return -1;
		}
		
	}
	
	public function addMenu($base_page)
	{
		add_submenu_page($base_page, __('Delibera Flow','delibera'),__('Delibera Flow','delibera'), 'manage_options', 'delibera-flow', array($this, 'confPage'));
	}
	
	public function listModulesConfigBoxes($post = null, $flow = false)
	{
		$is_post_meta = !is_null($post);
		
		/**
		 * Create Defaults value for topicMeta like in action TODO check if value is need after make this work
		 */
		$custom = array();
		$options_plugin_delibera = delibera_get_config();
		$situacao = "";
		$disable_edicao = false;
		
		if(is_null($post))
		{
			$post = new \WP_Post(new \stdClass());
		}
		else 
		{
			$custom = get_post_meta($post->ID);
			$situacao = delibera_get_situacao($post->ID);
		}
		
		$modules = $this->getFlowModules();
		
		if( $flow != false && is_array($flow) )
		{
			$allmodules = $modules;
			$modules = array();
			foreach ($flow as $step)
			{
				$modules[$step] = $allmodules[$step];
			}
		}
		
		foreach ($modules as $key => $module)
		{
			$situacao = get_term_by('slug', $key, 'situacao');
			?>
			<div class="dragbox <?php echo $situacao->slug.($flow === false ? '' : ' clone'); ?>" >
				<h2><?php echo $situacao->name; ?>
				  <span class="delete opIcons"> </span> 
				  <span class="maxmin opIcons"> </span> 
				</h2>
				<div class="dragbox-content" style="<?php echo ($is_post_meta && $flow === false) ? "display: none;" : ''; ?>" >
					<?php
					if($is_post_meta)
					{
						$module->topicMeta($post, $custom, $options_plugin_delibera, $situacao, $disable_edicao);
					}
					else 
					{
						$rows = array();
						$rows = $module->configPageRows($rows, $options_plugin_delibera);
						$table = delibera_form_table($rows);
						if(has_filter('delibera_config_form'))
						{
							$table = apply_filters('delibera_config_form', $table, $opt);
						}
						echo $table;
					}
					if(!$is_post_meta) // Use post save
					{?>
						<input type="button" class="dragbox-bt-save" value="<?php _e('Save', 'delibera'); ?>" />
					<?php
					}
					?>
				</div>
				<input type="hidden" class="delibera_flow_module_days" value="<?php echo $module->getDeadlineDays(); ?>" />
			</div>
			<?php
		}
	}
	
	/**
	 * Create a config page for manange flow and modules config
	 */
	public function confPage()
	{
		$post = get_post();
		$flow = $this->get();
		?>
		<div class="delibera-flow-panel <?php echo is_null($post) ? 'delibera-flow-panel-config' : 'delibera-flow-panel-post' ?>"><?php
			wp_nonce_field( 'delibera-flow-nonce', '_delibera-flow-nonce' );
			?>
			<input type="hidden" id="delibera-flow-postid" name="delibera-flow-postid" value="<?php the_ID(); ?>" />
			<input type="hidden" id="delibera_flow" name="delibera_flow" value="<?php echo implode(',', $flow); ?>" />
			<div class="delibera-flow-column" id="delibera-flow-column1">
			<?php 
				$this->listModulesConfigBoxes($post);
			?>
			</div>
			<input type="button" class="dragbox-bt-save" value="<?php _e('Save', 'delibera'); ?>" />
			<div class="delibera-flow-column" id="delibera-flow-column2" >
			<?php 
				$this->listModulesConfigBoxes($post, $flow);
			?>
			</div>
		</div>
		<?php
	}
	
	public function adminScripts()
	{
		$screen = get_current_screen();
		$screenid = $screen->id;
		if(strpos($screenid, 'page_delibera') !== false || $screenid == 'pauta' )
		{
			$post_id = get_the_ID();
			wp_enqueue_script('delibera-admin-flow', plugin_dir_url(__FILE__).'/js/flow.js', array( 'jquery-ui-core', 'jquery-ui-sortable'));
			$data = array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'post_id' => $post_id
			);
			
			wp_localize_script('delibera-admin-flow', 'delibera_admin_flow', $data);
			wp_enqueue_script('jquery-ui-datepicker-ptbr', DELIBERA_DIR_URL.'/js/jquery.ui.datepicker-pt-BR.js', array('jquery-ui-datepicker'));
			wp_enqueue_script('delibera-admin', plugin_dir_url(__FILE__).'/js/admin_scripts.js', array( 'jquery-ui-datepicker-ptbr'));
			
			wp_enqueue_style('delibera-admin-flow',plugin_dir_url(__FILE__).'/css/flow.css');
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
		if(array_key_exists('delibera_flow', $_POST) )
		{
			$flow = $_POST['delibera_flow'];
			if(is_string($flow))
			{
				$flow = explode(',', strip_tags($_POST['delibera_flow']));
			}
			$valida = is_array($flow) ? count($flow) : false;
			if(!$autosave && ($valida === false || $valida < 1))
			{
				$errors[] = __("É necessário definir corretamente o fluxo da pauta", "delibera");
			}
			else 
			{
				$modules = $this->getFlowModules();
				foreach ($flow as $situacao)
				{
					if(array_key_exists($situacao, $modules))
					{
						$errors = $modules[$situacao]->checkPostData($errors, $opt, false);
					}
				}
			}
		}
		else 
		{
			$errors[] = __("É necessário definir corretamente o fluxo da pauta", "delibera");
		}
		return $errors;
	}
	
	public function saveFlowCallback()
	{
		$flow = explode(',', strip_tags($_POST['delibera_flow']));
		$post_id = intval(strip_tags($_POST['post_id']));
		$opt = delibera_get_config();
		$all_errors = array();
		
		if($post_id > 0)
		{
			$modules = $this->getFlowModules();
			$events_meta = array();
			foreach ($flow as $situacao)
			{
				$errors = array();
				if(array_key_exists($situacao, $modules))
				{
					$errors = $modules[$situacao]->checkPostData($errors, $opt, false);
					if(count($errors) == 0)
					{
						$events_meta = $modules[$situacao]->savePostMetas($events_meta, $opt);
					}
					else 
					{
						$all_errors = array_merge($all_errors,$errors);
					}
				}
			}
			if(count($all_errors) > 0)
			{
				if ( defined( 'DOING_AJAX' ) ) {
					die(json_encode($all_errors));//TODO error notice and parser
				}
				wp_die( implode('<br/>', $errors) );
				
			}
			foreach ($events_meta as $key => $value) // Buscar dados
			{
				update_post_meta($post_id, $key, $value); // Atualiza
			}
		}
		else 
		{
			$rows = array();
			$rows = $this->configPageRows($rows, $opt);
			$modules = \Delibera\Flow::getFlowModules();
			foreach ($modules as $module)
			{
				$rows = $module->configPageRows($rows, $opt);
			}
			$oldFlow = implode(',', $opt['delibera_flow']);
			$configs_to_save = array_value_recursive('id', $rows);
			foreach ($configs_to_save as $option_name) //TODO better way to do this
			{
				if (isset($_POST[$option_name]))
				{
					$opt[$option_name] = htmlspecialchars($_POST[$option_name]);
				}
				else // checkbox maybe
				{
					$opt[$option_name] = "N";
				}
			}
			$opt['delibera_flow'] = $flow;
			if(! update_option('delibera-config', $opt) && $oldFlow != implode(',', $flow) )
			{
				$all_errors = array(__('can not update modules configs', 'delibera'));
			}
			
			if(count($all_errors) > 0)
			{
				die(json_encode($all_errors));//TODO error notice and parser
			}
		}
		die('ok');
	}
	
	/**
	 * return list of Deadlines of a post in format: array of [situacao] => [date]
	 * @param int $post_id
	 * @return array
	 */
	public static function getDeadlineDates($post_id)
	{
		global $DeliberaFlow;
	
		$dates = array();
		$flow = $DeliberaFlow->get($post_id);
		$modules = $DeliberaFlow->getFlowModules();
		foreach ($flow as $situacao)
		{
			if(array_key_exists($situacao, $modules))
			{
				$dates[$situacao] = $modules[$situacao]->getDeadline($post_id);
			}
		}
		return $dates;
	}
	
}

global $DeliberaFlow;
$DeliberaFlow = new \Delibera\Flow();
