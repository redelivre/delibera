<?php

function get_delibera_header() {
    global $wp;
    
    $opt = delibera_get_config();
    
    $current_url = add_query_arg($wp->query_string, '', home_url($wp->request));
    
	?>
	
	<header class="clearfix">
        <div class="alignleft">
            <h1><?php echo $opt['cabecalho_arquivo']; ?></h1>
            
            <p>
                <?php
                if (is_user_logged_in() && delibera_current_user_can_participate()) {
                    global $current_user;
                    get_currentuserinfo();
                    
                    printf(
                        __('Você está logado como %1$s. Caso deseje sair de sua conta, <a href="%2$s" title="Sair">faça o logout</a>.', 'delibera'),
                        $current_user->display_name,
                        wp_logout_url(home_url('/'))
                    );
                } else if (is_user_logged_in() && !delibera_current_user_can_participate()) {
                    global $current_user;
                    get_currentuserinfo();
                    
                    printf(
                        __('Você está logado como %1$s mas seu usuário não tem permissão para participar desta pauta. Caso deseje entrar com outra conta, <a href="%2$s" title="Sair">faça o logout</a>.', 'delibera'),
                        $current_user->display_name,
                        wp_logout_url(home_url('/'))
                    );
                } else {   
                    printf(
                        __('Para participar, você precisa <a href="%1$s" title="Faça o login">fazer o login</a> ou <a href="%2$s" title="Registre-se" class="register">registrar-se no site</a>.', 'delibera'), 
                        wp_login_url($current_url),
                        site_url('wp-login.php?action=register', 'login')."&lang="
                    );
                }
                ?>
            </p>
        </div>
        <div class="alignright">
            <a class="btn" href="<?php echo get_page_link(get_page_by_slug(DELIBERA_ABOUT_PAGE)->ID); ?>"><?php _e('Saiba por que e como participar', 'delibera'); ?></a>
        </div>
    </header>

	<?php
}

/**
 * Formulário do comentário que é usado para
 * aprovar, discutir e votar nas pautas.
 * 
 * Manipula com array usado pelo Wordpress para 
 * compor o formulário de comentário.
 * 
 * @param array $defaults
 * @return array
 */
function delibera_comment_form($defaults)
{
    global $post, $delibera_comments_padrao, $user_identity, $comment_footer;
    $comment_footer = "";
    
    if ($delibera_comments_padrao === true) {
        $defaults['fields'] = $defaults['must_log_in'];
        
        if (!is_user_logged_in()) {
            $defaults['comment_field'] = "";
            $defaults['logged_in_as'] = '';
            $defaults['comment_notes_after'] = "";
            $defaults['label_submit'] = "";
            $defaults['id_submit'] = "botao-oculto";
            $defaults['comment_notes_before'] = ' ';
        }
        
        return $defaults;
    }
    
    if (get_post_type($post) == "pauta") {
        $current_user = wp_get_current_user();
        $defaults['id_form'] = 'delibera_commentform';
        $defaults['comment_field'] = '<div class="delibera_before_fields">'.$defaults['comment_field'];
        $situacao = delibera_get_situacao($post->ID);
        
        switch ($situacao->slug) { 
            case 'validacao':
                $user_comments = delibera_get_comments($post->ID, 'validacao', array('user_id' => $current_user->ID));
                $temvalidacao = false;
                foreach ($user_comments as $user_comment) {
                    if (get_comment_meta($user_comment->comment_ID, 'delibera_comment_tipo', true) == 'validacao') {
                        $temvalidacao = true;
                        break;
                    }
                }

                if ($temvalidacao) {
                    $defaults['comment_notes_after'] = '
                        <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery("#respond").hide();
                                jQuery(".reply").hide();
                            });
                        </script>
                    ';
                } else {
                    $defaults['title_reply'] = __('Você quer ver essa pauta posta em discussão?','delibera');
                    $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> e ter permissão para votar.','delibera'), wp_login_url(apply_filters('the_permalink', get_permalink($post->ID))));                
                    if (delibera_current_user_can_participate()) {
                        $form = '
                            <div id="painel_validacao" class="actions textcenter">
                                <button class="btn btn-success">Sim</button>
                                <button class="btn btn-danger">Não</button>
                                <input type="hidden" name="delibera_validacao" id="delibera_validacao" />
                                <input name="comment" value="A validação de '.$current_user->display_name.' foi registrada no sistema." style="display:none;" />
                                <input name="delibera_comment_tipo" value="validacao" style="display:none;" />
                            </div>';
                        $defaults['comment_field'] = $form;
                        $defaults['comment_notes_after'] = '<script type="text/javascript">jQuery(document).ready(function() { jQuery(\'input[name="submit"]\').hide(); });</script><div class="delibera_comment_button">';;
                        $defaults['logged_in_as'] = "";
                        $defaults['label_submit'] = "__('Votar','delibera')";
                    } else {
                        $defaults['comment_field'] = "";
                        $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __('Você está logado como <a href="%1$s">%2$s</a> que não é um usuário autorizado a votar. <a href="%3$s" title="Sair desta conta?">Sair desta conta</a> e logar com um usuário com permissão de votar?','delibera') , admin_url('profile.php'), $user_identity, wp_logout_url(apply_filters('the_permalink', get_permalink($post->ID)))) . '</p>';
                        $defaults['comment_notes_after'] = "";
                        $defaults['label_submit'] = "";
                        $defaults['id_submit'] = "botao-oculto";
                    }
                }
                break;
            case 'discussao':
                $defaults['title_reply'] = __('Participar da discussão','delibera');
                $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> para contribuir com a discussão.','delibera'), wp_login_url(apply_filters('the_permalink', get_permalink($post->ID))));
                $defaults['comment_notes_after'] = "";
                $defaults['logged_in_as'] = "";
                $defaults['comment_field'] = '<input name="delibera_comment_tipo" value="discussao" style="display:none;" />' . $defaults['comment_field'];
                
                if($situacao->slug == 'relatoria') {
                    $defaults['comment_field'] = '<input id="delibera-baseouseem" name="delibera-baseouseem" value="" style="display:none;" autocomplete="off" />
                        <div id="painel-baseouseem" class="painel-baseouseem"><label id="painel-baseouseem-label" class="painel-baseouseem-label" >' . __('Proposta baseada em:', 'delibera') . '&nbsp;</label></div><br/>'
                        . $defaults['comment_field'];
                }
                
                if (delibera_current_user_can_participate()) {
                    $replace = '';

                    if (delibera_pautas_suportam_encaminhamento()) {
                        if ($situacao->slug != 'relatoria') {
                            $replace .= '<label class="delibera-encaminha-label" ><input type="radio" name="delibera_encaminha" value="N" checked="checked" />'.__('Opinião', 'delibera').'</label>';
                        }

                        $replace .= '<label class="delibera-encaminha-label" ><input type="radio" name="delibera_encaminha" value="S" ' . (($situacao->slug == 'relatoria') ? ' checked="checked" ' : '') . ' />'.__('Proposta de encaminhamento', 'delibera').'</label>';
                    } else {
                        $replace .= '<input type="hidden" name="delibera_encaminha" value="N" />';
                    }

                    $defaults['comment_field'] = preg_replace ("/<label for=\"comment\">(.*?)<\/label>/", $replace, $defaults['comment_field']);
                } else {
                    $defaults['comment_field'] = "";
                    $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __('Você está logado como <a href="%1$s">%2$s</a> que não é um usuário autorizado a votar. <a href="%3$s" title="Sair desta conta?">Sair desta conta</a> e logar com usuário que possa votar?','delibera') , admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>';
                    $defaults['comment_notes_after'] = "";
                    $defaults['label_submit'] = "";
                    $defaults['id_submit'] = "botao-oculto";
                }
                
                if (has_filter('delibera_discussao_comment_form')) {
                    $defaults = apply_filters('delibera_discussao_comment_form', $defaults, $situacao->slug);
                }
                break;
            case 'relatoria':
                $defaults['title_reply'] = __('Novo encaminhamento', 'delibera');
                $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> para contribuir com a discussão.','delibera'), wp_login_url(apply_filters('the_permalink', get_permalink($post->ID))));
                $defaults['comment_notes_after'] = '<p class="bottom textright">
                    <button id="new-encaminhamento-cancel" type="reset" class="btn btn-danger" style="display: none;">Cancelar</button>
                    <button id="new-encaminhamento-save" type="submit" class="btn btn-success">Salvar</button>
                </p>';
                $defaults['logged_in_as'] = '';
                $defaults['comment_field'] = '<input name="delibera_comment_tipo" value="discussao" style="display:none;" />' . $defaults['comment_field'];
                $defaults['id_submit'] = "botao-oculto";
                
                if ($situacao->slug == 'relatoria') {
                    $defaults['comment_field'] = '<input id="delibera-baseouseem" name="delibera-baseouseem" type="hidden" value="" />'
                        . '<p id="baseadoem-title" style="display: none;"><strong>' . __('Proposta de encaminhamento baseado no(s) encaminhamento(s) da(s) seguinte(s) pessoa(s):', 'delibera') . '</strong> <span id="baseadoem-list"></span></p>'
                        . $defaults['comment_field'];
                }
                
                $replace = '<input type="hidden" name="delibera_encaminha" value="S" />';
                $defaults['comment_field'] = preg_replace("/<label for=\"comment\">(.*?)<\/label>/", $replace, $defaults['comment_field']);
                
                if (has_filter('delibera_discussao_comment_form')) {
                    $defaults = apply_filters('delibera_discussao_comment_form', $defaults, $situacao->slug);
                }
                break;
            case 'emvotacao':
                $user_comments = delibera_get_comments($post->ID, 'voto', array('user_id' => $current_user->ID));
                $temvoto = false;
                
                foreach ($user_comments as $user_comment) {
                    if(get_comment_meta($user_comment->comment_ID, 'delibera_comment_tipo', true) == 'voto') {
                        $temvoto = true;
                        break;
                    }
                }
                
                if ($temvoto) {
                    $defaults['comment_notes_after'] = '
                        <script type="text/javascript">
                            var formdiv = document.getElementById("respond");
                            formdiv.style.display = "none";
                        </script>
                    ';
                } else {
                    $defaults['title_reply'] = __('Votação dos encaminhamentos propostos', 'delibera');
                    $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> e ter permissão para votar.'), wp_login_url(apply_filters('the_permalink', get_permalink($post->ID))));
                    $encaminhamentos = array();
                    
                    if (delibera_current_user_can_participate()) {
                        $form = '<div id="encaminhamentos" class="delibera_checkbox_voto">';
                        $encaminhamentos = delibera_get_comments_encaminhamentos_selecionados($post->ID);
                        
                        if (empty($encaminhamentos)) {
                            // se acabar o prazo e o relator não selecionar nenhum encaminhamento
                            // coloca todos os encaminhamentos para votacao
                            $encaminhamentos = delibera_get_comments_encaminhamentos($post->ID);
                        }
                        
                        $form .= '<div id="nenhum-voto" class="error" style="display: none;"><p><strong>' . __('Você precisa selecionar pelo menos um encaminhamento.', 'delibera') . '</strong></p></div>';
                        $form .= '<div class="instrucoes-votacao">'.__('Escolha os encaminhamentos que deseja aprovar e depois clique em "Votar":','delibera').'</div>';
                        $form .= '<ol class="encaminhamentos">';
                        
                        $i = 0;
                        foreach ($encaminhamentos as $encaminhamento) {
                            $tipo = get_comment_meta($encaminhamento->comment_ID, 'delibera_comment_tipo', true);
                            
                            $form .= '<li class="encaminhamento clearfix' . (($tipo == 'encaminhamento_selecionado') ? ' encaminhamentos-selecionados ' : '') . '">
                                <div class="alignleft checkbox">
                                    <input type="checkbox" name="delibera_voto'.$i.'" id="delibera_voto'.$i.'" value="'.$encaminhamento->comment_ID.'" />
                                </div>
                                <div class="alignleft content">
                                    <label for="delibera_voto'.$i++.'" class="label-voto">'.$encaminhamento->comment_content.'</label>
                                </div>
                            </li>';
                        }
                        
                        $form .= '</ol>';
                        $form .= '
                                <input name="delibera_comment_tipo" value="voto" style="display:none;" />
                                <input name="comment" value="O voto de '.$current_user->display_name.' foi registrado no sistema" style="display:none;" />
                            </div>'
                        ;
                        
                        $defaults['comment_field'] = $form;
                        $defaults['logged_in_as'] = "";
                        $defaults['label_submit'] = __('Votar','delibera');
                        $defaults['comment_notes_after'] = '<ol class="encaminhamentos"><li class="submit">';;
                        $comment_footer = "</li></ol>";
                    } else {
                        $defaults['comment_field'] = "";
                        $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __('Você está logado como <a href="%1$s">%2$s</a> que não é um usuário autorizado a votar. <a href="%3$s" title="Sair desta conta?">Sair desta conta</a> e logar com um usuário com permisão para votar?','delibera') , admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>';
                        $defaults['comment_notes_after'] = "";
                        $defaults['label_submit'] = "";
                        $defaults['id_submit'] = "botao-oculto";
                    }
                }

                if (has_filter('delibera_resolucoes_comment_form')) {
                    $defaults = apply_filters('delibera_resolucoes_comment_form', $defaults, $temvoto, $encaminhamentos);
                }
                break;
            case 'comresolucao':
                $defaults['comment_notes_after'] = '<script type="text/javascript">
                    var formdiv = document.getElementById("respond");
                    formdiv.style.display = "none";
                </script>';
                if (has_filter('delibera_comresolucao_comment_form')) {
                    $defaults = apply_filters('delibera_comresolucao_comment_form', $defaults);
                }
                break;
        }

        if (!is_user_logged_in()) {
            $defaults['comment_notes_before'] = '<script type="text/javascript">
                    var formdiv = document.getElementById("respond");
                    formdiv.style.display = "none";
            </script>';
        }
    }

    return $defaults;   
}
add_filter('comment_form_defaults', 'delibera_comment_form');

add_action('wp_enqueue_scripts', function() {
    global $deliberaThemes, $post;
    
    if (get_post_type() == 'pauta')
    {
    	$situacao = delibera_get_situacao($post->ID);
        wp_enqueue_script('twentyfifteen', $deliberaThemes->getThemeUrl() . '/js/twentyfifteen.js', array('delibera'));
        
        if ($situacao->slug == 'relatoria') {
            wp_enqueue_script('twentyfifteen-relatoria', $deliberaThemes->getThemeUrl() . '/js/twentyfifteen-relatoria.js', array('delibera'));
        } else if ($situacao->slug == 'emvotacao') {
            wp_enqueue_script('twentyfifteen-votacao', $deliberaThemes->getThemeUrl() . '/js/twentyfifteen-votacao.js', array('delibera'));
        }
    }
});

/**
 * Implementa o filtro da página de listagem de pautas
 * 
 * @return null
 */
function delibera_twentyfifteen_filter_pautas($query) {
    if (is_post_type_archive('pauta') && !is_admin()) {
        $situacoes = array();
        $temas = array();
        $taxonomy_filters = array('relation' => 'AND');
        
        if (!empty($_GET['situacao_filtro'])) {
            foreach ($_GET['situacao_filtro'] as $situacao => $value) {
                if ($value == 'on') {
                    $situacoes[] = $situacao;
                }
            }
        }

        if (!empty($_GET['tema_filtro'])) {
            foreach ($_GET['tema_filtro'] as $tema => $value) {
                if ($value == 'on') {
                    $temas[] = $tema;
                }
            }
        }

        if (!empty($situacoes)) {
            $taxonomy_filters[] = array('taxonomy' => 'situacao', 'field' => 'slug', 'terms' => $situacoes);
        }
        
        if (!empty($temas)) {
            $taxonomy_filters[] = array('taxonomy' => 'tema', 'field' => 'slug', 'terms' => $temas);
        }
        
        $query->set('tax_query', $taxonomy_filters);
        
        $query->set('showposts', 10);
    }
}
add_action('pre_get_posts', 'delibera_twentyfifteen_filter_pautas');

/* Gera código html para criação do botão curtir/concordar do sistema delibera
 * 
 * @param $ID post_ID ou comment_ID
 * @param $type 'pauta' ou 'comment'
 */
function delibera_gerar_curtir($ID, $type ='pauta')
{
    global $post;
    
    $situacoes_validas = array('validacao' => false, 'discussao' => true, 'emvotacao' => false);
    
    $postID = 0;
    
    if (is_object($ID)) {
        if($type == 'post' || $type == 'pauta') {
            $ID = $ID->ID;
            $postID = $ID;
        } else {
            $postID = $ID->comment_post_ID;
            $ID = $ID->comment_ID;
        }
    }
    
    $ncurtiu = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_curtir', true) : get_comment_meta($ID, 'delibera_numero_curtir', true));
    $ndiscordou = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_discordar', true) : get_comment_meta($ID, 'delibera_numero_discordar', true));
    $situacao = delibera_get_situacao($postID);

    $html = '<div class="delibera-like-count">' . ($ncurtiu > 0 ? sprintf(_n('%d concordou', '%d concordaram', $ncurtiu, 'delibera'), $ncurtiu) : '') . '</div>';    
    $html .= '<div class="delibera-unlike-count">' . ($ndiscordou > 0 ? sprintf(_n('%d discordou', '%d discordaram', $ndiscordou, 'delibera'), $ndiscordou) : '') . '</div><br/>';
    
    if (delibera_current_user_can_participate()) {
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (!delibera_ja_curtiu($ID, $user_id, $ip, $type) && // Ainda não curitu
            (is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas)) && $situacoes_validas[$situacao->slug] && // é uma situação válida
            !(delibera_ja_discordou($ID, $user_id, $ip, $type))) // não discordou
        {
            // $html .= (!$ncurtiu ? '<div class="delibera-like-count"></div>' : '');
            $html .= '<button class="btn btn-mini btn-success delibera_like"><span class="delibera_like_text">' . __('Concordo', 'delibera') . '</span>';
            $html .= "<input type='hidden' name='object_id' value='{$ID}' />";
            $html .= "<input type='hidden' name='type' value='{$type}' />";
            $html .= '</button>';
        }
    }
    
    return $html;
}

/**
 * 
 * Gera código html para criação do botão discordar do sistema delibera
 * @param $ID int post_ID ou comment_ID
 * @param $type string 'pauta' ou 'comment'
 */
function delibera_gerar_discordar($ID, $type ='pauta')
{
    global $post;
    
    $situacoes_validas = array('validacao' => false, 'discussao' => true, 'emvotacao' => false);
    $ndiscordou = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_discordar', true) : get_comment_meta($ID, 'delibera_numero_discordar', true));
    $postID = 0;
    if(is_object($ID))
    {
        if($type == 'post' || $type == 'pauta')
        {
            $ID = $ID->ID;
            $postID = $ID;
        }
        else
        {
            $postID = $ID->comment_post_ID;
            $ID = $ID->comment_ID;
        }
    }
    
    $situacao = delibera_get_situacao($postID);
    
    if(delibera_current_user_can_participate()) {
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (!delibera_ja_discordou($ID, $user_id, $ip, $type) && // Ainda não curitu
            (is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas)) && $situacoes_validas[$situacao->slug] &&// é uma situação válida
            !(delibera_ja_curtiu($ID, $user_id, $ip, $type))) // não discordou
        {
			$html = '';
            // $html .= (!$ndiscordou ? '<div class="delibera-unlike-count"></div>' : '');
            $html .= '<button class="btn btn-mini btn-danger delibera_unlike"><span class="delibera_unlike_text">' . __('Discordo','delibera') . '</span>';
            $html .= "<input type='hidden' name='object_id' value='{$ID}' />";
            $html .= "<input type='hidden' name='type' value='{$type}' />";
            $html .= '</button>';
            
            return $html;
        }
    }
}

/**
 * Salva quais encaminhamentos o relator escolheu para
 * serem usados na votação.
 */
function delibera_usar_na_votacao()
{
    if (current_user_can('relatoria')) {
        $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
        $checked = filter_input(INPUT_POST, 'checked', FILTER_SANITIZE_NUMBER_INT);
       
        if ($checked) {
            update_comment_meta($comment_id, 'delibera_comment_tipo', 'encaminhamento_selecionado');
        } else {
            update_comment_meta($comment_id, 'delibera_comment_tipo', 'encaminhamento');
        }
    }
    
    die();
}
add_action('wp_ajax_delibera_definir_votacao', 'delibera_usar_na_votacao');
add_action('wp_ajax_nopriv_delibera_definir_votacao', 'delibera_usar_na_votacao');

/**
 * Retorna apenas os encaminhamentos ids dos comentários do tipo
 * 'encaminhamento_selecionado' (aqueles que foram selecionados 
 * pelo relator para ir para votação).
 * 
 * @param int $post_id
 * @return array array de ids dos encaminhamentos
 */
function delibera_get_comments_encaminhamentos_selecionados_ids($post_id)
{
    $ids = array();
    $comments = delibera_get_comments($post_id, 'encaminhamento_selecionado');
    
    foreach ($comments as $comment) {
        $ids[] = $comment->comment_ID;
    }
    
    return $ids;
}

/**
 * Parseia a tag <img> com o avatar do usuário para poder
 * adicionar o atributo title já que não existe uma função
 * no wp que retorna apenas a url do avatar do usuário
 * 
 * @see http://core.trac.wordpress.org/ticket/21195
 * @param int $user_id
 * @return string
 */
function get_avatar_with_title($user_id)
{
    $authorName = get_the_author_meta('display_name', $user_id);
    $avatar = get_avatar($user_id, 44, '', $authorName);
    
    $avatar = preg_replace('|/>$|', " title='{$authorName}' />", $avatar);
    
    return $avatar;
}

/**
 * Método para detectar se o PHP está sendo executado via cli (linha de comando).
 * @author Julio Vedovatto <juliovedovatto@gmail.com>
 * @return boolean
 */
function is_cli() {
	return php_sapi_name() == 'cli';
}

/**
 * Método para detectar se requisição foi feita via método AJAX.
 * @author Julio Vedovatto <juliovedovatto@gmail.com>
 * @return boolean
 */
function is_ajax_request() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH'])  && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Função para depuração de valores. Aceita múltiplos argumentos para deburação (var_dump).
 * @author Julio Vedovatto <juliovedovatto@gmail.com>
 * @return void
 */
function debug() {
	if (!is_array($items = func_get_args()))
		$items = array();
	
	if (!is_cli() && !is_ajax_request())
		echo '<pre style="border: 1px solid #000; color: red; padding: 10px;">';
	foreach ($items as $item) {
		var_dump($item);
	}
	if (!is_cli() && !is_ajax_request())
		echo '</pre>';
}