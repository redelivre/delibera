<?php 
// Parse shorttag

function delibera_replace_propostas($matches)
{	
	global $wp_posts;
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    
    $param = array(); // TODO Tratar Parametros
    
    $html = delibera_get_propostas($param);
    
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
add_shortcode( 'delibera_lista_de_propostas', 'delibera_replace_propostas' );

function delibera_replace_pautas($matches)
{	
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    
    $param = array(); // TODO Tratar Parametros
    
    $html = delibera_get_pautas($param);
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
add_shortcode( 'delibera_lista_de_pautas', 'delibera_replace_pautas' );

function delibera_replace_resolucoes($matches)
{	
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    
    $param = array(); // TODO Tratar Parametros
    
    $html = delibera_get_resolucoes($param);
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
add_shortcode( 'delibera_lista_de_resolucoes', 'delibera_replace_resolucoes' );

function delibera_replace_agendamentos($matches)
{	
	global $wp_filter;
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    
    $param = array(); // TODO Tratar Parametros
    
    $html = print_r($wp_filter, true);
    
	return $html; // Retornar código da representação
}
add_shortcode( 'delibera_lista_de_agendamentos', 'delibera_replace_agendamentos' );

function delibera_replace_timeline($args)
{
	$atts = array('post_id' => false, 'tipo_data' => false);
	$atts = array_merge($atts, $args);
	
    return delibera_timeline($atts['post_id'], $atts['tipo_data']);
}
add_shortcode( 'delibera_timeline', 'delibera_replace_timeline' );

function delibera_replace_like($matches)
{
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    $param = array(); // TODO Tratar Parametros
    
    return delibera_gerar_curtir(get_the_ID());
}
add_shortcode( 'delibera_like', 'delibera_replace_like' );

function delibera_replace_unlike($matches)
{
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    $param = array(); // TODO Tratar Parametros
    
    return delibera_gerar_discordar(get_the_ID());
}
add_shortcode( 'delibera_unlike', 'delibera_replace_unlike' );

function delibera_replace_seguir($matches)
{
	$temp = explode(',', $matches[1]); // configurações da shorttag
    $count = count($temp);
    $param = array(); // TODO Tratar Parametros
    
    return delibera_gerar_seguir(get_the_ID());
}
add_shortcode( 'delibera_seguir', 'delibera_replace_seguir' );

function delibera_replace_basear($args)
{
	$atts = array('id' => '', 'autor' => __('Desconhecido', 'delibera'));
	$atts = array_merge($atts, $args);
	$val = $atts['id'];
	$autor = $atts['autor'];
	$server = $_SERVER['SERVER_NAME'];
	$endereco = $_SERVER ['REQUEST_URI'];
	$site_url = "http".(array_key_exists('HTTPS', $_SERVER))."://".$server.$endereco;
	
	$html = '<div id="painel-baseouseem-item-'.$val.'" class="painel-baseouseem-item" ><div id="painel-baseouseem-link-'.$val.'" class="painel-baseouseem-link" ><a href="'.$site_url.'#delibera-comment-'.$val.'">@'.$autor.'</a></div>, </div>';
    
    return $html;
}
add_shortcode( 'delibera_basear', 'delibera_replace_basear' );

// End Parse shorttag
?>