<?php

/**
 * On this file we implement plan restriction.
 * We have 5 diferent levels:
 * 
 * Caucaso  - level 1 - until 30  users - max 5 foruns -  no multilang - 1GB space - no private foruns.
 * Miscenas - level 2 - until 120 users - max 20 foruns - multilang    - 2GB space - no private foruns.
 * Creta    - level 3 - until 300 users - max 50 foruns - multilang    - 3GB space - no private foruns.
 * Atenas   - level 4 - unlimited users - unlimited     - multilang    - unlimited - private foruns.
 * Olimpo   - level 5 - unlimited users - unlimited     - multilang    - unlimited - private foruns.  
 */



/*-------------------------------------- Limited Users ----------------------------------------------------*/
	


add_filter('wpmu_validate_user_signup','verify_new_user',10,3);

function verify_new_user($var){

		if(!is_user_logged_in())
			return $var;
		
		$errors = new WP_Error();
		$errors = $var['errors'];

		// get pricing plan level for the current blog
		$blogid = 	get_current_blog_id();
		$princing_plan = get_blog_option($blogid,'product_id');
		
		// get pricing plan level for the current blog
		//$princing_plan = get_option('pricing_plan');	
			
		//get number of users for this blog
		//$users = get_users_of_blog();
		//$nusers = count($users);
		
		$nusers =0;
		global $blog_id;
			
		switch_to_blog($blog_id);
		$adminemail = get_bloginfo('admin_email');
		$admin = get_user_by_email($adminemail);
		restore_current_blog();
		
		$user_blogs = get_blogs_of_user($admin->ID);

		foreach ($user_blogs as $blog)
		{
			
			$bid = $blog->userblog_id ;
			
			if($bid == 1)
				continue; // does not count
			
			
			$email = get_blog_option($bid, 'admin_email');
			
			
			if($email != $adminemail)
				continue;//eh apenas membro
			
			$u = get_users_of_blog($bid);
			$nusers = $nusers+ count($u);
		}
		
		$pname = get_plan_name($princing_plan);
		
				
		switch ($pname)
		{
			case 'Cáucaso': 
				if($nusers == 30)
				{
					//$errors->add('pricing_plan', __('You have reached the maximum number of users. To add a new user pelase upgrade your plan.','delibera'));
					$errors->add('pricing_plan', __('Atingiu o número máximo de usuários permitido. Para adicionar mais usuários por favor actualize o seu plano.','delibera'));	
				}
				break;
			case 'Miscenas':
				if($nusers == 120)
				{
				//	$errors->add('pricing_plan', __('You have reached the maximum number of users. To add a new user pelase upgrade your plan.','delibera'));
					$errors->add('pricing_plan', __('Atingiu o número máximo de usuários permitido. Para adicionar mais usuários por favor actualize o seu plano.','delibera'));
				}
				break;
			case 'Creta':
				if($nusers == 300)
				{
				//	$errors->add('pricing_plan', __('You have reached the maximum number of users. To add a new user pelase upgrade your plan.','delibera'));
					$errors->add('pricing_plan', __('Atingiu o número máximo de usuários permitido. Para adicionar mais usuários por favor actualize o seu plano.','delibera'));
				}
				break;
		}
	
		$var['errors'] =$errors;
		return $var;
}

/*-------------------------------------- End Limited Users ----------------------------------------------------*/




/*-------------------------------------- Limited Forum ----------------------------------------------------*/

add_filter( 'wp_insert_post_data', 'limit_forum',99);

function limit_forum( $data) {
	
		$nposts =0;
		global $blog_id;

		// get pricing plan level for the current blog
		$princing_plan = get_blog_option($blog_id,'product_id');
		
		$current_admin_email  = get_blog_option($bid, 'admin_email');
		$admin = get_user_by_email($current_admin_email);
		$user_blogs = get_blogs_of_user($admin->ID);
		
		foreach ($user_blogs as $blog)
		{
		
			$bid = $blog->userblog_id ;
		
			if($bid == 1)
				continue; // does not count
		
		
			$email = get_blog_option($bid, 'admin_email');
		
		error_log('current admin email '.$current_admin_email);
		error_log('admin deste blog '.$email);
			if($email != $current_admin_email)
				continue;//eh apenas membro
		
			switch_to_blog($bid);
				$qposts = new WP_Query( array(
						'post_type' => 'pauta',
						'post_status' => 'publish'
				) );
				
				if ( $qposts->posts ){
					$nposts = $nposts + count($qposts->posts);
				error_log('para o blog '.$bid).'ja tenho este paustas '.count($qposts->posts);	
				}
			restore_current_blog();
		}
		error_log('total de pautas '.$nposts);
					
		$pname = get_plan_name($princing_plan);		
	
	switch ($pname)
		{
			case 'Cáucaso': 
				if($nposts > 4)
				{
					if($_REQUEST['action'] == '')
					{
//						wp_die(__('You have reached the maximum number of forums. To add a new forum pelase upgrade your plan.','delibera'));
						wp_die(__('Atingiu o número máximo de foruns permitido. Para adicionar novo forum por favor actualize o seu plano.','delibera'));
					}
				}
				break;
			case 'Miscenas':
				if($nposts > 19)
				{
					if($_REQUEST['action'] == '')
					{
					//	wp_die(__('You have reached the maximum number of forums. To add a new forum pelase upgrade your plan.','delibera'));
						wp_die(__('Atingiu o número máximo de foruns permitido. Para adicionar novo forum por favor actualize o seu plano.','delibera'));
					}
				}
				break;
			case 'Creta':
				if($nposts > 49)
				{
					if($_REQUEST['action'] == '')
					{
					//	wp_die(__('You have reached the maximum number of forums. To add a new forum pelase upgrade your plan.','delibera'));
						wp_die(__('Atingiu o número máximo de foruns permitido. Para adicionar novo forum por favor actualize o seu plano.','delibera'));
					}
				}
				break;
		}
		return $data;
}




/*-------------------------------------- End Limited Forum ----------------------------------------------------*/



/*-------------------------------------- MultiLang----------------------------------------------------*/

add_action( 'add_meta_boxes_pauta', 'limit_multilang',10,3 );

function limit_multilang( $post_id ) {
		
	// get pricing plan level for the current blog
	$blogid = 	get_current_blog_id();
	$princing_plan = get_blog_option($blogid,'product_id');
	
	$pname = get_plan_name($princing_plan);		
	
	switch ($pname)
		{
			case 'Cáucaso': 
				remove_meta_box('idiomadiv','pauta','side');
				break;
		}
	// No restrictions for others pricing plans
		return $post_id;
}
/*-------------------------------------- End Multilang ----------------------------------------------------*/



/*-------------------------------------- Private Forum ----------------------------------------------------*/
/*
 * Restriction was directly applied on delibera_registered_users_only.php file. 
 * 
 */
/*-------------------------------------- End Private Forum ----------------------------------------------------*/



/*-------------------------------------- Limited Spance ----------------------------------------------------*/

add_filter( 'wp_handle_upload', 'limit_space',10,1);

function removemediabuttons($cap)
{		
	$pname = get_plan_name($princing_plan);	

	
	$nposts =0;
	global $blog_id;
	
	// get pricing plan level for the current blog
	$princing_plan = get_blog_option($blog_id,'product_id');
	
	$current_admin_email  = get_blog_option($blog_id, 'admin_email');
	$admin = get_user_by_email($current_admin_email);
	$user_blogs = get_blogs_of_user($admin->ID);

	$total = 0;
	if(is_array($user_blogs))
	{
		foreach ($user_blogs as $blog)
		{
		
			$bid = $blog->userblog_id ;
		
			if($bid == 1)
				continue; // does not count
		
		
			$email = get_blog_option($bid, 'admin_email');
		
		
			if($email != $current_admin_email)
				continue;//eh apenas membro
		
			switch_to_blog($bid);
			
			$mu = get_option('media_uploaded');
			if(!$mu)
				$mu=0; //sets to 0 if not yet stored
			$total = $total + $mu;
			
			restore_current_blog();
		}
	}
	
	
	
	switch ($pname)
		{
			case 'Cáucaso': 
				if($total > 1073741824)
				{
					remove_all_actions('media_buttons');
					
				//	$message = __('You have reached 1GB storage. Please upgrade your plan to keep uploading.','delibera');
					$message = __('Atingiu 1GB de upload de arquivos. Para continuar a fazer upload por favor actualize o seu plano.','delibera');
					// if we are in Add New Media page
					if(isset($_GET['inline'])&& $_GET['inline'])
						wp_die($message);		
				}	
				break;
			case 'Miscenas':
				if($total > 2147483648)
				{
					remove_all_actions('media_buttons');
					$message = __('Atingiu 2GB de upload de arquivos. Para continuar a fazer upload por favor actualize o seu plano.','delibera');
					//$message = __('You have reached 2GB storage. Please upgrade your plan to keep uploading.','delibera');
					// if we are in Add New Media page
					if(isset($_GET['inline'])&& $_GET['inline'])
						wp_die($message);	
				}
				break;
			case 'Creta':
				if($total > 3221225472)
				{	
					remove_all_actions('media_buttons');
					$message = __('Atingiu 3GB de upload de arquivos. Para continuar a fazer upload por favor actualize o seu plano.','delibera');
			//		$message = __('You have reached 3GB storage. Please upgrade your plan to keep uploading.','delibera');
					// if we are in Add New Media page
					if(isset($_GET['inline'])&& $_GET['inline'])
						wp_die($message);	
				}
				break;
		}
	return  $cap;
}


add_action('role_has_cap','removemediabuttons',10,1);


//stores size uploaded
function limit_space( $args) {

	$mu = get_option('media_uploaded');
	if(!$mu)
		$mu=0; //sets to 0 if not yet stored

	$now = $mu + filesize($args['file']);
	
	
	update_option('media_uploaded',$now);
	
	return $args;
}




/*-------------------------------------- End Limited Space ----------------------------------------------------*/



?>