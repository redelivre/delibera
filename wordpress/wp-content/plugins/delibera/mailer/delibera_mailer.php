<?php

require_once 'delibera_mailer_read.php';

/*
 * Rotinas de instalação do plugin
*/

function delibera_mailer_get_config($opt_conf)
{
	$opt = array();
	$opt['delibera_mailer_address'] = 'delibera@mail.agoradelibera.com.br';
	$opt['delibera_mailer_user'] = 'delibera@mail.agoradelibera.com.br';
	$opt['delibera_mailer_pass'] = 'gmto8849*z';
	$opt['delibera_mailer_host'] = "zeus.ethymos.com.br";
	$opt['delibera_mailer_port'] = "143";
	$opt['delibera_mailer_ssl'] = false;
	$opt['delibera_mailer_inbox'] = "INBOX";
	$opt['delibera_mailer_from'] = 'mailer@mail.agoradelibera.com.br';
	$opt['delibera_mailer_from_server'] = 'mail.agoradelibera.com.br';
	$opt['delibera_mailer_reply_assunto'] = __("Resposta ao comentário", 'delibera');
	$opt['delibera_mailer_reply'] = "";
	$opt['delibera_resposta-por-email'] = "S";
	
	
	if(function_exists('qtrans_enableLanguage'))
	{
		global $q_config;
		foreach ($q_config['enabled_languages'] as $lang)
		{
			$opt["delibera_mailer_reply_assunto-$lang"] = $opt['delibera_mailer_reply_assunto'];
			$opt["delibera_mailer_reply-$lang"] = $opt['delibera_mailer_reply'];
			
		}
	}
	if(!is_array($opt_conf)) $opt_conf = array();
	
	$opt = array_merge($opt, $opt_conf);

	return $opt;
}
add_filter('delibera_get_config', 'delibera_mailer_get_config');

function delibera_mailer_create_table($table_name)
{
	global $wpdb;
	
	if (!empty ($wpdb->charset))
	$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	if (!empty ($wpdb->collate))
	$charset_collate .= " COLLATE {$wpdb->collate}";
	
	$sql = "";
	
	//TODO: Será que precisa armazenar os e-mail originais?
	/*if(strpos($table_name, 'mailer_box') !== false)
	{
	
		$sql = "CREATE TABLE {$table_name} (
				  	id BIGINT(20) NOT NULL AUTO_INCREMENT,
					subject varchar(1000) NOT NULL,
		 			topic varchar(1000) NOT NULL,
					mail_from varchar(1000) NOT NULL,
					text MEDIUMTEXT DEFAULT NULL,
					mail_to varchar(1000) NOT NULL,
		 			date timestamp DEFAULT NOW(),
		 			status varchar(2) DEFAULT 'U',
				  	UNIQUE KEY id (id)
				) {$charset_collate};";
	}
	else*/
	{
		$sql = "CREATE TABLE {$table_name} (
			  	id BIGINT(20) NOT NULL AUTO_INCREMENT,
			  	mail_key varchar(40) not null,
				user_id INT NOT NULL,
				blog_id INT NOT NULL,
				comment_id INT NOT NULL,
			  	UNIQUE KEY id (id)
			) {$charset_collate};
		";
	}
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

function delibera_mailer_instalacao()
{
	global $wpdb;
	/*$table_name = $wpdb->base_prefix . 'mailer_box';
	if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name) {
		delibera_mailer_create_table($table_name);
	}*/
	$table_name = $wpdb->base_prefix . 'delibera_mailer_keys';
	if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name) {
		delibera_mailer_create_table($table_name);
	}
}
//register_activation_hook('delibera.php','delibera_mailer_instalacao');


add_action('admin_init','delibera_mailer_instalacao');


function delibera_mailer_randomAlphaNum($length)
{
	$ret = '';
	for($i = 0; $i < $length;$i++)
	{
		$base10Rand = mt_rand(0, 36);
		$newRand = base_convert($base10Rand, 10, 36);
		$ret .= $newRand;
	}
	 
	return $ret;
}

/**
 *
 * Resposta de uma comentário
 * @param int $id
 * @return int
 */
function delibera_mailer_reply($id)
{
	$comment_parent = 0;
	$comment_post = 0;
	$isreply = 0;
	if(
		(int) mysql_escape_string($_POST['comment_parent']) === 0 ||
		(int) mysql_escape_string($_POST['comment_post_ID']) === 0
	)
	{
		if (isset($_POST['action']) && $_POST['action'] == 'replyto-comment' && isset($_POST['comment_ID']))
		{
			$isreply = 1;
		}
		if ($isreply == 0)
		{
			//return $id;
		}
		$comment_parent = mysql_escape_string($_POST['comment_ID']);
		$comment_post = mysql_escape_string($_POST['comment_post_ID']);
	}
	else
	{
		$comment_parent = mysql_escape_string($_POST['comment_parent']);
		$comment_post = mysql_escape_string($_POST['comment_post_ID']);
	}
	//echo ("comment_parent: $comment_parent, comment_post: $comment_post, ");
	$options_plugin_delibera = delibera_get_config();
	//if($options_plugin_delibera["delibera_resposta-por-email"] == "Y" && $isreply)
	{
		delibera_mailer_mailer($id,$comment_parent,$comment_post);
	}
	return $id;
}

add_action('delibera_nova_interacao', 'delibera_mailer_reply');

/**
 * 
 * Disparar E-mails
 * @param int $id comment id
 * @param int $comment_parent
 * @param int $comment_post
 * @param string $tipo
 */
function delibera_mailer_mailer($id,$comment_parent,$comment_post, $tipo = 'reply')
{
	require( ABSPATH . WPINC . '/pluggable.php' );
	add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	
	$comment = get_comment($id);
	$post = get_post($comment_post);
	$comment_parent = get_comment($comment_parent);
	
	if($post->post_status == 'publish')
	{
	
		$options_plugin_delibera = delibera_get_config();
	
		if(
			$options_plugin_delibera["reposta-por-email"] == "N" // Notificações estão desabilitadas OU
		)
		{
			return false;
		}
		
		$subject_default = htmlspecialchars_decode($options_plugin_delibera["mailer_{$tipo}_assunto"]);
		$mensage_default = '';
	
		$users = get_users();
	
		if(!is_array($users))
		{
			$users = array();
		}
	
		$seguiram = delibera_get_quem_seguiu($post->ID, 'ids');
	
		foreach ($users as $user)
		{
			if(user_can($user->ID, 'votar') && isset($user->user_email))
			{
				$segue = array_search($user->ID, $seguiram);
	
				$user_notificacoes = get_user_meta($user->ID, 'delibera_resposta_email', true);
	
				if($user_notificacoes == "N" && !$segue)
				{
					continue;
				}
				else
				{
					$mensage_tmp = $mensage_default;
					$subject_tmp = $subject_default;
					if(function_exists('qtrans_enableLanguage'))
					{
						$lang = get_user_meta($user->ID, 'user_idioma', true);
						
						if(strlen($lang) == 0) $lang = defined('WPLANG') && strlen(WPLANG) > 0 ? WPLANG : get_locale();
						
						if(array_key_exists("mailer_$tipo-$lang", $options_plugin_delibera))
						{
							$mensage_tmp = htmlspecialchars_decode($options_plugin_delibera["mailer_$tipo-$lang"]).delibera_notificar_get_mensagem_link($post);
						}
						if(array_key_exists("mailer_{$tipo}_assunto-$lang", $options_plugin_delibera))
						{
							$subject_tmp = htmlspecialchars_decode($options_plugin_delibera["mailer_{$tipo}_assunto-$lang"]);
						}
					}
					
					$key = delibera_mailer_generateKey($user->ID, $id, get_current_blog_id());
					$from = $options_plugin_delibera['delibera_mailer_from'];
					$from_server = $options_plugin_delibera['delibera_mailer_from_server'];
					
					$header = $headers = 'From: '. $from . "\r\n" .
    					'Reply-To: '.$key.'@'.$from_server. "\r\n" .
    					'X-Mailer: PHP/' . phpversion()
					;
					
					wp_mail($user->user_email, $subject_tmp.' '.get_the_title($comment_post), $mensage_tmp.get_comment_text($id), $header);
					
				}
			}
		}
	}
}

function delibera_mailer_generateKey($user_id, $comment_id, $blog_id)
{
	global $wpdb;
	$table_name = $wpdb->base_prefix . 'delibera_mailer_keys';
	$key = delibera_mailer_randomAlphaNum(40);
	$data = array(
		'user_id' => $user_id,
		'comment_id' => $comment_id,
		'blog_id' => $blog_id,
		'mail_key' => $key
	);
	$rows = $wpdb->insert($table_name, $data);
	return $key;
}

function delibera_mailer_new_comment($comment_post_ID, $comment, $user, $delibera_comment_tipo = 'discussao', $comment_parent = 0, $errors = array())
{
	
	$comment_post_ID = isset($comment_post_ID) ? (int) $comment_post_ID : 0;
	$comment_content = ( isset($comment) ) ? trim($comment) : null;

	$post = get_post($comment_post_ID);
	
	if ( empty($post->comment_status) ) {
		do_action('comment_id_not_found', $comment_post_ID);
		exit;
	}
	
	// get_post_status() will get the parent status for attachments.
	$status = get_post_status($post);
	
	$status_obj = get_post_status_object($status);
	
	if ( !comments_open($comment_post_ID) ) {
		do_action('comment_closed', $comment_post_ID);
		$erros[] = __('Sorry, comments are closed for this item.');
	} elseif ( 'trash' == $status ) {
		do_action('comment_on_trash', $comment_post_ID);
		exit;
	} elseif ( !$status_obj->public && !$status_obj->private ) {
		do_action('comment_on_draft', $comment_post_ID);
		exit;
	} elseif ( post_password_required($comment_post_ID) ) {
		do_action('comment_on_password_protected', $comment_post_ID);
		exit;
	} else {
		do_action('pre_comment_on_post', $comment_post_ID);
	}
	
	// If the user is logged in
	if ( $user->ID )
	{
		if ( empty( $user->display_name ) )
			$user->display_name=$user->user_login;
			
		$comment_author       = $wpdb->escape($user->display_name);
		$comment_author_email = $wpdb->escape($user->user_email);
		$comment_author_url   = $wpdb->escape($user->user_url);
		
	}
	
	
	$comment_type = '';
	
	if ( '' == $comment_content )
		$erros[] = ( __('<strong>ERROR</strong>: please type a comment.') );
	
	$comment_parent = isset($comment_parent) ? absint($comment_parent) : 0;
	
	$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');
	
	//global $_POST;
	//$_POST = array();
	//$_POST['delibera_comment_tipo'] = $delibera_comment_tipo;
	//$_POST['delibera_encaminha'] = $delibera_encaminha;
	
	$comment_id = wp_new_comment( $commentdata );
	
	$ret = new stdClass;
	$ret->comment_id = $comment_id;
	$ret->errors = $errors; 
	
	return $ret;
}

/**
 * Detecta o tipo do comentário e set a variábel global $_POST
 */
function delibera_mailer_comment_type($text, $parent_comment)
{
	global $_POST;
	$_POST = array();
	
	$tipo = delibera_get_situacao($parent_comment->comment_post_ID);
	
	switch($tipo)
	{
		case "validacao":
		{
			$_POST['delibera_validacao'] = "S";
		}break;
		
		case 'discussao':
		case 'encaminhamento':
		{
			$_POST['delibera_encaminha'] = "S";
			$_POST['delibera-baseouseem'] = '';
		}break;
		case 'voto':
		{
			
			foreach ($_POST as $postkey => $postvar)
			{
				if( substr($postkey, 0, strlen('delibera_voto')) == 'delibera_voto' )
				{
					$votos[] = $postvar;
				}
			}
			
		} break;
	}
}

function delibera_mailer_readBox()
{
	global $wpdb;
	$table_name = $wpdb->base_prefix . 'delibera_mailer_keys';
	$config = delibera_get_config();
	$dmr = new delibera_mailer_read();
	
	$c = $dmr->imap_login(
		$config['delibera_mailer_host'],
		$config['delibera_mailer_port'],
		$config['delibera_mailer_user'],
		$config['delibera_mailer_pass'],
		$config['delibera_mailer_inbox'],
		$config['delibera_mailer_ssl']
	);
	$stat = $dmr->pop3_stat($c);
	if(is_array($stat) && $stat['Unread'] > 0)
	{
		for($i = 1; $i < $stat['Unread']; $i++)
		{
			$mail = $dmr->mail_mime_to_array($c, $i, true);
			$key = $mail[0]['parsed']['To'];
			$key = substr($key, 0, strpos($key, '@'));
			$from = str_replace(array('<','>'), '', $mail[0]['parsed']['Return-Path']);
			
			$keys = $wpdb->get_results( "SELECT * FROM $table_name where mail_key='$key' ");
			
			if(count($keys) > 0)
			{
				foreach ( $keys as $value )
				{
					switch_to_blog($value->blog_id);
      				$user = get_userdata($value->user_id);
      				
					echo "<br/>";
					restore_current_blog();
				}
			}
		}
	}
}

?>