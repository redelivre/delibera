<?php

/**
 * Retorna todas as configurações do delibera
 * salvas no banco. Quando não houver um valor 
 * salvo no banco para determinada opções retorna o
 * valor padrão.
 * 
 * @return array
 */
function delibera_get_config() {
    $opt = array();
    
    $opt = apply_filters('delibera_get_config', $opt);
    
    $opt_conf = get_option('delibera-config', array());
    
    if(!is_array($opt_conf))
    {
    	$opt_conf = array();
    }
    if(!is_array($opt))
    {
    	$opt = array();
    }

    $opt = array_merge($opt, $opt_conf);
    
    return $opt;
}

function delibera_get_main_config($config = array()) {
    global $deliberaThemes;
    
    if(!is_object($deliberaThemes)) $deliberaThemes = new DeliberaThemes;
    
    $opt = array();
    $opt['theme'] = $deliberaThemes->getThemeDir('creta');
    $opt['minimo_validacao'] = '10';
    $opt['dias_validacao'] = '5';
    $opt['dias_discussao'] = '5';
    $opt['dias_votacao'] = '5';
    $opt['criar_pauta_pelo_front_end'] = 'N';
    $opt['representante_define_prazos'] = 'N';
    $opt['pauta_suporta_encaminhamento'] = 'S';
    $opt['dias_novo_prazo'] = '2';
    $opt['validacao'] = 'S';
    $opt['dias_relatoria'] = '2';
    $opt['relatoria'] = 'N';
    $opt['eleicao_relator'] = 'N';
    $opt['dias_votacao_relator'] = '2';
    $opt['limitar_tamanho_comentario'] = 'N';
    $opt['numero_max_palavras_comentario'] = '50';
    $opt['plan_restriction'] = 'N';
    $opt['cabecalho_arquivo'] = __( 'Bem-vindo a plataforma de debate do ', 'delibera' ).get_bloginfo('name');
    $opt['todos_usuarios_logados_podem_participar'] = 'N';
	$opt['data_fixa_nova_pauta_externa'] = '';
    
    return array_merge($opt, $config);
}
add_filter('delibera_get_config', 'delibera_get_main_config');

/**
 * Gera o HTML da página de configuração
 * do Delibera
 * 
 * @return null
 */
function delibera_conf_page()
{
    global $deliberaThemes;
    
    $mensagem = '';

    if ($_SERVER['REQUEST_METHOD']=='POST') {
        $opt = delibera_get_config();
        
        if (!current_user_can('manage_options')) {
            die(__('Você não pode editar as configurações do delibera.','delibera'));
        }
        
        check_admin_referer('delibera-config');
        	
        foreach (array_keys(delibera_get_main_config()) as $option_name) {
            if (isset($_POST[$option_name])) {
                $opt[$option_name] = htmlspecialchars($_POST[$option_name]);
            } else {
                $opt[$option_name] = "N";
            }
        }

        if (isset($_POST["delibera_reinstall"]) && $_POST['delibera_reinstall'] == 'S') {
            try {
                include_once __DIR__.DIRECTORY_SEPARATOR.'delibera_reinstall.php';
            } catch (Exception $e) {
                wp_die($e->getMessage());
            }
        }
        
        // atualiza os permalinks por conta da opção "criar_pauta_pelo_front_end"
        flush_rewrite_rules();

        if (update_option('delibera-config', $opt) || (isset($_POST["delibera_reinstall"]) && $_POST['delibera_reinstall'] == 'S'))
            $mensagem = __('Configurações salvas!','delibera');
        else
            $mensagem = __('Erro ao salvar as configurações. Verifique os valores inseridos e tente novamente!','delibera');
    }

    $opt = delibera_get_config();
    ?>
		
<div class="wrap">
<h2>Configurações gerais</h2>
<div class="postbox-container" style="width:80%;">
	<div class="metabox-holder">	
		<div class="meta-box-sortables">
			<?php if ($mensagem) {?>
			<div id="message" class="updated">
			<?php echo $mensagem; ?>
			</div>
			<?php }?>
			<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" id="delibera-config" >
			<?php if (function_exists('wp_nonce_field')) 		
					wp_nonce_field('delibera-config');
						
				$rows = array();
				if(is_multisite() && get_current_blog_id() == 1)
				{
					$rows[] = array(
						"id" => "plan_restriction",
						"label" => __('Sistema de planos de pagamento ativo?', 'delibera'),
						"content" => '<input type="checkbox" name="plan_restriction" id="plan_restriction" value="S" '. ( htmlspecialchars_decode($opt['plan_restriction']) == "S" ? "checked='checked'" : "" ).'/>',
					);
				}
                $rows[] = array(
                    "id" => "theme",
                    "label" => __('Tema', 'delibera'),
                    "content" => $deliberaThemes->getSelectBox($opt['theme']) . '<p class="description">' . __('É possível criar um tema para o Delibera criando uma pasta com o nome "delibera" dentro da pasta do tema atual do Wordpress. Esse tema aparecerá nesta listagem com o nome do tema atual.', 'delibera'). '</p>',
                );
				$rows[] = array(
					"id" => "criar_pauta_pelo_front_end",
					"label" => __('Habilitar a criação de pautas pelo front-end?', 'delibera'),
					"content" => '<input type="checkbox" name="criar_pauta_pelo_front_end" id="criar_pauta_pelo_front_end" value="S" '. ( htmlspecialchars_decode($opt['criar_pauta_pelo_front_end']) == "S" ? "checked='checked'" : "" ).'/>',
				);
				$rows[] = array(
					"id" => "representante_define_prazos",
					"label" => __('Representante define prazos?', 'delibera'),
					"content" => '<input type="checkbox" name="representante_define_prazos" id="representante_define_prazos" value="S" '. ( htmlspecialchars_decode($opt['representante_define_prazos']) == "S" ? "checked='checked'" : "" ).'/>',
				);
                $rows[] = array(
                    "id" => "pauta_suporta_encaminhamento",
                    "label" => __('Pautas suportam sugestão de encaminhamento?', 'delibera'),
                    "content" => '<input type="checkbox" name="pauta_suporta_encaminhamento" id="pauta_suporta_encaminhamento" value="S" '. ( htmlspecialchars_decode($opt['pauta_suporta_encaminhamento']) == "S" ? "checked='checked'" : "" ).'/>',
                );
				$rows[] = array(
					"id" => "validacao",
					"label" => __('É necessário validação das pautas?', 'delibera'),
					"content" => '<input type="checkbox" name="validacao" id="validacao" value="S" '.(htmlspecialchars_decode($opt['validacao']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "minimo_validacao",
					"label" => __('Mínimo de validações para uma pauta:', 'delibera'),
					"content" => '<input type="text" name="minimo_validacao" id="minimo_validacao" value="'.htmlspecialchars_decode($opt['minimo_validacao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_validacao",
					"label" => __('Dias para validação da pauta:', 'delibera'),
					"content" => '<input type="text" name="dias_validacao" id="dias_validacao" value="'.htmlspecialchars_decode($opt['dias_validacao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_discussao",
					"label" => __('Dias para discussão da pauta:', 'delibera'),
					"content" => '<input type="text" name="dias_discussao" id="dias_discussao" value="'.htmlspecialchars_decode($opt['dias_discussao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_votacao",
					"label" => __('Dias para votação de encaminhamentos:', 'delibera'),
					"content" => '<input type="text" name="dias_votacao" id="dias_votacao" value="'.htmlspecialchars_decode($opt['dias_votacao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_novo_prazo",
					"label" => __('Dias para novo prazo:', 'delibera'),
					"content" => '<input type="text" name="dias_novo_prazo" id="dias_novo_prazo" value="'.htmlspecialchars_decode($opt['dias_novo_prazo']).'"/><p class="description">' . __('Utilizado para as pautas em discussão, em relatoria e em votação para dar mais alguns dias quando uma condição mínima não é atingida até o fim do prazo. Por exemplo, quando acaba o prazo de uma pauta em discussão ou relatoria e ela não tem nenhuma proposta de encaminhamento. Utilizado também quando uma pauta não validada é reaberta.', 'delibera'). '</p>'
				);
				$rows[] = array(
					"id" => "relatoria",
					"label" => __('Necessário relatoria da discussão das pautas?', 'delibera'),
					"content" => '<input type="checkbox" id="relatoria" name="relatoria" value="S" '.(htmlspecialchars_decode($opt['relatoria']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "dias_relatoria",
					"label" => __('Prazo para relatoria:', 'delibera'),
					"content" => '<input type="text" name="dias_relatoria" id="dias_relatoria" value="'.htmlspecialchars_decode($opt['dias_relatoria']).'"/>'
				);
				/*$rows[] = array(
					"id" => "eleicao_relator",
					"label" => __('Necessário eleição de relator?', 'delibera'),
					"content" => '<input type="checkbox" name="eleicao_relator" value="S" '.(htmlspecialchars_decode($opt['eleicao_relator']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "dias_votacao_relator",
					"label" => __('Prazo para eleição de relator:', 'delibera'),
					"content" => '<input type="text" name="dias_votacao_relator" id="dias_votacao_relator" value="'.htmlspecialchars_decode($opt['dias_votacao_relator']).'"/>'
				);*/
				$rows[] = array(
					"id" => "limitar_tamanho_comentario",
					"label" => __('Limitar o tamanho do comentário visível?', 'delibera'),
					"content" => '<input type="checkbox" name="limitar_tamanho_comentario" id="limitar_tamanho_comentario" value="S" '.(htmlspecialchars_decode($opt['limitar_tamanho_comentario']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "numero_max_palavras_comentario",
					"label" => __('Número máximo de caracteres exibidos por comentário:', 'delibera'),
					"content" => '<input type="text" name="numero_max_palavras_comentario" id="numero_max_palavras_comentario" value="'.htmlspecialchars_decode($opt['numero_max_palavras_comentario']).'"/>'
				);
				$rows[] = array(
					"id" => "cabecalho_arquivo",
					"label" => __('Título da página de listagem de pautas e da página de uma pauta:', 'delibera'),
					"content" => '<input type="text" name="cabecalho_arquivo" id="cabecalho_arquivo" value="'.htmlspecialchars_decode($opt['cabecalho_arquivo']).'"/>'
				);
				$rows[] = array(
					"id" => "data_fixa_nova_pauta_externa",
					"label" => __('Data fixa para pauta externa:', 'delibera'),
					"content" => '<input type="text" name="data_fixa_nova_pauta_externa" id="data_fixa_nova_pauta_externa" value="'.htmlspecialchars_decode($opt['data_fixa_nova_pauta_externa']).'"/>'
				);
				if (is_multisite()) {
					$rows[] = array(
						"id" => "todos_usuarios_logados_podem_participar",
						"label" => __('Todos os usuários logados na rede podem participar?', 'delibera'),
						"content" => '<input type="checkbox" name="todos_usuarios_logados_podem_participar" id="todos_usuarios_logados_podem_participar" value="S" '.(htmlspecialchars_decode($opt['todos_usuarios_logados_podem_participar']) == 'S' ? 'checked="checked"' : '').' /><p class="description">' . __('Se essa opção estiver habilitada qualquer usuário logado da rede de sites poderá participar discutindo e votando nas pautas. Caso contrário a participação fica restrita aos usuários deste site.', 'delibera'). '</p>'
					);
				}
				$table = delibera_form_table($rows);
				if(has_filter('delibera_config_form'))
				{
					$table = apply_filters('delibera_config_form', $table, $opt);
				}
				echo $table.'<div class="submit"><input type="submit" class="button-primary" name="submit" value="'.__('Save Changes').'" /></form></div>';
			?>
				
				</form>
			</div> <!-- meta-box-sortables -->
		</div> <!-- meta-box-holder -->
	</div> <!-- postbox-container -->

	<?php do_action('delibera_config_page_extra');?>
	
</div>

<?php	

}
