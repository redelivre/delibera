<?php
/**
 * Manange topic flow
 */

// PHP 5.3 and later:
namespace Delibera;

class DeliberaFlow
{
	public function __construct()
	{
		add_filter('delibera_get_main_config', array($this, 'getMainConfig'));
		add_filter('delivera_config_page_rows', array($this, 'configPageRows'), 10, 2);
		add_filter('delibera-pre-main-config-save', array($this, 'preMainConfigSave'));
		add_action('delibera_topic_meta', array($this, 'topicMeta'), 10, 5);
		add_action('delibera_publish_pauta', array($this, 'publishPauta'), 10, 3);
		
	}
	
	/**
	 * Append configurations 
	 * @param array $opts
	 */
	public function getMainConfig($opts)
	{
		$opts['flow'] = array('validacao', 'discussao', 'elegerelator', 'relatoria', 'emvotacao', 'comresolucao');
		return $opts;
	}
	
	/**
	 * Array to show on config page
	 * @param array $rows
	 */
	public function configPageRows($rows, $opt)
	{
		$rows[] = array(
				"id" => "flow",
				"label" => __('Fluxo padrão de uma pauta?', 'delibera'),
				"content" => '<input type="text" name="flow" id="flow" value="'.implode(',', array_map("htmlspecialchars", $opt['flow']) ).'"/>'
		);
		return $rows;
	}
	
	/**
	 * Filter main config option before save
	 * @param unknown $opts
	 */
	public function preMainConfigSave($opts)
	{
		if(array_key_exists('flow', $opts) && !is_array($opts['flow']))
		{
			$opts['flow'] = array('validacao', 'discussao', 'elegerelator', 'relatoria', 'emvotacao', 'comresolucao');//explode(',', trim($opts['flow']));
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
		$flow = implode(',', array_map("htmlspecialchars", $options_plugin_delibera['flow']) );
		?>
			<p>
				<label for="flow" class="label_flow"><?php _e('Fluxo da Pauta','delibera'); ?>:</label>
				<input <?php echo $disable_edicao ?> id="flow" name="flow" class="flow widefat" value="<?php echo $flow; ?>"/>
			</p>
		<?php
	}
	
	public function publishPauta($postID, $opt, $alterar)
	{
		$prazo_validacao = get_post_meta($postID, 'prazo_validacao', true);
		$prazo_discussao =  get_post_meta($postID, 'prazo_discussao', true);
		$prazo_relatoria =  get_post_meta($postID, 'prazo_relatoria', true);
		$prazo_eleicao_relator =  get_post_meta($postID, 'prazo_eleicao_relator', true);
		$prazo_votacao =  get_post_meta($postID, 'prazo_votacao', true);
		
		
		delibera_criar_agenda(
			$post->ID,
			$prazo_validacao,
			$prazo_discussao,
			$prazo_votacao,
			$opt['relatoria'] == 'S' ? $prazo_relatoria : false,
			$opt['relatoria'] == 'S' && $opt['eleicao_relator'] == 'S' ? $prazo_eleicao_relator : false
		);
		// discussao
		/*else
		{
			if (! $alterar)
			{
				wp_set_object_terms ( $post->ID, 'discussao', 'situacao', false );
			}
			delibera_criar_agenda ( $post->ID, false, $prazo_discussao, $prazo_votacao, $opt ['relatoria'] == 'S' ? $prazo_relatoria : false, $opt ['relatoria'] == 'S' && $opt ['eleicao_relator'] == 'S' ? $prazo_eleicao_relator : false );
		}*/
	}
	
}


$DeliberaFlow = new DeliberaFlow();
