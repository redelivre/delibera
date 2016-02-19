<?php

function delibera_pauta_redirect_filter($location, $post_id = null) {

	if (strpos($_SERVER['HTTP_REFERER'], "post_type=pauta"))
		return admin_url("edit.php")."?post_type=pauta&updated=1";
	else
		return $location;
}
add_filter('redirect_post_location', 'delibera_pauta_redirect_filter', '99');

function delibera_pauta_custom_meta()
{
	add_meta_box("pauta_meta", "Detalhes da Pauta", 'delibera_pauta_meta', 'pauta', 'side', 'default');
}

/**
 *
 * Retorna a situação do post
 * @param int $postID
 * @return mixed term name in taxonomy situacao or false
 */
function delibera_get_situacao($postID)
{
	$situacao = get_the_terms($postID, 'situacao');
	$ret = false;
	if(is_array($situacao) && count($situacao)  > 0)
	{
		$ret = array_pop($situacao);
	}

	if(!is_object($ret)) // if term situacao does not exists
	{
		$ret = new stdClass();
		$ret->slug = '';
		$ret->name = '';
	}

	if(has_filter('delibera_get_situacao'))
	{
		return apply_filters('delibera_get_situacao', $ret);
	}

	return $ret;
}

/**
 * Retorna o label do botão com a situação da
 * pauta.
 *
 * @param int $postId
 * @return string
 */
function delibera_get_situation_button($postId)
{
    $situacao = get_the_terms($postId, 'situacao');

    if (is_array($situacao) && !empty($situacao))
    {
        $situacao = array_pop($situacao);
    }
    return apply_filters('delibera_situation_button_text', $situacao->slug);
}

function delibera_update_edit_form() {
    echo ' enctype="multipart/form-data"';
} // end update_edit_form
add_action('post_edit_form_tag', 'delibera_update_edit_form');

function delibera_pauta_meta()
{
	global $post;

	$custom = get_post_custom($post->ID);
	$options_plugin_delibera = delibera_get_config();

	if(!is_array($custom)) $custom = array();
	$situacao = delibera_get_situacao($post->ID);

    $pauta_pdf_file = get_post_meta($post->ID, 'pauta_pdf_contribution', true);

    // Recupera arquivo caso já tenha sido adicionados
    $pdf_html  = "<p><label>Pauta em PDF</label>";
    if( $pauta_pdf_file ) {
        $pdf_html .= "<a href='" . $pauta_pdf_file . "' target='_blank'>Arquivo Atual</a><br/>";
    }
    $pdf_html .= "<input type='file' name='pauta_pdf_contribution' id='pauta_pdf_contribution' value='' size='25'/></p>";
    echo $pdf_html;

	if($options_plugin_delibera['validacao'] == "S") // Adiciona prazo de validação se for necessário
	{
		//TODO adicionar modulo anterior ao prazo $dias_discussao += $dias_validacao;
	}

	$now = strtotime(date('Y/m/d')." 11:59:59");

	if (
		$options_plugin_delibera['representante_define_prazos'] == "N" &&
		!($post->post_status == 'draft' ||
		$post->post_status == 'auto-draft' ||
		$post->post_status == 'pending')
	)
	{
		$disable_edicao = 'readonly="readonly"';
	} else {
	    $disable_edicao = '';
	}

	do_action('delibera_topic_meta', $post, $custom, $options_plugin_delibera, $situacao, $disable_edicao);
	
}


function delibera_publish_pauta($postID, $post, $alterar = false)
{
	if(get_post_type( $postID ) != "pauta")
	{
		return $postID;
	}

	if (
			$alterar ||	(
				($post->post_status == 'publish' || $_POST['publish'] == 'Publicar') &&
					(
						(
							array_key_exists('prev_status', $_POST) &&
							(
								$_POST['prev_status'] == 'draft' ||
								$_POST['prev_status'] == 'pending'
							)
						) ||
						(
							array_key_exists('original_post_status', $_POST) && (
									$_POST['original_post_status'] == 'draft' ||
									$_POST['original_post_status'] == 'auto-draft' ||
									$_POST['original_post_status'] == 'pending')
						)
					)
			)
		)
	{
		$opt = delibera_get_config();
		
		do_action('delibera_publish_pauta', $postID, $opt, $alterar);
		
		$curtir = get_post_meta($post_id, 'delibera_numero_curtir', true);
		if($curtir == "" || $curtir === false || is_null($curtir))
		{
			$events_meta['delibera_numero_comments_padroes'] = 0;
			$events_meta['delibera_numero_curtir'] = 0;
			$events_meta['delibera_curtiram'] = array();
			$events_meta['delibera_numero_discordar'] = 0;
			$events_meta['delibera_discordaram'] = array();
			$events_meta['delibera_numero_seguir'] = 0;
			$events_meta['delibera_seguiram'] = array();
		}

		if($alterar)
		{
			//delibera_notificar_situacao($post);
		}
		else
		{
			delibera_notificar_nova_pauta($post);
		}
	}
}

add_action ('publish_pauta', 'delibera_publish_pauta', 1, 2);

/**
 * 
 * @param unknown $data
 * @param unknown $postarr
 */
function delibera_check_post_data($data, $postarr)
{
	$opt = delibera_get_config();
	$erros = array();
	$autosave = ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE );
	if(get_post_type() == 'pauta' && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'trash'))
	{
		
		$erros == apply_filters('delibera_check_post_data', $erros, $opt, $autosave);
		
		if(
			count($erros) == 0
		)
		{
			return $data;
		}
		else
		{
			//wp_die(__('Erro ao salvar dados da pauta, faltando informações de prazos e validações mínimas!','delibera'));
			wp_die(implode("<BR/>", $erros));
		}
	}
	return $data;
}

add_filter('wp_insert_post_data', 'delibera_check_post_data', 10, 2);

/**
 *
 * Retorna post do tipo pauta em uma determinada situacao, usando um filtro
 * @param array $filtro
 * @param string $situacao
 * 
 * @return array of posts of type pauta
 * 
 */
function delibera_get_pautas_em($filtro = array(), $situacao = false)
{
	$filtro['post_type'] = "pauta";
	$filtro['post_status'] = "publish";
	$tax_query = array();

	if(array_key_exists("tax_query", $filtro) && $situacao !== false)
	{
		$tax_query = $filtro['tax_query'];
		$tax_query['relation'] = 'AND';
	}
	if($situacao !== false)
	{
		$tax_query[] = array(
			'taxonomy' => 'situacao',
			'field' => 'slug',
			'terms' => $situacao
		);
		$filtro['tax_query'] = $tax_query;
	}
	return get_posts($filtro);
}

function delibera_des_filtro_qtranslate($where)
{
	if(is_archive())
	{
		global $q_config, $wpdb;
		if($q_config['hide_untranslated'] && !is_singular()) {
			$where = str_replace(" AND $wpdb->posts.post_content LIKE '%<!--:".qtrans_getLanguage()."-->%'", '', $where);
		}
	}
	return $where;
}

add_filter('posts_where_request', 'delibera_des_filtro_qtranslate', 11);

/**
 *
 * Retorna pautas já resolvidas
 * @param array $filtro
 */
function delibera_get_resolucoes($filtro = array())
{
	return delibera_get_pautas_em($filtro, 'comresolucao');
}

/**
 *
 * Save o post da pauta
 * @param $post_id int
 * @param $post
 */
function delibera_save_post($post_id, $post)
{
    if(get_post_type( $post_id ) != "pauta")
	{
		return $post_id;
	}
	$opt = delibera_get_config();
	$autosave = ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE );
	
	$events_meta = array();
	
		
	
	/* ######### START ######### */
	/* ######### FOR PDF UPLOAD FILE ######### */
	// Setup the array of supported file types. In this case, it's just PDF.
	$supported_types = array('application/pdf');

	// Get the file type of the upload
	$arr_uploaded_file_type = wp_check_filetype(basename($_FILES['pauta_pdf_contribution']['name']));
	$uploaded_file_type = $arr_uploaded_file_type['type'];

        if (isset ($_FILES['pauta_pdf_contribution']['name']) && $_FILES['pauta_pdf_contribution']['name'] != '') {
            if (!in_array($uploaded_file_type, $supported_types)) {
                //TODO: Improve this message and avoid wp_die
                wp_die("O arquivo para web não é um PDF (formato permitido).");
            }


            // Use the WordPress API to upload the file
            $upload_pauta_pdf = wp_upload_bits($_FILES['pauta_pdf_contribution']['name'], null, file_get_contents($_FILES['pauta_pdf_contribution']['tmp_name']));

            if (isset($upload_pauta_pdf['error']) && $upload_pauta_pdf['error'] != 0) {
                $events_meta['pauta_pdf_contribution'] = none;
                wp_die('Erro ao salvar arquivo para Web. O erro foi: ' . $upload_pauta_pdf['error']);
            } else {
                $events_meta['pauta_pdf_contribution'] = $upload_pauta_pdf['url'];

                global $wpdb;

                $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_content=%s WHERE ID=%d", '<iframe id="pauta-pdf-content" src="https://docs.google.com/viewer?url=' . urlencode($upload_pauta_pdf['url']) . '&amp;embedded=true" style="width: 100%; min-height: 400px; max-height: 800px; ">' . $upload_pauta_pdf['url'] . '</iframe>', $post->ID));
            }
        }
        /* ######### FOR PDF UPLOAD FILE ######### */
        /* ######### END ######### */
	        
	$events_meta = apply_filters('delibera_save_post_metas', $events_meta, $opt);

	foreach ($events_meta as $key => $value) // Buscar dados
	{
		if(get_post_meta($post->ID, $key, true)) // Se já existe
		{
			update_post_meta($post->ID, $key, $value); // Atualiza
		}
		else
		{
			add_post_meta($post->ID, $key, $value, true); // Senão, cria
		}
	}

	do_action('delibera_save_post', $post_id, $post, $opt);
	
    if(
    	array_key_exists('delibera_fim_prazo', $_POST) &&
    	$_POST['delibera_fim_prazo'] == 'S' &&
    	current_user_can('forcar_prazo')
    )
    {
    	delibera_forca_fim_prazo($post->ID);
    }

	if($post->post_status == 'publish' && !$autosave)
	{
		delibera_del_cron($post->ID);
		delibera_publish_pauta($post->ID, $post, true);
	}
}

add_action ('save_post', 'delibera_save_post', 1, 2);

/***
 * Verifica se as pautas devem suportar sugestão de encaminhamento ou se
 * as propostas entram apenas como opinião. Muito útil para consultas públicas.
 *
 * @return bool
 */
function delibera_pautas_suportam_encaminhamento()
{
    $options = delibera_get_config();

    if ( $options['pauta_suporta_encaminhamento'] == 'S' ) {
        return true;
    } else {
        return false;
    }
}

add_action('init', 'delibera_nova_pauta_create_action');
function delibera_nova_pauta_create_action(){
    $opt = delibera_get_config();
    if ($opt['criar_pauta_pelo_front_end'] == 'S' && is_user_logged_in() && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'delibera_nova_pauta')) {
        $title = $_POST['nova-pauta-titulo'];
        $content = $_POST['nova-pauta-conteudo'];
        $excerpt = $_POST['nova-pauta-resumo'];

        $pauta = array();
        $pauta['post_title'] = $title;
        $pauta['post_excerpt'] = $excerpt;
        $pauta['post_type'] = 'pauta';

        //Check if there is any file uploaded
        // If there is any, then ignore 'content' and use File.
        // else do add 'pauta' with the text content
        if(!empty($_FILES['post_pdf_contribution']['name'])) {
            // Setup the array of supported file types. In this case, it's just PDF.
            $supported_types = array('application/pdf');
            // Get the file type of the upload
            $pdf_contribution = wp_check_filetype(basename($_FILES['post_pdf_contribution']['name']));
            $sent_file_type = $pdf_contribution['type'];
            // Check if the type is supported. If not, throw an error.
            if (!in_array($sent_file_type, $supported_types)) {
                //TODO: Improve this message and avoid wp_die
                wp_die("O arquivo para web não é um PDF (formato permitido).");
            }
            $uploaded_file = wp_upload_bits($_FILES['pauta_pdf_contribution']['name'], null, file_get_contents($_FILES['pauta_pdf_contribution']['tmp_name']));
            if(isset($uploaded_file['error']) && $uploaded_file['error'] != 0) {
                wp_die('Erro ao salvar arquivo para Web. O erro foi: ' . $upload['error']);
            } else {
                $pauta['pauta_pdf_contribution'] = $uploaded_file['url'];
            }
        } else {
            $pauta['post_content'] = $content;
        }

        // para que a situação da pauta seja criada corretamente,
        // é necessário criar a pauta como rascunho para depois publicar no final desta função
        $pauta['post_status'] = 'draft';

        $pauta_id = wp_insert_post($pauta);

        if(is_int($pauta_id) && $pauta_id > 0){

        	do_action('delibera_create_pauta_frontend', $opt);
        
            // isto é necessário por causa do if da função delibera_publish_pauta()
            $_POST['publish'] = 'Publicar';
            $_POST['prev_status'] = 'draft';

            // verifica se todos os temas enviados por post são válidos
            $temas = get_terms('tema', array('hide_empty'    => true));
            $temas_ids = array();

            if(isset($_POST['tema']) && is_array($_POST['tema']))
                foreach($temas as $tema)
                    if(in_array ($tema->term_id, $_POST['tema']))
                        $temas_ids[] = $tema->term_id;

               // coloca  o s termos de temas no post
              wp_set_post_terms($pauta_id, $temas_ids, 'tema');

               // publica o post
              wp_publish_post($pauta_id);

               // isto serve para criar o slug corretamente,
                // já que no wp _ insert_post não cria o slug quando o status é draft e o wp_publish_post tb não cria o slug
              unset($pauta['post_status']);
              $pauta['ID'] = $pauta_id;
              $pauta['post_name'] = sanitize_post_field('post_name', $title, $pauta_id, 'save');
              wp_update_post($pauta);

               // redireciona para a pauta criada
              $permalink = get_post_permalink($pauta_id);
              wp_safe_redirect($permalink);
              die;
          }
      }
 }
