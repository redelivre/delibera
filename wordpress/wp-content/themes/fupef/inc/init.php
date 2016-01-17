<?php

/**
 * INIT
 *
 * Esse arquivo tem como objetivo chamar  
 * funcionalidades do wordpress
 * 
 * @author Kaleb Martins <kaleb@paradoxodigital.com.br>
 * @date (setembro 2015)
 * 
 */

/*--------------------------------------------------------------------------------------------------
// Adcionando funcionalidade de Imagem Destacada
--------------------------------------------------------------------------------------------------*/
if (function_exists('add_theme_support')):
	add_theme_support('post-thumbnails');
add_theme_support('menus');
endif;



// Chamando todos os JS e CSS do WordPress para a batalha
wp_enqueue_script("jquery-1.9.1", '/js/jquery-1.9.1.js');
wp_enqueue_script("jquery-migrate-1.2.1.min", '/js/jquery-migrate-1.2.1.min.js');
wp_enqueue_script("jquery-ui-1.10.3.custom.min", '/js/jquery-ui-1.10.3.custom.min.js');
wp_enqueue_script("jquery.maskedinput.min", '/js/jquery.maskedinput.min.js');
wp_enqueue_script('admin_scripts', '/js/scripts.js');
wp_enqueue_style('admin_css', '/css/style.css');
wp_enqueue_style('template', '/css/template.css');
wp_enqueue_style('galeria_css', '/css/style-page.css');
wp_enqueue_style('jquery-ui-1.10.3.custom.min', '/css/ui-lightness/jquery-ui-1.10.3.custom.min.css');

// Mudando URL do logo do WP-ADMIN
function my_site_url(){
	return ('http://www.fupef.ufpr.br/');
}
add_filter('login_headerurl', 'my_site_url');


// Mudando Logo do WP-ADMIN
function custom_login_logo() {
	echo '<style type="text/css">h1 a {width:230px !important; height:100px !important; background: url('.get_bloginfo('template_directory').'/public/img/logo.png) no-repeat !important; }</style>';
}
add_action('login_head', 'custom_login_logo');