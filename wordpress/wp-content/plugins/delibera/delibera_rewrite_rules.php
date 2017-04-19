<?php
/**
 * Define todas as regras de rewrite utilizadas pelo delibera
 */


// nova-pauta REWRITE: habilita a interface para criação de nova pauta pela interface pública

add_action('generate_rewrite_rules', 'delibera_nova_pauta_generate_rewrite_rules');

function delibera_nova_pauta_generate_rewrite_rules($wp_rewrite) {
    $new_rules = array(
        "nova-pauta/?$" => "index.php?&tpl=nova-pauta",

    );
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

add_filter('query_vars', 'delibera_nova_pauta_query_vars');

function delibera_nova_pauta_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

add_action('template_redirect', 'delibera_nova_pauta_template_redirect_intercept');

function delibera_nova_pauta_template_redirect_intercept() {
    global $wp_query, $wpdb;

    $tpl = $wp_query->get('tpl');

    if ($tpl && $tpl === 'nova-pauta') {
        $options = delibera_get_config();
        if(isset($options['criar_pauta_pelo_front_end']) && $options['criar_pauta_pelo_front_end'] == 'S'){

            global $deliberaThemes;

            include $deliberaThemes->themeFilePath('delibera_nova_pauta.php');
            die;
        }
    }
}

// -------- FIM nova-pauta -------

// temas REWRITE: lista de temas disponíveis na consulta

add_action('generate_rewrite_rules', 'delibera_temas_generate_rewrite_rules');

function delibera_temas_generate_rewrite_rules($wp_rewrite) {
    // Cat = 1 é um POG para o WordPress que essa é uma página de archive
    $new_rules = array(
        "temas/?$" => "index.php?&tpl=temas&cat=1",

    );
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

add_filter('query_vars', 'delibera_temas_query_vars');

function delibera_temas_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

add_action('template_redirect', 'delibera_temas_template_redirect_intercept');

function delibera_temas_template_redirect_intercept() {
    global $wp_query;

    $tpl = $wp_query->get('tpl');

    if ($tpl && $tpl === 'temas') {
        global $deliberaThemes;

        include $deliberaThemes->themeFilePath('delibera_temas.php');
        die;
    }
}

// ---------- FIM temas/ ---------------