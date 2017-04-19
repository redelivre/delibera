<?php

function get_delibera_header() {
    $opt = delibera_get_config();
	?>
	
	<div id="delibera-header">
		<?php
		
		$h = ( is_post_type_archive ( 'pauta' ) ) ? 'h1' : 'h2';
		
		$delibera_header = '<' . $h . ' class="page-title"><span>';
		$delibera_header .= __( 'Sistema de Discussão', 'delibera' );
		$delibera_header .= '</span></' . $h . '>';
		
		echo $delibera_header;
		
		?>
		<div class="delibera-apresentacao">
	        <p class="delibera-boasvindas">
    	    	<?php
    	    			echo $opt['cabecalho_arquivo'];
    	    	?>
        	</p>
            <p class="delibera-participacao">
            	<?php
            		$about =  get_page_by_slug( DELIBERA_ABOUT_PAGE );
            		if(is_null($about))
            		{
            			delibera_create_about_page();
            			$about =  get_page_by_slug( DELIBERA_ABOUT_PAGE );
            		}
            		
            	?>
            	<a href="<?php echo get_page_link($about->ID); ?>"><?php _e( 'Saiba por que e como participar', 'delibera' ); ?></a>
            </p>
        </div>
		<p class="delibera-login">
			<?php
			if ( is_user_logged_in() )
			{
				global $current_user;
				get_currentuserinfo();
				
            	printf(
            		__( 'Você está logado como <a href="%1$s" title="Ver meu perfil" class="profile">%2$s</a>. Caso deseje sair de sua conta, <a href="%3$s" title="Sair">faça o logout</a>.', 'delibera' ),
            		get_author_posts_url($current_user->ID),
            		$current_user->display_name,
            		wp_logout_url( home_url( '/' ) )
            	);
			} 		
			else
			{	
				printf(
            		__( 'Para participar, você precisa <a href="%1$s" title="Faça o login">fazer o login</a> ou <a href="%2$s" title="Registre-se" class="register">registrar-se no site</a>.', 'delibera' ), 
            		wp_login_url( get_permalink() ),
            		site_url('wp-login.php?action=register', 'login')."&lang="
            	);
            							
			}
			?>
		</p><!-- .delibera-login -->
		
		<?php if ( ! ( is_home() || is_post_type_archive( 'pauta' ) ) ) : ?>
			<p class="delibera-pagina-discussoes"><a href="<?php echo get_post_type_archive_link( 'pauta' ); ?>"><?php _e( 'Voltar à página de discussões', 'delibera' ); ?></a></p>
		<?php endif; ?>
	</div><!-- #delibera-header -->

	<?php
}

/**
 * Formulário do comentário
 * @param array $defaults
 */
function delibera_comment_form($defaults)
{
    global $post,$delibera_comments_padrao,$user_identity,$comment_footer;
    $comment_footer = "";
    
    if($delibera_comments_padrao === true)
    {
        $defaults['fields'] = $defaults['must_log_in'];
        if(!is_user_logged_in())
        {
            $defaults['comment_field'] = "";
            $defaults['logged_in_as'] = '';
            $defaults['comment_notes_after'] = "";
            $defaults['label_submit'] = "";
            $defaults['id_submit'] = "botao-oculto";
            $defaults['comment_notes_before'] = ' ';
        }
        return $defaults;
    }
    if(get_post_type($post) == "pauta")
    {
        /* @var WP_User $current_user */
        $current_user = wp_get_current_user();
        $defaults['id_form'] = 'delibera_commentform';
        $defaults['comment_field'] = '<div class="delibera_before_fields">'.$defaults['comment_field'];
        $situacao = delibera_get_situacao($post->ID);
        
        switch ($situacao->slug)
        { 
            
            case 'validacao':
            {
                $user_comments = delibera_get_comments($post->ID, 'validacao', array('user_id' => $current_user->ID));
                $temvalidacao = false;
                foreach ($user_comments as $user_comment)
                {
                    if(get_comment_meta($user_comment->comment_ID, 'delibera_comment_tipo', true) == 'validacao')
                    {
                        $temvalidacao = true;
                        break;
                    }
                }
                if($temvalidacao)
                {
                    $defaults['comment_notes_after'] = '
                        <script type="text/javascript">
                            var formdiv = document.getElementById("respond");
                            formdiv.style.display = "none";
                        </script>
                    ';
                }
                else
                {
                    $defaults['title_reply'] = __('Você quer ver essa pauta posta em discussão?','delibera');
                    $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> e ter permissão para votar.','delibera'),wp_login_url( apply_filters( 'the_permalink', get_permalink( $post->ID ))));                
                    if (delibera_current_user_can_participate()) {
                        $form = '
                            <div id="painel_validacao" >
                                <input id="delibera_aceitar" type="radio" name="delibera_validacao" value="S" checked /><label for="delibera_aceitar" class="delibera_aceitar_radio_label">'.__('Aceitar','delibera').'</label>
                                <input id="delibera_rejeitar" type="radio" name="delibera_validacao" value="N"  /><label for="delibera_rejeitar" class="delibera_aceitar_radio_label">'.__('Rejeitar','delibera').'</label>
                                <input name="comment" value="A validação de '.$current_user->display_name.' foi registrada no sistema." style="display:none;" />
                                <input name="delibera_comment_tipo" value="validacao" style="display:none;" />
                            </div>
                        ';
                        $defaults['comment_field'] = $form;
                        $defaults['comment_notes_after'] = '<div class="delibera_comment_button">';;
                        $defaults['logged_in_as'] = "";
                        $defaults['label_submit'] = __('Votar','delibera');
                        $comment_footer = "</div>";
                    } else {
                        $defaults['comment_field'] = "";
                        $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __('Você está logado como <a href="%1$s">%2$s</a> que não é um usuário autorizado a votar. <a href="%3$s" title="Sair desta conta?">Sair desta conta</a> e logar com um usuário com permissão de votar?','delibera') , admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>';
                        $defaults['comment_notes_after'] = "";
                        $defaults['label_submit'] = "";
                        $defaults['id_submit'] = "botao-oculto";
                    }
                }
            } break;
            case 'discussao':
            case 'relatoria':
            {
                $defaults['title_reply'] = sprintf(__('Discussão em torno de "%s"','delibera'),$post->post_title);
                $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> para contribuir com a discussão.','delibera'),wp_login_url( apply_filters( 'the_permalink', get_permalink( $post->ID ))));
                $defaults['comment_notes_after'] = "";
                $defaults['logged_in_as'] = "";
                $defaults['comment_field'] = '
                        <input name="delibera_comment_tipo" value="discussao" style="display:none;" />'.$defaults['comment_field']
                ;
                if($situacao->slug == 'relatoria')
                {
                    $defaults['comment_field'] = '
                            <input id="delibera-baseouseem" name="delibera-baseouseem" value="" style="display:none;" autocomplete="off" />
                            <div id="painel-baseouseem" class="painel-baseouseem"><label id="painel-baseouseem-label" class="painel-baseouseem-label" >'.__('Proposta baseada em:', 'delibera').'&nbsp;</label></div><br/>
                            '.$defaults['comment_field']
                    ;
                }
                if (delibera_current_user_can_participate())
                {
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
                }
                else
                {
                    $defaults['comment_field'] = "";
                    $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __('Você está logado como <a href="%1$s">%2$s</a> que não é um usuário autorizado a votar. <a href="%3$s" title="Sair desta conta?">Sair desta conta</a> e logar com usuário que possa votar?','delibera') , admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>';
                    $defaults['comment_notes_after'] = "";
                    $defaults['label_submit'] = "";
                    $defaults['id_submit'] = "botao-oculto";
                }
                if(has_filter('delibera_discussao_comment_form'))
                {
                    $defaults = apply_filters('delibera_discussao_comment_form', $defaults, $situacao->slug);
                }
            }break;
            case 'emvotacao':
            {
                $user_comments = delibera_get_comments($post->ID, 'voto', array('user_id' => $current_user->ID));
                $temvoto = false;
                foreach ($user_comments as $user_comment)
                {
                    if(get_comment_meta($user_comment->comment_ID, 'delibera_comment_tipo', true) == 'voto')
                    {
                        $temvoto = true;
                        break;
                    }
                }
                if($temvoto)
                {
                    $defaults['comment_notes_after'] = '
                        <script type="text/javascript">
                            var formdiv = document.getElementById("respond");
                            formdiv.style.display = "none";
                        </script>
                    ';
                }
                else
                {
                    $defaults['title_reply'] = sprintf(__('Regime de votação para a pauta "%s"','delibera'),$post->post_title);
                    $defaults['must_log_in'] = sprintf(__('Você precisar <a href="%s">estar logado</a> e ter permissão para votar.'),wp_login_url( apply_filters( 'the_permalink', get_permalink( $post->ID ))));
                    $encaminhamentos = array();
                    if (delibera_current_user_can_participate()) {
                        $form = '<div class="delibera_checkbox_voto">';
                        $encaminhamentos = delibera_get_comments_encaminhamentos($post->ID);
                        
                        $form .= '<div class="instrucoes-votacao">'.__('Escolha os encaminhamentos que deseja aprovar e depois clique em "Votar":','delibera').'</div>';
                        
                        $i = 0;
                        foreach ($encaminhamentos as $encaminhamento)
                        {
                            $form .= '
                                <div class="checkbox-voto"><input type="checkbox" name="delibera_voto'.$i.'" id="delibera_voto'.$i.'" value="'.$encaminhamento->comment_ID.'" /><label for="delibera_voto'.$i++.'" class="label-voto">'.$encaminhamento->comment_content.'</label></div> 
                            ';
                        }
                        $form .= '
                                <input name="delibera_comment_tipo" value="voto" style="display:none;" />
                                <input name="comment" value="O voto de '.$current_user->display_name.' foi registrado no sistema" style="display:none;" />
                            </div>'
                        ;
                        
                        $defaults['comment_field'] = $form;
                        $defaults['logged_in_as'] = "";
                        $defaults['label_submit'] = __('Votar','delibera');
                        $defaults['comment_notes_after'] = '<div class="delibera_comment_button">';;
                        $comment_footer = "</div>";
                    } else {
                        $defaults['comment_field'] = "";
                        $defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf( __('Você está logado como <a href="%1$s">%2$s</a> que não é um usuário autorizado a votar. <a href="%3$s" title="Sair desta conta?">Sair desta conta</a> e logar com um usuário com permisão para votar?','delibera') , admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>';
                        $defaults['comment_notes_after'] = "";
                        $defaults['label_submit'] = "";
                        $defaults['id_submit'] = "botao-oculto";
                    }
                }
                if(has_filter('delibera_resolucoes_comment_form'))
                {
                    $defaults = apply_filters('delibera_resolucoes_comment_form', $defaults, $temvoto, $encaminhamentos);
                }
            } break;
            case 'comresolucao':
            {
                $defaults['comment_notes_after'] = '<script type="text/javascript">
                    var formdiv = document.getElementById("respond");
                    formdiv.style.display = "none";
                </script>';
                if(has_filter('delibera_comresolucao_comment_form'))
                {
                    $defaults = apply_filters('delibera_comresolucao_comment_form', $defaults);
                }
            }break;
        }
        if(!is_user_logged_in())
        {
            $defaults['comment_notes_before'] = '<script type="text/javascript">
                    var formdiv = document.getElementById("respond");
                    formdiv.style.display = "none";
            </script>';
        }
    }
    return $defaults;   
}
add_filter('comment_form_defaults', 'delibera_comment_form');

add_action('wp_enqueue_scripts', function()
{
    if (get_post_type() == 'pauta')
    {
    	global $deliberaThemes, $post;
    	
    	$situacao = delibera_get_situacao($post->ID);
        wp_enqueue_script('atenas', $deliberaThemes->getThemeUrl() . '/js/atenas.js', array('jquery'));
        
        if ($situacao->slug == 'relatoria')
        {
            wp_enqueue_script('delibera_relatoria_js', WP_CONTENT_URL . '/plugins/delibera/js/delibera_relatoria.js', array('jquery'));
        }
    }
});

/**
 * Gera código html para criação do botão seguir do sistema delibera
 *
 * @param $ID post_ID
 * @return string
 */
function delibera_gerar_seguir($ID)
{
    if(is_user_logged_in())
    {
        global $post;

        if(is_object($ID))
        {
            $ID = $ID->ID;
        }

        $user_id = get_current_user_id();
        $situacao = is_object($post) ? delibera_get_situacao($post->ID) : '';

        $seguir = false;
        if(!delibera_ja_seguiu($ID, $user_id) && (is_object($situacao) && $situacao->slug != 'relatoria'))
        {
            $seguir = true;
        }

        $html = '<div id="delibera_seguir" class="delibera_seguir">
            <span id="delibera-seguir-text" class="delibera_seguir_text" ' . (($seguir == false) ? 'style="display: none;"' : '') . '>' . __('Seguir','delibera') . '</span>'
            . '<span id="delibera-seguindo-text" class="delibera_seguir_text" ' . (($seguir == true) ? 'style="display: none;"' : '') . '>' . __('Seguindo','delibera') . '<span class="delibera_seguir_cancel">&nbsp;('.__('Cancelar', 'delibera').')</span></span>'
            . '</div>';
        
        return $html;
    }
    else
    {
        $html = '<div id="delibera_seguir" class="delibera_seguir" ><a class="delibera-seguir-login" href="';
        $html .= wp_login_url( get_post_type() == "pauta" ? get_permalink() : delibera_get_comment_link());
        $html .= '" ><span class="delibera_seguir_text">'.__('Seguir','delibera').'</span></a></div>';
        return $html;
    }
}

/**
 * Gera código html para criação do botão curtir/concordar do sistema delibera
 * 
 * @param $ID post_ID ou comment_ID
 * @param $type 'pauta' ou 'comment'
 */
function delibera_gerar_curtir($ID, $type ='pauta')
{
    global $post;
    
    $situacoes_validas = array('validacao' => false, 'discussao' => true, 'emvotacao' => false, 'comresolucao' => true);
    
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
    
    $ncurtiu = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_curtir', true) : get_comment_meta($ID, 'delibera_numero_curtir', true));
    $ndiscordou = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_discordar', true) : get_comment_meta($ID, 'delibera_numero_discordar', true));
    $situacao = delibera_get_situacao($postID);

    if ($ncurtiu > 0) {
        $html = '<span class="delibera-like-count">' . "$ncurtiu " . ($ncurtiu > 1 ? __('concordaram', 'delibera') : __('concordou', 'delibera')) . '</span>';
    } else {
        $html = '<span class="delibera-like-count" style="display: none;"></span>';
    }

    if ($ndiscordou > 0) {
        $html .= '<span class="delibera-unlike-count">' . "$ndiscordou " . ($ndiscordou > 1 ? __('discordaram', 'delibera') : __('discordou', 'delibera')) . '</span>';
    } else {
        $html .= '<span class="delibera-unlike-count" style="display: none;"></span>';
    }
    
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if(
            !delibera_ja_curtiu($ID, $user_id, $ip, $type) && // Ainda não curitu
            (is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas)) && $situacoes_validas[$situacao->slug] && // é uma situação válida
            !(delibera_ja_discordou($ID, $user_id, $ip, $type)) // não discordou
        )
        {
            $html .= '<div id="thebutton'.$type.$ID.'" class="delibera_like" ><span class="delibera_like_text">'.__('Concordo','delibera').'</span>';
            $html .= "<input type='hidden' name='object_id' value='{$ID}' />";
            $html .= "<input type='hidden' name='type' value='{$type}' />";
            $html .= '</div>';
        }
    } else {
        $html .= '<div id="thebutton'.$type.$ID.'" class="delibera_like" >';
        if (is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas) && $situacoes_validas[$situacao->slug]) { // é uma situação válida
            $html .= '<a class="delibera-like-login" href="';
            $html .= wp_login_url( $type == "pauta" ? get_permalink() : delibera_get_comment_link());
            $html .= '" ><span class="delibera_like_text">'.__('Concordo','delibera').'</span></a>';
        }
        $html .= '</div>';
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
    
    $situacoes_validas = array('validacao' => false, 'discussao' => true, 'emvotacao' => false, 'comresolucao' => true);
    
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
    
    if(is_user_logged_in())
    {
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if(
            !delibera_ja_discordou($ID, $user_id, $ip, $type) && // Ainda não curitu
            (is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas)) && $situacoes_validas[$situacao->slug] &&// é uma situação válida
            !(delibera_ja_curtiu($ID, $user_id, $ip, $type)) // não discordou
        )
        {
            $html = '<div id="thebuttonDiscordo'.$type.$ID.'" class="delibera_unlike" ><span class="delibera_unlike_text">'.__('Discordo','delibera').'</span>';
            $html .= "<input type='hidden' name='object_id' value='{$ID}' />";
            $html .= "<input type='hidden' name='type' value='{$type}' />";
            $html .= '</div>';
            
            return $html;
        }
    }
    else 
    {
        $html = '<div id="thebuttonDiscordo'.$type.$ID.'" class="delibera_unlike" >';
        if(is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas) && $situacoes_validas[$situacao->slug]) // é uma situação válida
        {
            $html .= '<a class="delibera-unlike-login" href="';
            $html .= wp_login_url( $type == "pauta" ? get_permalink() : delibera_get_comment_link());
            $html .= '" ><span class="delibera_unlike_text">'.__('Discordo','delibera').'</span></a>';
        }

        return $html;
    }
}
