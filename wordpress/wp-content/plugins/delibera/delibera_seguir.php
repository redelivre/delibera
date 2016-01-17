<?php
function delibera_seguir($ID, $type = 'seguir')
{
	$user_id = get_current_user_id();
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if($type == 'seguir')
	{
		$postID = $ID;
		$nseguir = intval(get_post_meta($postID, 'delibera_numero_seguir', true));
		$nseguir++;
		update_post_meta($postID, 'delibera_numero_seguir', $nseguir);
		$seguiram = get_post_meta($postID, 'delibera_seguiram', true);
		if(!is_array($seguiram)) $seguiram = array();
		$hora = time();
		if(!array_key_exists($hora, $seguiram)) $seguiram[$hora] = array();
		$seguiram[$hora][] = array('user' => $user_id, 'ip' => $ip);
		update_post_meta($postID, 'delibera_seguiram', $seguiram);
		return $nseguir;
	}
	elseif($type == 'nao_seguir')
	{
		$postID = $ID;
		$nseguir = intval(get_post_meta($postID, 'delibera_numero_seguir', true));
		$nseguir--;
		update_post_meta($postID, 'delibera_numero_seguir', $nseguir);
		$seguiram = get_post_meta($postID, 'delibera_seguiram', true);
		if(!is_array($seguiram)) $seguiram = array();
		$seguiram2 = array();
		foreach ($seguiram as $hora => $segs)
		{
			foreach ($segs as $user_ip)
			{
				if($user_id != $user_ip['user'])
				{
					if(!array_key_exists($hora, $seguiram2)) $seguiram2[$hora] = array();
					$seguiram2[$hora][] = $seguiram[$hora];
				}
			}
		}
		update_post_meta($postID, 'delibera_seguiram', $seguiram2);
		return $nseguir;
	}
}

function delibera_numero_seguir($ID)
{
	$postID = $ID;
	$nseguir = get_post_meta($postID, 'delibera_numero_seguir', true);
	return $nseguir;
}

function delibera_ja_seguiu($postID, $user_id)
{
	$seguiram = get_post_meta($postID, 'delibera_seguiram', true);
	if(!is_array($seguiram)) $seguiram = array();
	foreach ($seguiram as $hora => $seguiram)
	{
		foreach ($seguiram as $seguiu)
		{
			if(array_key_exists('user', $seguiu) && $user_id == $seguiu['user'])
			{
				return true;
			}
		}
	}
	return false;
}

function delibera_seguir_callback()
{
	if(array_key_exists('seguir_id', $_POST) && array_key_exists('type', $_POST))
	{
		echo delibera_seguir($_POST['seguir_id'], $_POST['type']);
	}
	die();
}
add_action('wp_ajax_delibera_seguir', 'delibera_seguir_callback');
add_action('wp_ajax_nopriv_delibera_seguir', 'delibera_seguir_callback');

function delibera_get_quem_seguiu($ID, $return = 'array')
{
	$seguiram_hora = get_post_meta($ID, 'delibera_seguiram', true);
	
	if(!is_array($seguiram_hora)) $seguiram_hora = array();
	switch($return)
	{
		case 'string':
			$ret = '';
			foreach ($seguiram_hora as $hora => $seguiram)
			{
				foreach ($seguiram as $seguiu)
				{
					if (strlen($ret) > 0) $ret .= ", ";
					$ret .= (($seguiu['user'] == false || $seguiu['user'] == 0) ? $seguiu['ip'] : get_author_name($seguiu['user']));
				}
			}
			return $ret;
		break;
		case 'ids':
			$ids = array();
			foreach ($seguiram_hora as $hora => $seguiram)
			{
				foreach ($seguiram as $seguiu)
				{
					if($seguiu['user'] != false && $seguiu['user'] != 0)
					{
						$ids[] = $seguiu['user'];
					}
				}
			}
			return $ids;
		break;
		case 'array':
		default:
			return $seguiram_hora;
		break;
	}
	
}
?>