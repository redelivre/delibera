<?php

class delibera_timeline
{
	public function get_dados($postID = false, $tipo_data = false)
	{
		$pautas = '';
		$comments = '';
		if($postID === false)
		{
			$pautas = delibera_get_pautas_em();
			$comments = delibera_wp_get_comments();
		}
		else 
		{
			$pautas = array(get_post($postID));
			$comments = delibera_wp_get_comments(array('post_id' => $postID));
		}
		
		$events = array();
		foreach($pautas as $pauta)
		{
			$data = strtotime($pauta->post_date_gmt);
			if(!array_key_exists($data, $events))
			{
				$events[$data] = array();
			}
			$events[$data][] = array(
				'type' => 'pauta',
				'title' => get_the_title($postID),
				'body' =>  apply_filters('the_content', $pauta->post_content),
				//'date_event' => date('d/m/Y H:i:s', $data)
				'date_event' => $data
			); 
		}
		foreach($comments as $comment)
		{
			$data = strtotime($comment->comment_date_gmt);
			if(!array_key_exists($data, $events))
			{
				$events[$data] = array();
			}
			$events[$data][] = array(
				'type' => 'comment-'.get_comment_meta($comment->comment_ID, "delibera_comment_tipo", true),
				'title' => "@".$comment->comment_author,
				'body' => apply_filters('comment_text', get_comment_text($comment->comment_ID)),
				//'date_event' => date('d/m/Y H:i:s', $data)
				'date_event' => $data
			); 
		}
		ksort($events, SORT_NUMERIC);
		
		return $this->filtrar($events, $tipo_data);
	}
	
	public function get_config()
	{
		$opt = array();
		$opt['tipo_data'] = 'quinzenal';
		
		$opt_conf = get_option('delibera-timeline-config', array());
		if(!is_array($opt_conf)) $opt_conf = array();
		$opt = array_merge($opt, $opt_conf);
		
		return $opt;
	}
	
	public function filtrar($events, $tipo_data = false)
	{
		$new_events = array();
		if($tipo_data === false)
		{
			$opt = $this->get_config();
			$tipo_data = $opt['tipo_data']; 
		}
		switch ($tipo_data)
		{
			case 'mensal':
				foreach ($events as $time => $event)
				{
					$mes = date('m', $time);
					if(!array_key_exists($mes, $new_events))
					{
						$new_events[$mes] = array();
					}
					$new_events[$mes] = array_merge($new_events[$mes], $event);
				}
			break;
			case 'quinzenal':
				foreach ($events as $time => $event)
				{
					$mes = date('m', $time);
					$dia = date('d', $time) > 15 ? 2 : 1;
					if(!array_key_exists("$mes/$dia", $new_events))
					{
						$new_events["$mes/$dia"] = array();
					}
					$new_events["$mes/$dia"] = array_merge($new_events["$mes/$dia"], $event);
				}
			break;
			case 'semanal':
				foreach ($events as $time => $event)
				{
					$semana = date('W', $time);
					if(!array_key_exists($semana, $new_events))
					{
						$new_events[$semana] = array();
					}
					$new_events[$semana] = array_merge($new_events[$semana], $event);
				}
			break;
			case 'diario':
				foreach ($events as $time => $event)
				{
					$mes = date('m', $time);
					$dia = date('d', $time);
					if(!array_key_exists("$dia/$mes", $new_events))
					{
						$new_events["$dia/$mes"] = array();
					}
					$new_events["$dia/$mes"] = array_merge($new_events["$dia/$mes"], $event);
				}
			break;
		}
		return $new_events;
	}
	
	public function generate($postID = false, $tipo_data = false)
	{
		$dates = $this->get_dados($postID, $tipo_data);
		$colors = array('green','blue','chreme');
		$scrollPoints = '';
		
		$i=0;
		$html = '<div id="timelineLimiter"> <!-- Hides the overflowing timelineScroll div -->
			    	<div id="timelineScroll"> <!-- Contains the timeline and expands to fit -->';
		foreach($dates as $year=>$array)
		{
			// Loop through the years:
		
			$html .= '<div class="event">
					<div class="eventHeading '.$colors[$i++%3].'">'.$year.'</div>
						<ul class="eventList">';
		
			foreach($array as $event)
			{
				// Loop through the events in the current year:
		
				$html .= '<li class="'.$event['type'].'">
					<span class="icon" title="'.ucfirst($event['type']).'"></span>
					'.htmlspecialchars($event['title']).'
		
		 			<div class="timeline_content">
						<div class="timeline_body">'.($event['type']=='image'?'<div style="text-align:center"><img src="'.$event['body'].'" alt="Image" /></div>':nl2br($event['body'])).'</div>
						<div class="timeline_title">'.htmlspecialchars($event['title']).'</div>
						<div class="timeline_date">'.date("F j, Y",$event['date_event']).'</div>
					</div>
		 			</li>';
			}
		
			$html .= '</ul></div>';
		
		 	// Generate a list of years for the time line scroll bar:
			$scrollPoints.='<div class="scrollPoints">'.$year.'</div>';
		}
			$html_bar_head ='
	        <div class="clear"></div>
	        </div>
	        
	        <div id="hscroll" class="scroll"> <!-- The year time line -->
	            <div id="hcentered" class="centered"> <!-- Sized by jQuery to fit all the years -->
		            <div id="hhighlight" class="highlight"></div> <!-- The light blue highlight shown behind the years -->
		            	'.$scrollPoints.'  <!-- This PHP variable holds the years that have events -->
	                <div class="clear"></div>
	            </div>
	        </div>
	        
	        <div id="hslider" class="slider"> <!-- The slider container -->
	        	<div id="hbar" class="bar"> <!-- The bar that can be dragged -->
	            	<div id="hbarLeft" class="barLeft"></div>  <!-- Left arrow of the bar -->
	                <div id="hbarRight" class="barRight"></div>  <!-- Right arrow, both are styled with CSS -->
	          </div>
	    	</div>
	    	';
			$html_bar_tail = '
	        <div class="clear"></div>
	        </div>
	        
	        <div id="scroll" class="scroll"> <!-- The year time line -->
	            <div id="centered" class="centered"> <!-- Sized by jQuery to fit all the years -->
		            <div id="highlight" class="highlight"></div> <!-- The light blue highlight shown behind the years -->
		            	'.$scrollPoints.' <!-- This PHP variable holds the years that have events -->
	                <div class="clear"></div>
	            </div>
	        </div>
	        
	        <div id="slider" class="slider"> <!-- The slider container -->
	        	<div id="bar" class="bar"> <!-- The bar that can be dragged -->
	            	<div id="barLeft" class="barLeft"></div>  <!-- Left arrow of the bar -->
	                <div id="barRight" class="barRight"></div>  <!-- Right arrow, both are styled with CSS -->
	          </div>
	    	</div>
	    	'; 
		echo $html_bar_head.$html.$html_bar_tail.'</div>';
	}
	
}

add_filter('query_vars', 'timeline_variables');
function timeline_variables($public_query_vars) {
	$public_query_vars[] = 'delibera_timeline';
	$public_query_vars[] = 'delibera_timelinepage';
	return $public_query_vars;
}

function delibera_timeline_template_redirect()
{
	if(intval(get_query_var('delibera_timeline')) == 1 || intval(get_query_var('delibera_timelinepage')) == 1)
	{
		wp_enqueue_style('delibera_timeline_css',  WP_CONTENT_URL.'/plugins/delibera/timeline/delibera_timeline.css');
		wp_enqueue_script( 'delibera_timeline_js', WP_CONTENT_URL.'/plugins/delibera/timeline/js/delibera_timeline.js', array( 'jquery' ));
		wp_enqueue_script( 'jquery-ui-draggable');
		echo delibera_timeline(get_the_ID());
	}
}

add_action('template_redirect', 'delibera_timeline_template_redirect', 5);

new delibera_timeline();

function delibera_timeline($post_id = false, $tipo_data = false)
{
	$timeline = new delibera_timeline();
	$timeline->generate($post_id, $tipo_data);
}

function delibera_replace_timeline($args)
{
	$atts = array('post_id' => false, 'tipo_data' => false);
	$atts = array_merge($atts, $args);

	return delibera_timeline($atts['post_id'], $atts['tipo_data']);
}
add_shortcode( 'delibera_timeline', 'delibera_replace_timeline' );

?>