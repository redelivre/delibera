<?php

// PHP 5.3 and later:
namespace Delibera\Modules;

class Vote extends \Delibera\Modules\ModuleBase
{
	
	/**
	 * List of of topic status
	 * @var array
	 */
	public $situacao = array('emvotacao');
	
	/**
	 *
	 * @var array list of module flows
	 */
	protected $flows = array('emvotacao');
	
	/**
	 * Name of module deadline metadata
	 * @var String
	 */
	protected $prazo_meta = 'prazo_votacao';
	
	/**
	 * Config days to make new deadline
	 * @var array
	 */
	protected $days = array('dias_votacao');
	
	/**
	 * Display priority
	 * @var int
	 */
	public $priority = 4;
	
	public function __construct()
	{
		parent::__construct();
		add_action( 'admin_print_scripts', array($this, 'adminScripts') );
		add_action( 'wp_enqueue_scripts', array($this, 'js') );
		add_filter('delibera_unfilter_duplicate', array($this, 'unfilterDuplicate'));
	}
	
	/**
	 * Register Tax for the module
	 */
	public function registerTax()
	{
		if(term_exists('emvotacao', 'situacao', null) == false)
		{
			delibera_insert_term('Regime de Votação', 'situacao', array(
					'description'=> 'Pauta com encaminhamentos em Votacao',
					'slug' => 'emvotacao',
				),
				array(
					'qtrans_term_pt' => 'Regime de Votação',
					'qtrans_term_en' => 'Voting',
					'qtrans_term_es' => 'Sistema de Votación',
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
		wp_set_object_terms($post_id, 'emvotacao', 'situacao', false);
		$this->newDeadline($post_id);
	}
	
	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['dias_votacao'] = '5';
		$opts['tipo_votacao'] = 'checkbox';
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		$rows[] = array(
			"id" => "dias_votacao",
			"label" => __('Dias para votação de encaminhamentos:', 'delibera'),
			"content" => '<input type="text" name="dias_votacao" id="dias_votacao" value="'.htmlspecialchars_decode($opt['dias_votacao']).'" autocomplete="off" />'
		);
		$id = 'tipo_votacao';
		$value = htmlspecialchars_decode($opt[$id]);
		$rows[] = array(
			"id" => $id,
			"label" => __('Tipo da votação:', 'delibera'),
			//"content" => '<input type="text" name="dias_votacao" id="dias_votacao" value="'.htmlspecialchars_decode($opt['dias_votacao']).'" autocomplete="off" />'
			"content" => '
				<select name="'.$id.'" id="'.$id.'" autocomplete="off" >
					<option value="checkbox" '.($value == 'checkbox' ? 'selected="selected"' : '').'>'.__('Checkbox', 'delibera').'</option>
					<option value="radio" '.($value == 'radio' ? 'selected="selected"' : '').'>'.__('Radio Buttons', 'delibera').'</option>
					<!-- <option value="dropdown" '.($value == 'dropdown' ? 'selected="selected"' : '').'>'.__('Dropdown', 'delibera').'</option> -->
				</select>
			'
		);
		return $rows;
	}
	
	/**
	 * Label to apply to button
	 * @param unknown $situation
	 */
	public function situationButtonText($situation)
	{
		if($situation == 'emvotacao')
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
		$dias_votacao = intval(htmlentities($options_plugin_delibera['dias_votacao']));
		
		$prazo_votacao_sugerido = strtotime("+$dias_votacao days", delibera_tratar_data(\Delibera\Flow::getLastDeadline('emvotacao')));
		
		return date('d/m/Y', $prazo_votacao_sugerido);
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
		/*global $DeliberaFlow;
		$flow = $DeliberaFlow->get($post->ID);
		$discussao = array_search('discussao', $haystack)*/
		
		$prazo_votacao = $this->generateDeadline($options_plugin_delibera);
		$tipo_votacao = $options_plugin_delibera['tipo_votacao'];
		
		if(!($post->post_status == 'draft' ||
			$post->post_status == 'auto-draft' ||
			$post->post_status == 'pending'))
		{
			
			$prazo_votacao = array_key_exists("prazo_votacao", $custom) ?  $custom["prazo_votacao"][0] : $prazo_votacao;
			$tipo_votacao = array_key_exists("tipo_votacao", $custom) ?  $custom["tipo_votacao"][0] : $tipo_votacao;
		}
		
		?>
		<p>
			<label class="label_prazo_votacao"><?php _e('Prazo para Votações','delibera') ?>:</label>
			<input <?php echo $disable_edicao ?> name="prazo_votacao" class="prazo_votacao widefat hasdatepicker" value="<?php echo $prazo_votacao; ?>"/>
		</p>
		<p>
			<label class="label_tipo_votacao"><?php _e('Tipo de Votação','delibera') ?>:</label>
			<select name="tipo_votacao" id="tipo_votacao" class="tipo_votacao widefat" autocomplete="off" >
				<option value="checkbox" <?php echo $tipo_votacao == 'checkbox' ? 'selected="selected"' : ''; ?>><?php _e('Checkbox', 'delibera'); ?></option>
				<option value="radio" <?php echo $tipo_votacao == 'radio' ? 'selected="selected"' : ''; ?>><?php _e('Radio Buttons', 'delibera'); ?></option>
				<!-- <option value="dropdown" <?php echo $tipo_votacao == 'dropdown' ? 'selected="selected"' : ''; ?>><?php _e('Dropdown', 'delibera'); ?></option> -->
			</select>
		</p>
		
		<div class="delibera_comment_list_panel" style="display: none;">
			<label class="label_opcoes_votacao"><?php _e('Opções de votação','delibera') ?>:</label>
			<textarea class="delibera_comment_input_list" ></textarea><a class="btn_delibera_comment_createList" class="button" onclick="delibera_add_comment_input(this);return false;" href="#delibera_comment_input_list"><?php _e('Adicionar opção','delibera') ?></a>
			<ul class="delibera_comment_add_current">
			<?php
				foreach (delibera_get_comments_encaminhamentos($post->ID) as $comment)
				{?>
					<p><textarea id="vote-comment-id-<?php echo $comment->comment_ID; ?>" name="delibera_comment_add_list[]"><?php echo get_comment_text($comment->comment_ID); ?></textarea><a href="#" class="delibera_comment_input_bt_remove delibera-icon-cancel"></a></p><?php	
				}
			?>
			</ul>
		</div>
		
		<?php
	}
	
	public function adminScripts()
	{
		$screen = get_current_screen();
		$screenid = $screen->id;
		if(strpos($screenid, 'page_delibera') !== false || $screenid == 'pauta' )
		{
			wp_enqueue_script('delibera-module-vote-admin',WP_PLUGIN_URL.'/delibera/modules/vote/assets/js/admin-vote.js', array('jquery'));
		}
	}
	
	public function js()
	{
		wp_enqueue_script('delibera-module-vote',WP_PLUGIN_URL.'/delibera/modules/vote/assets/js/vote.js', array('jquery'));
	}
	
	public function publishPauta($postID, $opt)
	{
		$events_meta = array();
		$events_meta['delibera_numero_comments_votos'] = 0;
		
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
	
	function checkPostData($errors, $opt, $autosave)
	{
		$value = $_POST['prazo_votacao'];
		$valida = delibera_tratar_data($value);
		if(!$autosave && (empty($value) ||  $valida === false || $valida < 1) )
		{
			$errors[] = __("É necessário definir corretamente o prazo para votação", "delibera");
		}
		return $errors;
	}
	
	/**
	 *
	 * Retorna pautas em Validação
	 * @param array $filtro
	 */
	public static function getEmvotacao($filtro = array())
	{
		return self::getPautas($filtro);
	}
	
	public function savePostMetas($events_meta, $opt, $post_id = false)
	{
		if(array_key_exists('prazo_votacao', $_POST))
		{
			$events_meta['prazo_votacao'] = sanitize_text_field($_POST['prazo_votacao']);
		}
		if(array_key_exists('tipo_votacao', $_POST))
		{
			$events_meta['tipo_votacao'] = sanitize_text_field($_POST['tipo_votacao']);
		}
		
		global $post, $current_user;
		if(!is_object($post))
		{
			if($post_id)
			{
				$post = get_post($post_id);
			}
			elseif(array_key_exists('post_id', $_POST ))
			{
				$post = get_post($_POST['post_id']);
			}
		}
		
		if(array_key_exists('delibera_comment_add_list', $_POST) )
		{
			if(is_array($_POST['delibera_comment_add_list']))
			{
				get_currentuserinfo();
				
				$all_saved_vote_options = delibera_get_comments_encaminhamentos($post->ID);
				if(!is_array($all_saved_vote_options)) $all_saved_vote_options = array();
				$all_saved_vote_options = array_object_value_recursive('comment_ID', $all_saved_vote_options);
				
				foreach ($_POST['delibera_comment_add_list'] as $vote_option)
				{
					$vote_option = explode(',', $vote_option, 2);
					if(count($vote_option) == 1) $vote_option = array('', $vote_option[0]);
					if($vote_option[0] == '')
					{
						$commentdata = array(
								'comment_post_ID' => (int) $post->ID, 
								'comment_author' => $current_user->dispay_name, 
								'comment_author_email' => $current_user->user_mail, 
								'comment_author_url' => '',
								'comment_content' => wp_kses_data( (string) $vote_option[1]),
								'comment_type' => '',
								'comment_parent' => 0,
								'user_id' => (int) $current_user->ID,
								'comment_author_IP' => preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] ),
								'comment_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '',
								'comment_date'     => current_time('mysql'),
								'comment_date_gmt' => current_time('mysql', 1),
								'comment_approved' => 1
						);
						$commentdata = wp_filter_comment($commentdata);
						
						
						//Insert new comment and get the comment ID
						$comment_id = wp_insert_comment($commentdata) ;
						if($comment_id)
						{
							add_comment_meta($comment_id, 'delibera_comment_tipo', 'encaminhamento', true);
							$nencaminhamentos = get_post_meta($comment_id, 'delibera_numero_comments_encaminhamentos', true);
							$nencaminhamentos++;
							update_post_meta($comment_id, 'delibera_numero_comments_encaminhamentos', $nencaminhamentos);
						}
					}
					else 
					{
						$comment_tmp_id = substr($vote_option[0], strlen('vote-comment-id-'));
						$comment = get_comment($comment_tmp_id, ARRAY_A);
						if(is_array($comment))
						{
							$comment['comment_content'] = wp_kses_data( (string) $vote_option[1]);
							wp_update_comment($comment);
							$all_saved_vote_options = array_diff($all_saved_vote_options, array($comment_tmp_id));	
						}
						else 
						{
							//TODO parse comment error
						}
					}
				}
				foreach ($all_saved_vote_options as $comment_delete_id)
				{
					wp_delete_comment($comment_delete_id);
				}
				
			}
		}
		return $events_meta;
	}
	
	public function createPautaAtFront($opt)
	{
		$data_externa = trim($opt['data_fixa_nova_pauta_externa']);
		if ( !empty($data_externa) && strlen($data_externa) == 10) {
			$prazo_votacao = \DateTime::createFromFormat('d/m/Y', $data_externa);
			$_POST['prazo_votacao'] = $prazo_votacao->format('d/m/Y');
		} else {
			$_POST['prazo_votacao'] = $this->generateDeadline($opt);
		}
	}
	
	/**
	 *
	 * Faz a apuração dos votos e toma as devidas ações:
	 *    Empate: Mais prazo;
	 *    Vencedor: Marco com resolucao e marca o encaminhamento.
	 * @param interger $postID
	 * @param array $votos
	 */
	function computaVotos($postID, $votos = null)
	{
		if(is_null($votos)) // Ocorre no fim do prazo de votação
		{
			$votos = delibera_get_comments_votacoes($postID);
		}
		$encaminhamentos = delibera_get_comments_encaminhamentos($postID);
		$encaminhamentos_votos = array();
		foreach ($encaminhamentos as $encaminhamento)
		{
			$encaminhamentos_votos[$encaminhamento->comment_ID] = 0;
		}
	
		foreach ($votos as $voto_comment)
		{
			$voto = get_comment_meta($voto_comment->comment_ID, 'delibera_votos', true);
			foreach ($voto as $voto_para)
			{
				if (array_key_exists($voto_para, $encaminhamentos_votos))
				{
					$encaminhamentos_votos[$voto_para]++;
				} else {
					$encaminhamentos_votos[$voto_para] = 1;
				}
			}
		}
		$maisvotado = array(-1, -1);
		$iguais = array();
	
		foreach ($encaminhamentos_votos as $encaminhamentos_voto_key => $encaminhamentos_voto_valor)
		{
			if($encaminhamentos_voto_valor > $maisvotado[1])
			{
				$maisvotado[0] = $encaminhamentos_voto_key;
				$maisvotado[1] = $encaminhamentos_voto_valor;
				$iguais = array();
			}
			elseif($encaminhamentos_voto_valor == $maisvotado[1])
			{
				$iguais[] = $encaminhamentos_voto_key;
			}
			delete_comment_meta($encaminhamentos_voto_key, 'delibera_comment_numero_votos');
			add_comment_meta($encaminhamentos_voto_key, 'delibera_comment_numero_votos', $encaminhamentos_voto_valor, true);
		}
	
		// nao finaliza a votacao caso haja um empate, exceto quando o administrador clicar no botão "Forçar fim do prazo"
		if(count($iguais) > 0 && !(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delibera_forca_fim_prazo_action')) // Empato
		{
			$this->newDeadline($postID, false);
		}
		else
		{
			//wp_set_object_terms($postID, 'comresolucao', 'situacao', false);
			\Delibera\Flow::next($postID);
			
			update_comment_meta($maisvotado[0], 'delibera_comment_tipo', 'resolucao');
			add_post_meta($postID, 'data_resolucao', date('d/m/Y H:i:s'), true);
			////delibera_notificar_situacao($postID);
			if(has_action('votacao_concluida'))
			{
				do_action('votacao_concluida', $post);
			}
		}
	}
	
	/**
	 *
	 * Verifica se o número de votos é igual ao número de representantes para deflagar fim da votação
	 * @param integer $postID
	 */
	public function validaVotos($postID)
	{
		global $wp_roles,$wpdb;
		$users_count = 0;
		foreach ($wp_roles->roles as $nome => $role)
		{
			if(is_array($role['capabilities']) && array_key_exists('votar', $role['capabilities']) && $role['capabilities']['votar'] == 1 ? "SSSSSim" : "NNNnnnnnnnao")
			{
				$result = $wpdb->get_results("SELECT count(*) as n FROM $wpdb->usermeta WHERE meta_key = 'wp_capabilities' AND meta_value LIKE '%$nome%' ");
				$users_count += $result[0]->n;
			}
		}
	
		$votos = delibera_get_comments_votacoes($postID);
	
		$votos_count = count($votos);
	
		if($votos_count >= $users_count)
		{
			$this->computaVotos($postID, $votos);
		}
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::deadline()
	 */
	public static function deadline($args)
	{
		$post_id = $args['post_ID'];
		$current = \Delibera\Flow::getCurrentModule($post_id);
		$current->computaVotos($post_id);
	}
	
	public function unfilterDuplicate($tipos)
	{
		$tipos[] = 'voto';
		return $tipos;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Delibera\Modules\ModuleBase::getCommentListLabel()
	 */
	public function getCommentListLabel()
	{
		return __('Votação da Pauta', 'delibera');
	}
	
}
$DeliberaVote = new \Delibera\Modules\Vote();


