<?php

//TODO: Criar formato de inserção de notificações
/*function delibera_notificar_get_tipos($post = false)
  {
  if($post === false)
  {
  $post = get_post();
  }
  elseif(is_int($post))
  {
  $post = get_post($post);
  }

  $tipos = array(
  'nova_pauta' => array(),
  'situacao' => array('mensagem' => __('Situação', 'delibera').': '.delibera_get_situacao($post)->name),
  'fim_prazo' => array()
  );
  if(file_exists(__DIR__.'delibera_notifica_tipos.php'))
  {
  include __DIR__.'delibera_notifica_tipos.php';
  }

  return has_filter('delibera_notificar_get_tipos') ? apply_filters('delibera_notificar_get_tipos', $tipos) : $tipos;   
  }*/

function delibera_notifications_menu_action($base_page)
{
	add_submenu_page($base_page, __('Notificações', 'delibera'), __('Notificações', 'delibera'), 'manage_options', 'delibera-notifications', 'delibera_notifications_page' );
}
add_action('delibera_menu_itens', 'delibera_notifications_menu_action', 10, 1);

function delibera_notifications_page()
{
	$mensagem = '';
	$opt = delibera_get_config();
	$notification_options = delibera_get_notification_config();

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (!current_user_can('manage_options')) {
			die(__('Você não pode editar as configurações do delibera.', 'delibera'));
		}
		check_admin_referer('delibera-notifications');
//XXX problema com o htmlspecialchars
		foreach (array_keys($notification_options) as $option_name) {
			if (isset($_POST[$option_name])) {
				$opt[$option_name] = htmlspecialchars($_POST[$option_name]);
			} else {
				$opt[$option_name] = 'N';
			}
		}

		if (update_option('delibera-config', $opt)) {
			$mensagem = __('Configurações salvas!','delibera');
		} else {
			$mensagem = __('Erro ao salvar as configurações. Verifique os valores inseridos e tente novamente!','delibera');
		}
	}

	?>
		<div class="wrap">
		<h2>Notificações</h2>
		<div class="postbox-container" style="width:80%;">
		<div class="metabox-holder">	
		<div class="meta-box-sortables">
		<?php if ($mensagem) : ?>
		<div id="message" class="updated">
		<?php echo $mensagem; ?>
		</div>
		<?php endif; ?>
		<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" id="delibera-config" >
		<?php
		wp_nonce_field('delibera-notifications');

	//{
	$content_notif = '<input id="notificacoes" type="checkbox" name="notificacoes" value="S" ';
	$content_notif.= (htmlspecialchars_decode($opt['notificacoes']) == 'S' ? 'checked="checked"' : '') . ' />';
	$opt = delibera_get_config();

	$rows = array();
	$rows[] = array(
			"id" => "notificacoes",
			"label" => __('Permitir notificações por e-mail?','delibera'),
			"content" => $content_notif
		       );
	$table = delibera_form_table($rows);
	$rows_lang = array();

	if(function_exists('qtrans_enableLanguage'))
	{
		$head = "<div id=\"delibera-mensagens-notificacoes-painel\" ";
		$head.= (htmlspecialchars_decode($opt['notificacoes']) == 'S' ? '' : 'style="display:none"');
		$head.= " ><div id=\"delibera-mensagens-notificacoes\"><label id=\"label-delibera-mensagens-notificacoes\" >";
		$head.= __('Selecione uma língua para configurar as notificações em cada idioma', 'delibera')."</label>";
		global $q_config;
		foreach ($q_config['enabled_languages'] as $lang)
		{
			$display = "none";
			$ativa = "";

			if($lang == $q_config['default_language'])
			{
				$display = "block";
				$ativa = "active";
			}

			$onclick="switch_mensagem_box('".$lang."');";
			$head.= '<a id="link-delibera-mensagens-';
			$head.= $lang.'" class="link-delibera-mensagens ';
			$head.= $ativa.'" href="javascript:'.$onclick.'" ><label>';
			$head.= __($q_config['language_name'][$lang], 'qtranslate').'</label></a>';

			$rows_lang[$lang] = delibera_nofiticar_config_page_campos($opt, $lang);

			if($lang == $q_config['default_language'])
			{
				$rows_lang[$lang]['default'] = true;
			}
		}
		$table .= $head."</div>";
	}
	else
	{

		$lang = get_locale();

		$display = "block";
		$ativa = "active";

		$rows_lang[$lang] = delibera_nofiticar_config_page_campos($opt, $lang);

		$rows_lang[$lang]['default'] = true;

		$table;
	}

	$table .= '<div id="painel-notificacoes" '.(htmlspecialchars_decode($opt['notificacoes']) == 'S' ? '' : 'style="display:none"').' >';
	$rows = array();
	$i = 0;

	foreach (delibera_nofiticar_get_tipos() as $notif)
	{
		delibera_nofiticar_config_page_create_checkbox($rows,
				"{$notif['mensagem']}-enabled",
				$notif['dica_permicao'],
				$opt,
				$rows_lang, $i++
				);
	}
	echo $table;  
	echo '<table class="form-table">';
	foreach($rows as $row)
	{
		echo '<tr '.(array_key_exists('row-id', $row) ? 'id="'.$row['row-id'].'"' : '' ).' '.(array_key_exists('row-style', $row) ? 'style="'.$row['row-style'].'"' : '' ).' '.(array_key_exists('row-class', $row) ? 'class="'.$row['row-class'].'"' : '' ).' ><th valign="top" scrope="row">';

		if (isset($row['id']) && $row['id'] != '') {
			echo '<label for="'.$row['id'].'">'.$row['label'].'</label>';
		} else {
			echo $row['label'];
		}

		if (isset($row['desc']) && $row['desc'] != '') {
			echo '<br/><small>'.$row['desc'].'</small>';
		}
        //XXX aiaiaiai gambiarra norvosa :( str_replace('\\', '', htmlspecialchars_decode($row['content']) )
		echo '</th><td valign="top">';
		echo array_key_exists('type', $row) ? wp_editor( str_replace('\\', '', htmlspecialchars_decode($row['content']) ) , $row['id'] ,  array( 'editor_class' => 'delibera-config-mensagem') ): $row['content'];
		echo '</td></tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '<div class="submit"><input type="submit" class="button-primary" name="submit" value="' . __('Save Changes') . '" /></div>';
	?>
		</form>
		</div> <!-- meta-box-sortables -->
		</div> <!-- meta-box-holder -->
		</div> <!-- postbox-container -->
		</div>
		<?php
}


/*
delibera_get_notification_config

Recebe um arranjo vázio ou não e complementa o arranjo com as informações necessárias para a página de notificação.
Todas as mensagens e os cabeçalhos e rodapés das mensagens são predefinidos aqui. Qualquer nova linha deve antes ser definida abaixo.
Existem comentários que identificam o inicio de uma mensagem. A mensagem possui titulo, corpo, cabeçalho e roddapé.

*/
function delibera_get_notification_config($config = array())
{
	$opt['notificacoes'] = "S";
	foreach (delibera_nofiticar_get_tipos() as $notif)
	{
		$opt["{$notif['mensagem']}-enabled"] = "S";
	}
// inicio mensagem_criacao_pauta
	$opt['mensagem_criacao_pauta_assunto'] = __('Nova Pauta Criada','delibera'); 
	$opt['mensagem_criacao_pauta'] = __('Nova Pauta: {post_title}','delibera');
	$opt['mensagem_criacao_pauta_cabecalho'] = __('Cabeçalho','delibera');
	$opt['mensagem_criacao_pauta_rodape'] = __('Rodapé','delibera');
// fim mensagem_criacao_pauta
// inicio mensagem_validacao
	$opt['mensagem_validacao_assunto'] = __('Novo voto de validação: Pauta {post_title}','delibera');
	$opt['mensagem_validacao'] = __(
			'A validação do usuário xxx na pauta {post_title} foi registrada no sistema. Acesse a pauta para verificar a situação: 
			{post_url}', 'delibera');
	$opt['mensagem_validacao_cabecalho'] = __('Cabeçalho','delibera');
	$opt['mensagem_validacao_rodape'] = __('Rodapé','delibera');
// fim mensagem_validacao
// mensagem_validacao_concluida
	$opt['mensagem_validacao_concluida_assunto'] = __('Nova pauta validada: Pauta {post_title}','delibera');
	$opt['mensagem_validacao_concluida'] = __(
			'<div><h1>Nova pauta validada: Pauta «{post_title}»</h1></div>
			<div style="border: 5px solid grey; padding: 1em;">
			Olá {display_name},

			A pauta "{post_title}" foi validada, agora você poderá fazer comentários e propostas. Além disto poderá inserir arquivos em diversos formatos, seja em texto, imagem, áudio ou vídeo. 
			Seus comentários poderão ser avaliados pelos demais membros, já as propostas, além de avaliadas previamente, poderão ir a votação onde todos os usuários poderão votar. Caso exista uma relatoria, esta poderá fazer a sistematização, mesclando propostas similares e ajustando a redação para a melhor compreensão, tudo garantindo o controle social do processo com o histórico de propostas originais. 

			Agora vai em frente! Debata, proponha, fortaleça a democracia digital! 
			{post_url}', 'delibera');
	$opt['mensagem_validacao_concluida_cabecalho'] = __('Cabeçalho','delibera');
	$opt['mensagem_validacao_concluida_rodape'] = __('Rodapé','delibera');
// fim mensagem_validacao_concluida
// inicio mensagem_pauta_recusada
	$opt['mensagem_pauta_recusada_assunto'] = __('Pauta Recusada: Pauta {post_title}','delibera');
	$opt['mensagem_pauta_recusada'] = __(
			'<div><h1>Pauta Recusada: Pauta «{post_title}»</h1></div>
			<div style="border: 5px solid grey; padding: 1em;">
			Olá {display_name},

			A pauta {post_title} não obteve o apoio necessário, por isto ela não entrará em debate desta vez. 

			{post_url}', 'delibera');
	$opt['mensagem_pauta_recusada_cabecalho'] = __('Cabeçalho','delibera');
	$opt['mensagem_pauta_recusada_rodape'] = __('Rodapé','delibera');
// fim mensagem_pauta_recusada
// inicio mensagem_fim_prazo_validacao
	$opt['mensagem_fim_prazo_validacao_assunto'] = __('Fim de prazo para Validação: Pauta {post_title}' , 'delibera');
	$opt['mensagem_fim_prazo_validacao'] = __('Olá {display_name},

		Falta apenas 1 dia para o fim do prazo para validação da pauta {post_title}. Caso não tenha votado aproveite agora para fazê-lo, ou chame seus conhecidos! 

		{post_url}' , 'delibera' );
	$opt['mensagem_fim_prazo_validacao_cabecalho'] = __('Cabeçalho' , 'delibera');
	$opt['mensagem_fim_prazo_validacao_rodape'] = __('Rodapé' , 'delibera');
// fim mensagem_fim_prazo_validacao
// inicio mensagem_novo_comentario
	$opt['mensagem_novo_comentario_assunto'] = __('Novo Comentário em {post_title}','delibera');
	$opt['mensagem_novo_comentario'] = __('Há um novo comentário na pauta seguida: ','delibera');
	$opt['mensagem_novo_comentario_cabecalho'] = __('Cabeçalho','delibera');
	$opt['mensagem_novo_comentario_rodape'] = __('Rodapé','delibera');

// fim mensagem_novo_comentario
// inicio mensagem_fim_prazo_discussao
	$opt['mensagem_fim_prazo_discussao_assunto'] = __( 'Fim de prazo para Discussão: Pauta {post_title}' , 'delibera' );
	$opt['mensagem_fim_prazo_discussao'] = __( 'Olá {display_name},

		Falta apenas 1 dia para o fim do prazo de discussão da pauta {post_title}. Caso ainda queira comentar aproveite agora para fazê-lo agora!
		{post_url}' , 'delibera' );
	$opt['mensagem_fim_prazo_discussao_cabecalho'] = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_fim_prazo_discussao_rodape'] = __( 'Rodapé' , 'delibera' );
	
// fim mensagem_fim_prazo_discussao
// inicio mensagem_discussao_concluida_relatoria
	$opt['mensagem_discussao_concluida_rel_assunto'] = __('Término da Discussão e início da relatoria: Pauta {post_title}','delibera');
	$opt['mensagem_discussao_concluida_rel'] = __(
			'<div><h1>Término da Discussão e início da relatoria: Pauta «{post_title}»</h1></div>
			<div style="border: 5px solid grey; padding: 1em;">
			Olá {display_name},

			O prazo para discussão da pauta {post_title} terminou, agora o relator terá o prazo de {report_deadline} para sistematizar as propostas e colocar em votação. Você continua pondendo visualizar o que foi discutido nesta pauta:

			{post_url}', 'delibera');
	$opt['mensagem_discussao_concluida_rel_cabecalho'] = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_discussao_concluida_rel_rodape'] = __( 'Rodapé' , 'delibera' );

// fim mensagem_discussao_concluida_relatoria
// inicio mensagem_discussao_concluida
	$opt['mensagem_discussao_concluida_assunto'] = __('Término da Discussão: Pauta {post_title}','delibera');
	$opt['mensagem_discussao_concluida'] = __(
			'<div><h1>Término da Discussão: Pauta «{post_title}»</h1></div>
			<div style="border: 5px solid grey; padding: 1em;">
			Olá {display_name},

			{post_url}', 'delibera');
	$opt['mensagem_discussao_concluida_cabecalho'] = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_discussao_concluida_rodape'] = __( 'Rodapé' , 'delibera' );
// fim mensagem_discussao_concluida
// inicio mensagem_relatoria_concluida
	$opt['mensagem_relatoria_concluida_assunto'] = __('Fim do prazo da relatoria: Pauta {post_title}','delibera');
	$opt['mensagem_relatoria_concluida'] = __(
			'<div><h1>Fim do prazo da relatoria: Pauta «{post_title}»</h1></div>
			<div style="border: 5px solid grey; padding: 1em;">
			Olá {display_name},

			O prazo para a relatoria da pauta {post_title} terminou, agora você poderá votar nas propostas que foram encaminhadas durante o processo de discussão e sistematizadas pela relatoria. 
			Você pode acompanhar a origem de todas as propostas, de modo que garanta o controle social da sistematização das propostas.

			{post_url}', 'delibera');
	$opt['mensagem_relatoria_concluida_cabecalho'] = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_relatoria_concluida_rodape'] = __( 'Rodapé' , 'delibera' );
// fim mensagem_relatoria_concluida
// inicio mensagem_fim_prazo_relatoria
	$opt['mensagem_fim_prazo_relatoria_assunto'] = 'Fim do prazo da relatoria: Pauta {post_title}';
	$opt['mensagem_fim_prazo_relatoria'] = 'Olá {display_name},
		O prazo para a relatoria da pauta "{post_title}" terminou, agora você poderá votar nas propostas que foram encaminhadas durante o processo de discussão e sistematizadas pela relatoria. 
			Você pode acompanhar a origem de todas as propostas, de modo que garanta o controle social da sistematização das propostas.

			{post_url}';
	$opt['mensagem_fim_prazo_relatoria_cabecalho'] = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_fim_prazo_relatoria_rodape'] = __( 'Rodapé' , 'delibera' );

// fim mensagem_fim_prazo_relatoria
// inicio mensagem_fim_prazo_votacao
	$opt['mensagem_fim_prazo_votacao_assunto'] = __( 'Fim de prazo para Votação: Pauta {post_title}' , 'delibera' );
	$opt['mensagem_fim_prazo_votacao'] = __( 'Olá {display_name},

		Falta apenas 1 dia para o fim do prazo de votação da pauta {post_title}. Se ainda não votou,  aproveite agora para fazê-lo agora. Sua participação é muito importante!

		{post_url}' , 'delibera' );
	$opt['mensagem_fim_prazo_votacao_cabecalho']  = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_fim_prazo_votacao_rodape']  = __( 'Rodapé' , 'delibera' );
// fim mensagem_fim_prazo_votacao
// inicio mensagem_votacao_concluida
	$opt['mensagem_votacao_concluida_assunto'] = __('Votação Concluída, veja as resoluções: Pauta {post_title}','delibera');
	$opt['mensagem_votacao_concluida'] = __(
			'<div><h1>Votação Concluída, veja as resoluções: Pauta «{post_title}»</h1></div>
			<div style="border: 5px solid grey; padding: 1em;">
			Olá {display_name},

			O prazo para votação da pauta {post_title} encerrou, confira as resoluções do processo.
			
			Obrigado por sua participação!

			{post_url}', 'delibera');
	$opt['mensagem_votacao_concluida_cabecalho'] = __( 'Cabeçalho' , 'delibera' );
	$opt['mensagem_votacao_concluida_rodape'] = __( 'Rodapé' , 'delibera' );

	$langs = delibera_get_available_languages();

	foreach ($langs as $lang)
	{
		foreach (delibera_nofiticar_get_tipos() as $notif)
		{
			$opt["{$notif['mensagem']}_assunto-$lang"] = $opt["{$notif['mensagem']}_assunto"];
			$opt["{$notif['mensagem']}-$lang"] = $opt[$notif['mensagem']];
			$opt["{$notif['mensagem']}_cabecalho-$lang"] = $opt["{$notif['mensagem']}_cabecalho"];
			$opt["{$notif['mensagem']}_rodape-$lang"] = $opt["{$notif['mensagem']}_rodape"];
		}
	}

	return array_merge($opt, $config);
}
add_filter('delibera_get_config', 'delibera_get_notification_config');
/*

XXX nome da função esta incorreto. (delibeta)

delibeta_nofiticar_config_page_row

Esta função é respnsavel por visitar cada elemento de um arranjo contendo informações para montar linhas de uma tabela.
Básicamente ela monta as notificações que contem titílo, corpo/mensagem, cabeçalho e cabeçalho.

*/
function delibeta_nofiticar_config_page_row(&$rows, $opt, $tipo, $label = '', $lang = '')
{

	if($label == '') $label = $tipo;
	$label = "<strong>".$label."</strong>";

	$rows2 = array();

	$rows2[] = array(
			"id" => "mensagem_{$tipo}_assunto".$lang,
			"row-id" => "row-mensagem_{$tipo}_assunto".$lang,
			"label" => __('Assunto da mensagem padrão de notificação de ' . $label . ':', 'delibera'),
			"content" => '<input type="text" class="delibera-config-mensagem-assunto" name="mensagem_'.$tipo.'_assunto'.$lang.'" id="mensagem_'.$tipo.'_assunto'.$lang.'" value="'.htmlspecialchars_decode($opt['mensagem_'.$tipo.'_assunto'.$lang]).'"/>'
			);
	$rows2[] = array(
			"id" => "mensagem_{$tipo}".$lang,
			"row-id" => "row-mensagem_{$tipo}".$lang,
			"label" => __('Mensagem padrão de notificação de ' . $label . ':', 'delibera'),
			"content" => htmlspecialchars_decode($opt['mensagem_'.$tipo.$lang]),
			"type" => 'wp_editor'
			);
		$rows2[] = array(
			"id" => "mensagem_{$tipo}_cabecalho".$lang,
			"row-id" => "row-mensagem_{$tipo}_cabecalho".$lang,
			"label" => __('Cabeçalho da mensagem padrão de notificação de ' . $label . ':', 'delibera'),
			"content" => htmlspecialchars_decode($opt['mensagem_'.$tipo.'_cabecalho'.$lang]),
			"type" => 'wp_editor'
			);
		$rows2[] = array(
			"id" => "mensagem_{$tipo}_rodape".$lang,
			"row-id" => "row-mensagem_{$tipo}_rodape".$lang,
			"label" => __('Rodapé da mensagem padrão de notificação de ' . $label . ':', 'delibera'),
			"content" => htmlspecialchars_decode($opt['mensagem_'.$tipo.'_rodape'.$lang]),
			"type" => 'wp_editor'
			);

	$rows[] = $rows2;
}

function delibera_nofiticar_config_page_campos($opt, $lang = '')
{
	if($lang != '') $lang = "-".$lang;

	$rows = array();

	foreach (delibera_nofiticar_get_tipos() as $notif)
	{
		delibeta_nofiticar_config_page_row($rows, $opt, $notif['tipo'], $notif['dica'], $lang);
	}

	return $rows;
}

function delibera_nofiticar_config_page()
{
	$table = '';
	$opt = delibera_get_config();

	$rows = array();
	$rows[] = array(
			"id" => "notificacoes",
			"label" => __('Permitir notificações por e-mail?','delibera'),
			"content" => '<input id="notificacoes" type="checkbox" name="notificacoes" value="S" '.(htmlspecialchars_decode($opt['notificacoes']) == 'S' ? 'checked="checked"' : '').' />'
		       );
	$table .= delibera_form_table($rows);
	$rows_lang = array();

	if(function_exists('qtrans_enableLanguage'))
	{
		$head = "<div id=\"delibera-mensagens-notificacoes-painel\" ".(htmlspecialchars_decode($opt['notificacoes']) == 'S' ? '' : 'style="display:none"')." ><div id=\"delibera-mensagens-notificacoes\"><label id=\"label-delibera-mensagens-notificacoes\" >".__('Selecione uma língua para configurar as notificações em cada idioma', 'delibera')."</label>";
		global $q_config;

		foreach ($q_config['enabled_languages'] as $lang)
		{
			$display = "none";
			$ativa = "";

			if($lang == $q_config['default_language'])
			{
				$display = "block";
				$ativa = "active";
			}

			$onclick="switch_mensagem_box('".$lang."');";
			$head .= '<a id="link-delibera-mensagens-'.$lang.'" class="link-delibera-mensagens '.$ativa.'" href="javascript:'.$onclick.'" ><label>'.__($q_config['language_name'][$lang], 'qtranslate').'</label></a>';

			$rows_lang[$lang] = delibera_nofiticar_config_page_campos($opt, $lang);

			if($lang == $q_config['default_language'])
			{
				$rows_lang[$lang]['default'] = true;
			}
		}
		$table .= $head."</div>";
	}
	else
	{

		$lang = get_locale();

		$display = "block";
		$ativa = "active";

		$rows_lang[$lang] = delibera_nofiticar_config_page_campos($opt, $lang);

		$rows_lang[$lang]['default'] = true;

		$table;
	}

	$table .= '<div id="painel-notificacoes" '.(htmlspecialchars_decode($opt['notificacoes']) == 'S' ? '' : 'style="display:none"').' >';
	$rows = array();
	$i = 0;

	foreach (delibera_nofiticar_get_tipos() as $notif)
	{
		delibera_nofiticar_config_page_create_checkbox($rows,
				"{$notif['mensagem']}-enabled",
				$notif['dica_permicao'],
				$opt,
				$rows_lang, $i++
				);
	}
	echo '<pre>';	
	print_r($rows);
	echo '</pre>';	
	$table .= delibera_form_table($rows);
	$table .= '</div>';

	return $table;
}

function delibera_nofiticar_config_page_create_checkbox(&$rows, $id, $label, $opt, $rows_lang, $index)
{
	$rows[] = array(
			"id" => $id,
			"label" => $label,
			"content" => '<input id="'.$id.'" type="checkbox" class="checkbox-mensagem-notificacao" name="'.$id.'" value="S" '.(htmlspecialchars_decode($opt[$id]) == 'S' ? 'checked="checked"' : '').' />'
		       );


	foreach($rows_lang as $lang => $row_lang)
	{
		$active = false;
		if(array_key_exists('default', $row_lang))
		{
			$active = true;
		}
		foreach($row_lang[$index] as $rl)
		{
			$rl["row-class"] = 'div-delibera-mensagens-'.$lang.($active ? ' active' : '');
			if(!$active)
			{
				$rl["row-style"] = "display: none;";
			}
			$rows[] = $rl;
		}
	}
}

function delibera_notificar_get_mensagem_link($post, $link = false)
{
	$mensage = "";
	if($link === false)
	{
		$link = get_permalink($post);
	}
	$mensage = '<br/><br/>Origem: <a href="'.get_post_type_archive_link('pauta').'">'.get_bloginfo('name').'</a><br/>';
	$mensage .= 'Pauta: <a href="'.$link.'">'.$post->post_title.'</a><br/><br/>';
	$mensage .= __('Para ver a mensagem na página, clique aqui: ', 'delibera').$link;
	return $mensage;
}

function delibera_notificar_nova_pauta($post = false)
{
	$message = '';
	delibera_notificar_representantes($message, 'mensagem_criacao_pauta', $post);
}

function delibera_notificar_situacao($post = false)
{
	/*$message = 'Situação: '.delibera_get_situacao($post)->name;
	  delibera_notificar_representantes($message, 'mensagem_mudanca_situacao', $post);*/
}

function delibera_notificar_nova_validacao($post = false)
{
	delibera_notificar_representantes('', 'validacao', $post);
}
add_action('delibera_validacao', 'delibera_notificar_nova_validacao');

function delibera_notificar_pauta_recusada($post = false)
{
	delibera_notificar_representantes('', 'pauta_recusada', $post);
}
add_action('delibera_pauta_recusada', 'delibera_notificar_pauta_recusada');

function delibera_notificar_validacao_concluida($post = false)
{
	delibera_notificar_representantes('', 'validacao_concluida', $post);
}
add_action('delibera_validacao_concluida', 'delibera_notificar_validacao_concluida');

function delibera_notificar_discussao_concluida($post = false)
{
	$opt = delibera_get_config();
	if($opt['relatoria'] == 'S')
	{
		delibera_notificar_representantes('', 'discussao_concluida_rel', $post);
	}
	else
	{
		delibera_notificar_representantes('', 'discussao_concluida', $post);
	}
}
add_action('delibera_discussao_concluida', 'delibera_notificar_discussao_concluida');

function delibera_notificar_relatoria_concluida($post = false)
{
	delibera_notificar_representantes('', 'relatoria_concluida', $post);
}
add_action('delibera_relatoria_concluida', 'delibera_notificar_relatoria_concluida');

function delibera_notificar_votacao_concluida($post = false)
{
	delibera_notificar_representantes('', 'votacao_concluida', $post);
}
add_action('delibera_votacao_concluida', 'delibera_notificar_votacao_concluida');

function delibera_notificar_fim_prazo($args)
{
	$post = $args['post_ID'];
	if(is_int($post))
	{
		$post = get_post($post);
	}
	$message = '';
	$situacao = delibera_get_situacao($post->ID);

	delibera_notificar_representantes($message, "mensagem_fim_prazo_{$situacao->slug}", $post);
}
function delibera_notificar_representantes($mensage, $tipo, $post = false, $users = false, $link = false)
{
	require( ABSPATH . WPINC . '/pluggable.php' );
	add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

	if($post === false)
	{
		$post = get_post();
	}
	
	if(!is_object($post))
	{
		$post = get_post($post);
	}
	
	if(is_object($post) && $post->post_status == 'publish')
	{

		$options_plugin_delibera = delibera_get_config();

		if(
				$options_plugin_delibera["notificacoes"] == "N" || // Notificações estão desabilitadas OU
				!array_key_exists("$tipo-enabled", $options_plugin_delibera) ||
				$options_plugin_delibera["$tipo-enabled"] == "N" // Esse tipo de notificação está desabilitada
		  )
		{
			return false;
		}
		$subject_default = htmlspecialchars_decode($options_plugin_delibera["{$tipo}_assunto"]);
		$mensage_default = htmlspecialchars_decode($options_plugin_delibera[$tipo]).$mensage.delibera_notificar_get_mensagem_link($post, $link);

		if(!is_array($users))
		{
			$users = get_users();
		}

		if(!is_array($users))
		{
			$users = array();
		}

		$autor_id = get_current_user_id();

		$seguiram = delibera_get_quem_seguiu($post->ID, 'ids');

		foreach ($users as $user)
		{
			if(user_can($user->ID, 'votar') && isset($user->user_email) && $user->ID != $autor_id)
			{
				$segue = array_search($user->ID, $seguiram);

				$user_notificacoes = get_user_meta($user->ID, 'delibera_notificacoes_email', true);

				if(!$segue && ($user_notificacoes == "N" || get_user_meta($user->ID, "$tipo-enabled", true) == "N"))
				{
					continue;
				}

				$mensage_tmp = $mensage_default;
				$subject_tmp = $subject_default;

				$lang = get_user_meta($user->ID, 'user_idioma', true);

				if(strlen($lang) == 0) $lang = defined('WPLANG') && strlen(WPLANG) > 0 ? WPLANG : get_locale();

				if(array_key_exists("$tipo-$lang", $options_plugin_delibera))
				{
					$mensage_tmp = htmlspecialchars_decode($options_plugin_delibera["{$tipo}_cabecalho-$lang"]) . htmlspecialchars_decode($options_plugin_delibera["$tipo-$lang"]) . $mensage . htmlspecialchars_decode($options_plugin_delibera["{$tipo}_rodape-$lang"]);
				}
				if(array_key_exists("{$tipo}_assunto-$lang", $options_plugin_delibera))
				{
					$subject_tmp = htmlspecialchars_decode($options_plugin_delibera["{$tipo}_assunto-$lang"]);
				}

				$subject_tmp = delibera_notificar_replace_vars($subject_tmp, $user, $post);
				$mensage_tmp = delibera_notificar_replace_vars($mensage_tmp, $user, $post);
				wp_mail($user->user_email, $subject_tmp, $mensage_tmp);
			}
		}
	}

}

function delibera_notificar_novo_comentario($comment)
{
	global $post;
	if(!is_object($post))
	{
		$post = get_post($comment->comment_post_ID);
	}
	$seguiram = get_post_meta($comment->comment_post_ID, 'delibera_seguiram', true);
	$users = array();

	if(!is_array($seguiram)) $seguiram = array(); // Ops, não tem o plugin ou não tem seguidores

	foreach ($seguiram as $hora => $seguiram)
	{
		foreach ($seguiram as $user)
		{
			if($user['user'] > 0)
			{
				$users[] = get_user_by('id', $user['user']);
			}
		}
	}

	$autor = get_user_by('id', $post->post_author);

	$users = is_array($users) ? array_merge($users, array($autor)) : array($autor);

	$mensage = '<br/>'.__('Autor', 'delibera').": ".$autor->display_name.'<br/>';
	$mensage .= get_comment_text($comment->comment_ID)."<br/>";

	$link = delibera_get_comment_link($comment);

	delibera_notificar_representantes($mensage, "mensagem_novo_comentario", $post, $users, $link);

}

function delibera_notificar_replace_vars($subject, $user, $postReport)
{
	global $post;
	if(!is_object($postReport))
	{
		$postReport = get_post($postReport);
	}

	$post = $postReport;

	$opt = delibera_get_config();

	$author = get_user_by('id', $post->post_author);

	$subject = str_ireplace("{first_name}", $user->user_firstname, $subject);
	$subject = str_ireplace("{last_name}", $user->user_lastname, $subject);
	$subject = str_ireplace("{display_name}", $user->display_name, $subject);
	$subject = str_ireplace("{post_title}", $post->post_title, $subject);
	$subject = str_ireplace("{post_url}", delibera_notificar_get_mensagem_link($post), $subject);
	$subject = str_ireplace("{report_deadline}", $opt['dias_relatoria'], $subject);
	$subject = str_ireplace("{reporter_election}", $opt['dias_votacao_relator'], $subject);
	$subject = str_ireplace("{validation_days}", $opt['dias_validacao'], $subject);
	$subject = str_ireplace("{discuss_days}", $opt['dias_validacao'], $subject);
	$subject = str_ireplace("{election_days}", $opt['dias_votacao'], $subject);
	$subject = str_ireplace("{post_author}", $author->user_firstname, $subject);
	$subject = str_ireplace("{post_content}", get_the_content(), $subject);
	$subject = str_ireplace("{post_excerpt}", get_the_excerpt(), $subject);
	$subject = str_ireplace("{site_url}", get_site_url(), $subject);
	$subject = str_ireplace("{site_name}", get_bloginfo( 'name' ), $subject);

	$campos = delibera_get_user_campos_form_registro();
	if($campos > 0)
	{
		foreach ($campos as $campo)
		{
			$valor_replace = "";
			if($campo['novo'] == true)
			{ 
				switch($campo['tipo_painel'])
				{
					case 'DropDown':
						$valores = array();
						if(is_array($campo['dados']) && count($campo['dados']) > 0)
						{
							$valores = $campo['dados'];
						}
						elseif(is_string($campo['dados']) && function_exists($campo['dados']))
						{
							$param = $campo['dados_param'] ? $campo['dados_param'] : array();
							$ret = call_user_func($campo['dados'], $param);
							if(is_array($ret) && count($ret) > 0)
							{
								$valores = $ret;
							}
						}
						foreach ($valores as $valor => $desc)
						{
							if(strtolower(( get_user_meta( $user->ID, $campo['id'], true ))) === strtolower($valor))
							{
								$valor_replace = $desc;
							}
						}
						break;
					case 'CheckBox':
						$valor = true;
						if(is_array($campo['dados']) && count($campo['dados']) > 0)
						{
							$keys = array_keys($campo['dados']);
							$valor = $keys[0];
						}
						elseif(is_string($campo['dados']) && function_exists($campo['dados']))
						{
							$param = $campo['dados_param'] ? $campo['dados_param'] : array();
							$ret = call_user_func($campo['dados'], $param);
							if(is_array($ret) && count($ret) > 0)
							{
								$keys = array_keys($ret);
								$valor = $keys[0] == 0 ? $ret[0] : $keys[0];
							}
							elseif (is_string($ret))
							{
								$valor = $ret;
							}
						}
						if(strtolower(( get_user_meta( $user->ID, $campo['id'], true ))) === strtolower($valor))
						{
							$valor_replace = __("Sim", 'delibera');
						}
						else 
						{
							$valor_replace = __("Não", 'delibera');
						}
						break;
					case 'Texto':
					default:
						$valor_replace = ( get_user_meta( $user->ID,$campo['id'], true ) );
						break;
				}
			}
			$subject = str_ireplace("{".$campo['id']."}", $valor_replace, $subject);
		}
	}
	return $subject;
}

function delibera_nofiticar_get_tipos()
{
	return array(
			array(
					'tipo' => 'criacao_pauta',
					'mensagem' => 'mensagem_criacao_pauta',
					'dica' => __('criação de pauta', 'delibera'),
					'dica_permicao' => __('Permitir notificações na criação de uma pauta?', 'delibera'),
					'user_panel_text' => __('Receber mensagem de criação de novas pautas?', 'delibera')
			     ),
			array(
					'tipo' => 'validacao',
					'mensagem' => 'mensagem_validacao',
					'dica' => __('quando a pauta recebe uma validação', 'delibera'),
					'dica_permicao' => __('Permitir notificações de cada validação de uma pauta?', 'delibera'),
					'user_panel_text' => __('Receber mensagem para cada validação de uma pauta?', 'delibera')
			     ),
			array(
					'tipo' => 'validacao_concluida',
					'mensagem' => 'mensagem_validacao_concluida',
					'dica' => __('quando a pauta é validada', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando a pauta é validada?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando uma pauta for aprovada?', 'delibera')
			     ),
			array(
					'tipo' => 'pauta_recusada',
					'mensagem' => 'mensagem_pauta_recusada',
					'dica' => __('quando a pauta é recusada (não obteve validações necessárias)', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando a pauta é recusada?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando uma pauta for recusada?', 'delibera')
			     ),
			array(
					'tipo' => 'fim_prazo_validacao',
					'mensagem' => 'mensagem_fim_prazo_validacao',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando o fim do prazo para validações estiver próximo?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando o fim do prazo para validações estiver próximo?', 'delibera')
			     ),
			array(
					'tipo' => 'novo_comentario',
					'mensagem' => 'mensagem_novo_comentario',
					'dica' => __('novo comentário', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando houver interações na discussão de uma pauta?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando houver interações na discussão de uma pauta?', 'delibera')
			     ),
			array(
					'tipo' => 'discussao_concluida',
					'mensagem' => 'mensagem_discussao_concluida',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando a discussão for encerrada?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando a discussão for encerrada?', 'delibera')
			     ),
			array(
					'tipo' => 'fim_prazo_discussao',
					'mensagem' => 'mensagem_fim_prazo_discussao',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando o fim do prazo para discussão estiver próximo?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando o fim do prazo para discussão estiver próximo?', 'delibera')
			     ),
			array(
					'tipo' => 'discussao_concluida_rel',
					'mensagem' => 'mensagem_discussao_concluida_rel',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando a discussão for encerrada?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando a discussão for encerrada?', 'delibera')
			     ),
			array(
					'tipo' => 'relatoria_concluida',
					'mensagem' => 'mensagem_relatoria_concluida',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando a relatoria for encerrada?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando a relatoria for encerrada?', 'delibera')
			     ),
			array(
					'tipo' => 'fim_prazo_relatoria',
					'mensagem' => 'mensagem_fim_prazo_relatoria',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando o fim do prazo para relatoria estiver próximo?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando o fim do prazo para relatoria estiver próximo?', 'delibera')
			     ),
			array(
					'tipo' => 'fim_prazo_votacao',
					'mensagem' => 'mensagem_fim_prazo_votacao',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando o fim do prazo para votação estiver próximo?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando o fim do prazo para votação estiver próximo?', 'delibera')
			     ),
			array(
					'tipo' => 'votacao_concluida',
					'mensagem' => 'mensagem_votacao_concluida',
					'dica' => __('', 'delibera'),
					'dica_permicao' => __('Permitir notificações quando a votação for encerrada?', 'delibera'),
					'user_panel_text' => __('Receber mensagem quando a votação for encerrada?', 'delibera')
			     )
		        );
}

function delibera_nofitica_user_panel($forms)
{
	/*$campos = array(
	  array(
	  'novo' => true,
	  'id' => 'mensagem_criacao_pauta-enabled',
	  'nome' => __('Receber mensagem de criação de novas pauta?', 'delibera'),
	  'registro' => false,
	  'tipo_painel' => 'CheckBox',
	  'dados' => array("S" => "S", "N" => "N")
	  ),
	  array(
	  'novo' => true,
	  'id' => 'mensagem_mudanca_situacao-enabled',
	  'nome' => __('Receber mensagem nova situação das pautas?', 'delibera'),
	  'registro' => false,
	  'tipo_painel' => 'CheckBox',
	  'dados' => array("S" => "S", "N" => "N")
	  ),
	  array(
	  'novo' => true,
	  'id' => 'mensagem_fim_prazo-enabled',
	  'nome' => __('Receber mensagem fim de prazo próximo?', 'delibera'),
	  'registro' => false,
	  'tipo_painel' => 'CheckBox',
	  'dados' => array("S" => "S", "N" => "N")
	  )
	  );*/
	if(!is_array($forms)) $forms = array();

	foreach (delibera_nofiticar_get_tipos() as $notif)
	{
		$forms[] = array(
				'novo' => true,
				'id' => $notif['mensagem'].'-enabled',
				'nome' => $notif['user_panel_text'],
				'registro' => false,
				'tipo_painel' => 'CheckBox',
				'dados' => array("S" => "S", "N" => "N"),
				'default' => 'S'
				);
	}

	return $forms;
}
add_filter('delibera_user_painel_campos', 'delibera_nofitica_user_panel', 10, 1)

?>
