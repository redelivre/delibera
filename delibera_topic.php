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
	add_meta_box("pauta_meta", __("Detalhes da Pauta", 'delibera'), 'delibera_pauta_meta', 'pauta', 'normal', 'high');
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


function delibera_publish_pauta($post)
{
	$postID = $post->ID;
	if(get_post_type( $postID ) != "pauta")
	{
		return $postID;
	}
	
	$opt = delibera_get_config();
	
	do_action('delibera_publish_pauta', $postID, $opt);
	
	$curtir = get_post_meta($postID, 'delibera_numero_curtir', true);
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

	delibera_notificar_nova_pauta($postID);
}
add_action ('draft_to_publish', 'delibera_publish_pauta', 1, 1);
add_action ('pending_to_publish', 'delibera_publish_pauta', 1, 1);
add_action ('auto-draft_to_publish', 'delibera_publish_pauta', 1, 1);
add_action ('new_to_publish', 'delibera_publish_pauta', 1, 1);

/**
 * 
 * @param unknown $data
 * @param unknown $postarr
 */
function delibera_check_post_data($data, $postarr)
{
	$opt = delibera_get_config();
	$errors = array();
	$autosave = ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE );
	if(get_post_type() == 'pauta' && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'trash'))
	{
		
		$errors = apply_filters('delibera_check_post_data', $errors, $opt, $autosave);
		
		if(count($errors) == 0)
		{
			return $data;
		}
		else
		{
			//wp_die(__('Erro ao salvar dados da pauta, faltando informações de prazos e validações mínimas!','delibera'));
			wp_die(implode("<BR/>", $errors));
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

	if (! $autosave && array_key_exists('pauta_pdf_contribution', $_FILES))
	{
		// Get the file type of the upload
		$arr_uploaded_file_type = wp_check_filetype ( basename ( $_FILES ['pauta_pdf_contribution'] ['name'] ) );
		$uploaded_file_type = $arr_uploaded_file_type ['type'];
		
		if (isset ( $_FILES ['pauta_pdf_contribution'] ['name'] ) && $_FILES ['pauta_pdf_contribution'] ['name'] != '') {
			if (! in_array ( $uploaded_file_type, $supported_types )) {
				// TODO: Improve this message and avoid wp_die
				wp_die ( "O arquivo para web não é um PDF (formato permitido)." );
			}
			
			// Use the WordPress API to upload the file
			$upload_pauta_pdf = wp_upload_bits ( $_FILES ['pauta_pdf_contribution'] ['name'], null, file_get_contents ( $_FILES ['pauta_pdf_contribution'] ['tmp_name'] ) );
			
			if (isset ( $upload_pauta_pdf ['error'] ) && $upload_pauta_pdf ['error'] != 0) {
				$events_meta ['pauta_pdf_contribution'] = none;
				wp_die ( 'Erro ao salvar arquivo para Web. O erro foi: ' . $upload_pauta_pdf ['error'] );
			} else {
				$events_meta ['pauta_pdf_contribution'] = $upload_pauta_pdf ['url'];
				
				global $wpdb;
				
				$wpdb->query ( $wpdb->prepare ( "UPDATE " . $wpdb->prefix . "posts SET post_content=%s WHERE ID=%d", '<iframe id="pauta-pdf-content" src="https://docs.google.com/viewer?url=' . urlencode ( $upload_pauta_pdf ['url'] ) . '&amp;embedded=true" style="width: 100%; min-height: 400px; max-height: 800px; ">' . $upload_pauta_pdf ['url'] . '</iframe>', $post->ID ) );
			}
		}
	}
        /* ######### FOR PDF UPLOAD FILE ######### */
        /* ######### END ######### */
	        
	$events_meta = apply_filters('delibera_save_post_metas', $events_meta, $opt, $post_id);

	foreach ($events_meta as $key => $value) // Buscar dados
	{
		update_post_meta($post->ID, $key, $value); // Atualiza
	}

	do_action('delibera_save_post', $post_id, $post, $opt);
	
    if(
    	array_key_exists('delibera_fim_prazo', $_POST) &&
    	$_POST['delibera_fim_prazo'] == 'S' &&
    	current_user_can('forcar_prazo')
    )
    {
    	\Delibera\Flow::forcarFimPrazo($post->ID);
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
function delibera_nova_pauta_create_action()
{
	$opt = delibera_get_config();
	if($opt['criar_pauta_pelo_front_end'] == 'S' && is_user_logged_in() &&
			 isset($_POST['_wpnonce']) &&
			 wp_verify_nonce($_POST['_wpnonce'], 'delibera_nova_pauta'))
	{
		$title = $_POST['nova-pauta-titulo'];
		$content = $_POST['nova-pauta-conteudo'];
		$excerpt = $_POST['nova-pauta-resumo'];
		
		$pauta = array();
		$pauta['post_title'] = $title;
		$pauta['post_excerpt'] = $excerpt;
		$pauta['post_type'] = 'pauta';
		
		// Check if there is any file uploaded
		// If there is any, then ignore 'content' and use File.
		// else do add 'pauta' with the text content
		if(! empty($_FILES['post_pdf_contribution']['name']))
		{
			// Setup the array of supported file types. In this case, it's just
			// PDF.
			$supported_types = array(
				'application/pdf'
			);
			// Get the file type of the upload
			$pdf_contribution = wp_check_filetype(
					basename($_FILES['post_pdf_contribution']['name']));
			$sent_file_type = $pdf_contribution['type'];
			// Check if the type is supported. If not, throw an error.
			if(! in_array($sent_file_type, $supported_types))
			{
				// TODO: Improve this message and avoid wp_die
				wp_die("O arquivo para web não é um PDF (formato permitido).");
			}
			$uploaded_file = wp_upload_bits(
					$_FILES['pauta_pdf_contribution']['name'], null, 
					file_get_contents(
							$_FILES['pauta_pdf_contribution']['tmp_name']));
			if(isset($uploaded_file['error']) && $uploaded_file['error'] != 0)
			{
				wp_die(
						'Erro ao salvar arquivo para Web. O erro foi: ' .
								 $upload['error']);
			}
			else
			{
				$pauta['pauta_pdf_contribution'] = $uploaded_file['url'];
			}
		}
		else
		{
			$pauta['post_content'] = $content;
		}
		
		// para que a situação da pauta seja criada corretamente,
		// é necessário criar a pauta como rascunho para depois publicar no
		// final desta função
		$pauta['post_status'] = 'draft';
		
		$pauta_id = wp_insert_post($pauta);
		
		if(is_int($pauta_id) && $pauta_id > 0)
		{
			
			do_action('delibera_create_pauta_frontend', $opt);
			
			// isto é necessário por causa do if da função
			// delibera_publish_pauta()
			$_POST['publish'] = 'Publicar';
			$_POST['prev_status'] = 'draft';
			
			// verifica se todos os temas enviados por post são válidos
			$temas = get_terms('tema', array(
				'hide_empty' => true
			));
			$temas_ids = array();
			
			if(isset($_POST['tema']) && is_array($_POST['tema']))
				foreach($temas as $tema)
					if(in_array($tema->term_id, $_POST['tema']))
						$temas_ids[] = $tema->term_id;
				
				// coloca o s termos de temas no post
			wp_set_post_terms($pauta_id, $temas_ids, 'tema');
			
			// publica o post
			wp_publish_post($pauta_id);
			
			// isto serve para criar o slug corretamente,
			// já que no wp _ insert_post não cria o slug quando o status é
			// draft e o wp_publish_post tb não cria o slug
			unset($pauta['post_status']);
			$pauta['ID'] = $pauta_id;
			$pauta['post_name'] = sanitize_post_field('post_name', $title, 
					$pauta_id, 'save');
			wp_update_post($pauta);
			
			// redireciona para a pauta criada
			$permalink = get_post_permalink($pauta_id);
			wp_safe_redirect($permalink);
			die();
		}
	}
}

/**
 *
 * @param array $args
 *        	array with:
 *        	post_title,
 *        	post_excerpt,
 *        	post_content,
 *        	redirect (true or false),
 *        	redirect_to (url to redirect or will redirect to new created pauta,
 *        	delibera_flow is a comma separated list of situations, install
 *        	default is: 'validacao,discussao,relatoria,emvotacao,comresolucao'
 *        	modules config, check function savePostMetas on each module will be used, ex:
 *        	prazo_validacao: date format dd/mm/yyyy,
 *        	min_validacoes int ex: 10,
 *        	prazo_discussao: date format dd/mm/yyyy,
 *        	prazo_relatoria: date format dd/mm/yyyy,
 *        	prazo_votacao: date format dd/mm/yyyy,
 *        	
 */
function deliberaCreateTopic($args = array())
{
	$opt = delibera_get_config();
	if($opt['criar_pauta_pelo_front_end'] == 'S' && is_user_logged_in() )
	{
		$defaults = array(
			'post_title' => '',
			'post_excerpt' => '',
			'post_content' => '',
			'redirect' => false,
			'redirect_to' => '',
			'delibera_flow' => $opt['delibera_flow'],
		);
		
		$args = array_merge($defaults, $args);
		if(!is_array($args['delibera_flow'])) $args['delibera_flow'] = explode(',', $args['delibera_flow']);
		
		$title = $args['post_title'];
		$content = $args['post_content'];
		$excerpt = $args['post_excerpt'];
		
		$pauta = array();
		$pauta['post_title'] = $title;
		$pauta['post_excerpt'] = $excerpt;
		$pauta['post_type'] = 'pauta';
		$pauta['post_name'] = sanitize_title($title);
		$pauta['post_content'] = $content;
		
		// para que a situação da pauta seja criada corretamente,
		// é necessário criar a pauta como rascunho para depois publicar no
		// final desta função
		$pauta['post_status'] = 'draft';
		if(array_key_exists('tags_input', $args))
		{
			$pauta['tags_input'] = $args['tags_input'];
		}
		if(array_key_exists('tags_input', $args))
		{
			$pauta['tags_input'] = $args['tags_input'];
		}
		if(array_key_exists('post_category', $args))
		{
			$pauta['post_category'] = $args['post_category'];
		}
		
		// Load defaults modules values at $_POST
		do_action('delibera_create_pauta_frontend', $opt);
		
		// Load args values at $_POST for save_meta action
		foreach (array_diff_key($args, $defaults) as $key => $arg)
		{
			if(array_key_exists($key, $_POST))
			{
				$_POST[$key] = $args[$key];
			}
		}
		$_POST['delibera_flow'] = $args['delibera_flow'];
		$pauta_id = 0;
		
		if(array_key_exists('post_id', $args) && $args['post_id'] > 0)
		{
			$pauta_id = $args['post_id'];
		}
		else 
		{
			$pauta_id = wp_insert_post($pauta);
		}
		
		if(is_int($pauta_id) && $pauta_id > 0)
		{
			// isto é necessário por causa do if da função
			// delibera_publish_pauta()
			$_POST['publish'] = 'Publicar';
			$_POST['prev_status'] = 'draft';
			
			//TODO tratar as categorias e tags
			deliberaAddTerms($pauta_id, $args, 'tema', true);
			
			if(defined('WP_DEBUG') && WP_DEBUG)
			{
				ini_set('display_errors', 1);
				ini_set('display_startup_errors', 1);
				error_reporting(E_ALL & ~E_STRICT);
			}
			
			// publica o post
			wp_publish_post($pauta_id);
			
			// isto serve para criar o slug corretamente,
			// já que no wp _ insert_post não cria o slug quando o status é
			// draft e o wp_publish_post tb não cria o slug
			unset($pauta['post_status']);
			$pauta['ID'] = $pauta_id;
			wp_update_post($pauta);
			
			if(array_key_exists('redirect', $args) && $args['redirect'])
			{
				if(array_key_exists('redirect_to', $args) &&
						 ! empty($args['redirect_to']))
				{
					wp_safe_redirect($args['redirect_to']);
					die();
				}
				else
				{
					// redireciona para a pauta criada
					$permalink = get_post_permalink($pauta_id);
					wp_safe_redirect($permalink);
					die();
				}
			}
			return $pauta_id;	
		}
	}
	elseif($opt['criar_pauta_pelo_front_end'] == 'S')
	{
		wp_die(__('Criação de pauta fora do painel está desabilitada, favor contactar o administrador e pedir sua ativação.', 'delibera'));
	}
}

/**
 * Add terms to a topic/pauta
 * 
 * @param int $pauta_id
 * @param array $args with $taxonomy as key with array ex: $taxonomy = 'category so $args['category'] = array(id1, id2) or $args['post_tags'] = array(id1, id2)
 * @param string $taxonomy like 'tema' or 'category'
 * @param bool $insert insert or not on pauta
 * 
 * @return valids ids
 */
function deliberaAddTerms($pauta_id, $args, $taxonomy = 'tema', $insert = true )
{
	$terms_ids = array();
	
	if(array_key_exists($taxonomy, $args))
	{
		// check array of terms itens
		$itens = $args[$taxonomy];
		if(!is_array($itens) && is_string($itens))
		{
			if(strpos($itens, ',') != false)
			{
				$itens = explode(',', $itens);
			}
			else 
			{
				$itens = array($itens);
			}
		}
		
		$terms = get_terms( $taxonomy,
			array(
				'hide_empty' => false
			)
		);
		
		if(is_array($itens))
		{
			// verifica se todos os temas enviados por post são válidos
			foreach($terms as $term)
			{
				if(in_array($term->term_id, $itens))
				{
					$terms_ids[] = $term->term_id;
				}
			}
			
			// coloca os termos no post
			if($insert && count($terms_ids) > 0) wp_set_post_terms($pauta_id, $terms_ids, 'tema');
		}
	}
	return $terms_ids;
}

?>