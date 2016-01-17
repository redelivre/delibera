<?php

function delibera_discordar_comment_meta($comment_id)
{
	$ndiscordaram = get_comment_meta($comment_id, "delibera_numero_discordar", true);
	if($ndiscordaram == false || $ndiscordaram == "")
	{
		$ndiscordaram = array();
		add_comment_meta($comment_id, 'delibera_numero_discordar', $ndiscordaram, true);
	}
	
	$discordaram = get_comment_meta($comment_id, "delibera_discordaram", true);
	if($discordaram == false || $discordaram == "")
	{
		$discordaram = array();
		add_comment_meta($comment_id, 'delibera_discordaram', $discordaram, true);
	}
}

function delibera_discordar($ID, $type ='pauta')
{
	$user_id = get_current_user_id();
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if(!delibera_ja_discordou($ID, $user_id, $ip, $type) && !(function_exists('delibera_ja_curtiu') && delibera_ja_curtiu($ID, $user_id, $ip, $type)) )
	{
		if($type == 'pauta')
		{
			$postID = $ID;
			$ndiscordar = get_post_meta($postID, 'delibera_numero_discordar', true);
			$ndiscordar++;
			update_post_meta($postID, 'delibera_numero_discordar', $ndiscordar);
			$discordaram = get_post_meta($postID, 'delibera_discordaram', true);
			if(!is_array($discordaram)) $discordaram = array();
			$hora = time();
			if(!array_key_exists($hora, $discordaram)) $discordaram[$hora] = array();
			$discordaram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_post_meta($postID, 'delibera_discordaram', $discordaram);
		}
		elseif($type == 'comment')
		{
			$comment_id = $ID;
			$ndiscordar = intval(get_comment_meta($comment_id, 'delibera_numero_discordar', true));
			$ndiscordar++;
			update_comment_meta($comment_id, 'delibera_numero_discordar', $ndiscordar);
			$discordaram = get_comment_meta($comment_id, 'delibera_discordaram', true);
			if(!is_array($discordaram)) $discordaram = array();
			$hora = time();
			if(!array_key_exists($hora, $discordaram)) $discordaram[$hora] = array();
			$discordaram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_comment_meta($comment_id, 'delibera_discordaram', $discordaram);
		}
		
		return sprintf(_n('%d discordou', '%d discordaram', $ndiscordar, 'delibera'), $ndiscordar);
	}
}

function delibera_numero_discordar($ID, $type ='pauta')
{
	if($type == 'puata')
	{
		$postID = $ID;
		$ndiscordar = get_post_meta($postID, 'delibera_numero_discordar', true);
		return $ndiscordar;
	}
	elseif($type == 'comment')
	{
		$comment_id = $ID;
		$ndiscordar = intval(get_comment_meta($comment_id, 'delibera_numero_discordar', true));
		return $ndiscordar;
	}
}

function delibera_ja_discordou($postID, $user_id, $ip, $type)
{
	$discordaram = array();
	if($type == 'pauta')
	{
		$discordaram = get_post_meta($postID, 'delibera_discordaram', true);
	}
	else 
	{
		$discordaram = get_comment_meta($postID, 'delibera_discordaram', true);
	}
	if(!is_array($discordaram)) $discordaram = array();
	
	foreach ($discordaram as $hora => $discordouem)
	{
		foreach ($discordouem as $discordou)
		{
			if(intval($user_id) == 0 && $ip == $discordou['ip'])
			{
				return true;
			}
			elseif($user_id == $discordou['user'])
			{
				return true;
			}
		}
	}
	return false;
}

function delibera_discordar_callback()
{
	if(array_key_exists('like_id', $_POST) && array_key_exists('type', $_POST))
	{
		echo delibera_discordar($_POST['like_id'], $_POST['type']);
	}
	die();
}
add_action('wp_ajax_delibera_discordar', 'delibera_discordar_callback');
add_action('wp_ajax_nopriv_delibera_discordar', 'delibera_discordar_callback');

function delibera_get_quem_discordou($ID, $type = 'pauta', $return = 'array')
{
	$discordaram = array();
	if($type == 'pauta')
	{
		$discordaram = get_post_meta($ID, 'delibera_discordaram', true);
	}
	else 
	{
		$discordaram = get_comment_meta($ID, 'delibera_discordaram', true);
	}
	if(!is_array($discordaram)) $discordaram = array();
	switch($return)
	{
		case 'string':
			$ret = '';
			foreach ($discordaram as $hora => $discordouem)
			{
				foreach ($discordouem as $discordou)
				{
					if (strlen($ret) > 0) $ret .= ", ";
					$ret .= (($discordou['user'] == false || $discordou['user'] == 0) ? $discordou['ip'] : get_author_name($discordou['user']));
				}
			}
			return $ret;
		break;
		case 'array':
		default:
			return $discordaram;
		break;
	}
	
}
?>