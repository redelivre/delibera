<?php
/**
 * RESET
 *
 * Esse arquivo tem como objetivo "zerar" as 
 * funcionalizades do wordpress, deixando ele mais 
 * simples e direto.
 * 
 * @author Kaleb Martins <kaleb@paradoxodigital.com.br>
 * @date (setembro 2015)
 * 
 */

/*--------------------------------------------------------------------------------------------------
// Removendo tudo que não interessa no Head do theme
--------------------------------------------------------------------------------------------------*/
remove_action('wp_head', 'print_emoji_detection_script', 7 );
remove_action('wp_print_styles', 'print_emoji_styles' );
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action( 'welcome_panel', 'wp_welcome_panel' ); // removendo o WelCome do WordPress


/*--------------------------------------------------------------------------------------------------
// Removendo os box Meta do dashboard, deixando apenas o 'Agora'
--------------------------------------------------------------------------------------------------*/
function remove_dashboard_meta() {
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	// remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	// remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
	// remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
}
add_action( 'admin_init', 'remove_dashboard_meta' );



/*--------------------------------------------------------------------------------------------------
// Removendo os menus, deixando os importantes
--------------------------------------------------------------------------------------------------*/
function remove_menus() {    
	global $submenu;	
	// remove_menu_page('upload.php');
	// remove_menu_page('link-manager.php');
	// remove_menu_page('tools.php');
	// remove_submenu_page('themes.php', 'theme-editor.php');
	// remove_menu_page('edit-comments.php');
	// remove_submenu_page ( 'options-general.php', 'options-writing.php' );
	// remove_submenu_page ( 'options-general.php', 'options-discussion.php' );
	// remove_submenu_page ( 'options-general.php', 'options-media.php' );
	// remove_menu_page('plugins.php'); // Plugins
	// remove_menu_page('options-general.php');// Settings
	// remove_submenu_page ( 'index.php', 'update-core.php' ); //Dashboard->Updates
	// remove_submenu_page ( 'themes.php', 'themes.php' ); // Appearance-->Themes
	remove_submenu_page( 'themes.php', 'customize.php' );
	// remove_submenu_page ( 'themes.php', 'widgets.php' ); // Appearance-->Widgets	
	// remove_submenu_page ( 'options-general.php', 'options-general.php' ); // Settings->General
	// remove_submenu_page ( 'options-general.php', 'options-permalink.php' ); // Settings->writing
	// remove_submenu_page ( 'options-general.php', 'options-reading.php' ); // Settings->Reading
	// remove_submenu_page ( 'options-general.php', 'options-privacy.php' ); // Settings->Privacy
}
add_action('admin_menu', 'remove_menus', 102);

function remove_customize_page(){
	global $submenu;
	unset($submenu['themes.php'][6]);
}
add_action( 'admin_menu', 'remove_customize_page');


/*--------------------------------------------------------------------------------------------------
// Escondendo Opções de Tela e Ajuda 
--------------------------------------------------------------------------------------------------*/
function hide_help_link() {
	echo '<style type="text/css">#contextual-help-link-wrap, .toplevel_page_wp-responsive-menu, .wp-admin-bar-customize, #wp-admin-bar-comments, .comment-count { display: none !important; }</style>';
}
add_action('admin_head', 'hide_help_link');



/*--------------------------------------------------------------------------------------------------
// Zerando o Footer 
--------------------------------------------------------------------------------------------------*/
function custom_admin_footer() {
	echo 'Desenvolvido por Kaleb Martins, Teste da FUPEF';
}
add_filter('admin_footer_text', 'custom_admin_footer');

