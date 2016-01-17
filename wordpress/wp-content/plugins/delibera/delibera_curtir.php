<?php

function delibera_curtir_comment_meta($comment_id)
{
	$ncurtiram = get_comment_meta($comment_id, "delibera_numero_curtir", true);
	if($ncurtiram == false || $ncurtiram == "")
	{
		$ncurtiram = array();
		add_comment_meta($comment_id, 'delibera_numero_curtir', $ncurtiram, true);
	}
	
	$curtiram = get_comment_meta($comment_id, "delibera_curtiram", true);
	if($curtiram == false || $curtiram == "")
	{
		$curtiram = array();
		add_comment_meta($comment_id, 'delibera_curtiram', $curtiram, true);
	}
}

function delibera_curtir($ID, $type = 'pauta')
{
	$user_id = get_current_user_id();
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if(!delibera_ja_curtiu($ID, $user_id, $ip, $type) && !(function_exists('delibera_ja_discordou') && delibera_ja_discordou($ID, $user_id, $ip, $type)) )
	{
		if ($type == 'pauta') {
			$postID = $ID;
			$ncurtir = get_post_meta($postID, 'delibera_numero_curtir', true);
			$ncurtir++;
			update_post_meta($postID, 'delibera_numero_curtir', $ncurtir);
			$curtiram = get_post_meta($postID, 'delibera_curtiram', true);
			if(!is_array($curtiram)) $curtiram = array();
			$hora = time();
			if(!array_key_exists($hora, $curtiram)) $curtiram[$hora] = array();
			$curtiram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_post_meta($postID, 'delibera_curtiram', $curtiram);
		} elseif ($type == 'comment') {
			$comment_id = $ID;
			$ncurtir = intval(get_comment_meta($comment_id, 'delibera_numero_curtir', true));
			$ncurtir++;
			update_comment_meta($comment_id, 'delibera_numero_curtir', $ncurtir);
			$curtiram = get_comment_meta($comment_id, 'delibera_curtiram', true);
			if(!is_array($curtiram)) $curtiram = array();
			$hora = time();
			if(!array_key_exists($hora, $curtiram)) $curtiram[$hora] = array();
			$curtiram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_comment_meta($comment_id, 'delibera_curtiram', $curtiram);
		}

		return sprintf(_n('%d concordou', '%d concordaram', $ncurtir, 'delibera'), $ncurtir);
	}
}

function delibera_numero_curtir($ID, $type ='pauta')
{
	if($type == 'puata')
	{
		$postID = $ID;
		$ncurtir = get_post_meta($postID, 'delibera_numero_curtir', true);
		return $ncurtir;
	}
	elseif($type == 'comment')
	{
		$comment_id = $ID;
		$ncurtir = intval(get_comment_meta($comment_id, 'delibera_numero_curtir', true));
		return $ncurtir;
	}
}

function delibera_ja_curtiu($postID, $user_id, $ip, $type)
{
	$curtiram = array();
	if($type == 'pauta')
	{
		$curtiram = get_post_meta($postID, 'delibera_curtiram', true);
	}
	else 
	{
		$curtiram = get_comment_meta($postID, 'delibera_curtiram', true);
	}
	if(!is_array($curtiram)) $curtiram = array();
	
	foreach ($curtiram as $hora => $curtiuem)
	{
		foreach ($curtiuem as $curtiu)
		{
			if(intval($user_id) == 0 && $ip == $curtiu['ip'])
			{
				return true;
			}
			elseif($user_id == $curtiu['user'])
			{
				return true;
			}
		}
	}
	return false;
}

function delibera_curtir_callback()
{
	if(array_key_exists('like_id', $_POST) && array_key_exists('type', $_POST))
	{
		echo delibera_curtir($_POST['like_id'], $_POST['type']);
	}
	die();
}
add_action('wp_ajax_delibera_curtir', 'delibera_curtir_callback');
add_action('wp_ajax_nopriv_delibera_curtir', 'delibera_curtir_callback');

function delibera_get_quem_curtiu($ID, $type = 'pauta', $return = 'array')
{
	$curtiram = array();
	if($type == 'pauta')
	{
		$curtiram = get_post_meta($ID, 'delibera_curtiram', true);
	}
	else 
	{
		$curtiram = get_comment_meta($ID, 'delibera_curtiram', true);
	}
	if(!is_array($curtiram)) $curtiram = array();
	switch($return)
	{
		case 'string':
			$ret = '';
			foreach ($curtiram as $hora => $curtiuem)
			{
				foreach ($curtiuem as $curtiu)
				{
					if (strlen($ret) > 0) $ret .= ", ";
					$ret .= (($curtiu['user'] == false || $curtiu['user'] == 0) ? $curtiu['ip'] : get_the_author_meta('display_name', $curtiu['user']));
				}
			}
			return $ret;
		break;
		case 'array':
		default:
			return $curtiram;
		break;
	}
	
}
?>