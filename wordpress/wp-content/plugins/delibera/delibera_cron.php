<?php 
/*if(array_key_exists("delibera_cron_action", $_REQUEST) && !defined('DOING_DELIBERA_CRON'))
{
	ignore_user_abort(true);
	define('DOING_DELIBERA_CRON', true);
	delibera_cron_action();
}*/

function delibera_cron_action()
{
	ignore_user_abort(true);
	define('DOING_DELIBERA_CRON', true);
	try
	{
		$crons =  get_option('delibera-cron', array());
		$new_crons = array();
		$now = time();
		$exec = 0;
		foreach ($crons as $key => $values)
		{
			if($key <= $now)
			{
				foreach ($values as $value)
				{
					$exec++;
					if(function_exists($value['call_back']))
					{
						call_user_func($value['call_back'], $value['args']);
					}
				}
			}
			else
			{
				$new_crons[$key] = $values;
			}
		}
		update_option('delibera-cron', $new_crons);
	}
	catch (Exception $e)
	{
		$error = __('Erro no cron Delibera: ','delibera').$e->getMessage()."\n".$e->getCode()."\n".$e->getTraceAsString()."\n".$e->getLine()."\n".$e->getFile();
		wp_mail("jacson@ethymos.com.br", get_bloginfo('name'), $error);
	}
	//wp_mail("jacson@ethymos.com.br", get_bloginfo('name'),"Foram executadas $exec tarefa(s)");
}

add_action('admin_action_delibera_cron_action', 'delibera_cron_action');

function delibera_cron_registry()
{
	if ( !wp_next_scheduled( 'admin_action_delibera_cron_action' ) )
	{
		wp_schedule_event(time(), 'hourly', 'admin_action_delibera_cron_action');
	}
}
add_action('wp', 'delibera_cron_registry');

function delibera_cron_list()
{
	$crons =  get_option('delibera-cron', array());
	foreach ($crons as $key => $values)
	{
		echo "\n<br/>[$key]: ".date("d/m/Y H:i:s", $key);
		foreach ($values as $key2 => $value)
		{
			echo "\n<br/>\t&nbsp;&nbsp;&nbsp;[$key2]";
			foreach ($value as $ki => $item)
			{
				echo "\n<br/>\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[$ki]";
				if(is_array($item))
				{
					echo "\n<br/>\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".print_r($item, true);
				}
				else 
				{
					echo "\n<br/>\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$item";
				}
			}
		}
	}
}

add_action('admin_action_delibera_cron_list', 'delibera_cron_list');

function delibera_add_cron($data, $call_back, $args)
{
	if(is_int($data) && $data > 0)
	{
		$crons =  get_option('delibera-cron', array());
		if(!is_array($crons)) $crons = array();
		
		if(!array_key_exists($data, $crons))
		{
			$crons[$data] = array();
		}
		$crons[$data][] = array('call_back' => $call_back, "args" => $args);
		ksort($crons);
		update_option('delibera-cron', $crons);
	}
}

function delibera_del_cron($postID)
{
	$crons =  get_option('delibera-cron', array());
	if(!is_array($crons)) $crons = array();
	$crons_new = array();
	
	foreach($crons as $cron_data => $cron_value)
	{
		$new_cron = array();
		foreach ($cron_value as $call)
		{
			if($call['args']['post_ID'] != $postID)
			{
				$new_cron[] = $call;
			}
		}
		if(count($new_cron) > 0)
		{
			$crons_new[$cron_data] = $new_cron;
		}
	}
	
	ksort($crons_new);
	update_option('delibera-cron', $crons_new);
}

add_action('delibera_pauta_recusada', 'delibera_del_cron');

?>