<?php
class WidgetListaPropostas extends WP_Widget
{
	
	public function getDefaults()
	{
		return array(
			'title' => __( 'Delibera Lista de Propostas', 'delibera' ),
			'show_summary' => 1,
			'show_author' => 1,
			'show_date' => 1,
			'show_prazo' => 1,
			'show_comment_link' => 1,
			'show_situacao' => 1,
			'items' => 10,
			'situacao' => 'todas'
		);
	}
	
	public function __construct()
	{
		parent::__construct(
			'WidgetListaPropostas', // Base ID
			'Delibera Lista de Propostas', // Name
			array( 'description' => __( 'Listas as Propostas de Pauta do Delibera', 'delibera' ), ) // Args
		);
		add_action('wp_enqueue_scripts', array($this, 'styles'));
	}
	
	public function styles()
	{
		wp_register_style('WidgetListaPropostas', WP_PLUGIN_URL."/delibera/widgets/WidgetListaPropostas/WidgetListaPropostas.css");
		wp_enqueue_style('WidgetListaPropostas');
	}
	
	public function widget( $args, $instance )
	{
		$params = array_merge($this->getDefaults(), $instance);
		
		if($params['situacao'] == 'todas')
		{
			$terms = get_terms('situacao');
			$params['situacao'] = array();
			foreach ($terms as $term)
			{
				$params['situacao'][] = $term->slug;
			}
		}
		
		extract($params);
		
		/* @var $wp_posts WP_Query */ 
		$wp_posts = delibera_get_pautas_em(array('posts_per_page'  => $items), $params['situacao']);
		
		include 'view.php';
	}
	
	public function form( $instance )
	{
		$default_inputs = $this->getDefaults();
		
		$args = array_merge($default_inputs, $instance); 
		
		extract( $args );
		
		$title  = esc_attr( $title );
		$items  = (int) $items;
		if ( $items < 1 || 20 < $items )
			$items  = 10;
		$show_summary   	= (int) $show_summary;
		$show_author    	= (int) $show_author;
		$show_date      	= (int) $show_date;
		$show_prazo 		= (int) $show_prazo;
		$show_comment_link 	= (int) $show_comment_link;
		$show_situacao 		= (int) $show_situacao;
		
		if ( !empty($error) )
			echo '<p class="widget-error"><strong>' . sprintf( __('RSS Error: %s'), $error) . '</strong></p>';
		
		include 'form.php';
	}
	
	public function update( $new_instance, $old_instance )
	{
		$old_instance = is_array($old_instance) ? $old_instance : $this->getDefaults();
		$new_instance = array_map(
			function ($item)
			{
				return strip_tags( $item );
			}, $new_instance
		);
		
		$defaults = $this->getDefaults();
		foreach ($defaults as $key => $default)
		{
			if(!array_key_exists($key, $new_instance) && in_array($default, array(true,false,1,0), true))
			{
				$new_instance[$key] = false;
			}
		}
		return array_merge($old_instance, $new_instance);
	}
	
}
?>
