<?php
//-------------------------------------- Registered Users Only -------------------------------
require_once 'delibera_registered_users_only.php';
//--------------------------------------End Registered Users Only -------------------------------

//-------------------------------------- Theme selector ------------------------------
require_once 'delibera_theme_selector.php';
//--------------------------------------End Theme selector ------------------------------

//-------------------------------------- Pricing plan restriction ------------------------------
require_once 'delibera_plan_restriction.php';
//--------------------------------------End Pricing plan restriction ------------------------------

global $product_list;
global $wpdb;


$product_list = $wpdb->get_results("SELECT * FROM `wp_wpsc_product_list` WHERE `active` IN('1') AND `publish` IN('1') LIMIT 1",'ARRAY_A');



function get_current_plan(){
	global $product_list;

	$blogid = get_current_blog_id();

	$princing_plan = get_blog_option($blogid,'product_id');



	if(!empty($product_list)) {
		foreach ($product_list as $plan) {
			if($plan['id']==$princing_plan)
			{

				return $plan['name'];
			}
		}
	}
}


function get_plan_name($planid){
	global $product_list;

	if(!empty($product_list)) {
		foreach ($product_list as $plan) {
			if($plan['id']==$planid)
				return $plan['name'];
		}
	}

}



function get_plan_id($plan_name){
	global $product_list;

	if(!empty($product_list)) {
		foreach ($product_list as $plan) {
			if($plan['name']==$plan_name)
				return $plan['id'];
		}
	}

}


function get_delibera_role($blogid){


	$princing_plan = get_blog_option($blogid,'product_id');


	switch ($princing_plan) {
		case 2:
			return 'miscenas';
			break;

		case 3:
			return 'creta';
			break;
		case 4:
			return 'atenas';
			break;
		default:
			return 'caucaso';
			break;
	}

}

function get_current_plan_expire_date(){

	$blogid = get_current_blog_id();
	$princing_plan = get_blog_option($blogid,'product_id');
		
	$admin_email = get_blog_option($blogid, 'admin_email');
	$user =  get_user_by_email($admin_email);

	$role = get_delibera_role($blogid);


	if($role == 'caucaso')
		return __('Ilimitado','delibera');

	$user_id = $user->ID;
	$subscription_lengths = get_user_meta($user_id, '_subscription_starts',true); //get_user_meta($user->ID, '_subscription_length',true);
	$start = $subscription_lengths[$role];


	return date('d M Y',$start+strtotime('1 year'));


}

function delibera_plan_config_page()
{
?>	
	<div class="postbox-container" style="width:80%;">
	<div class="metabox-holder">
	<div class="meta-box-sortables">
	
	<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" id="delibera-plan-config" >
	<?php
		
	//	wpsc_empty_cart();
		
		
		 	
	/*$selected_atenas = ''; $selected_creta = ''; $selected_miscenas ='';
	 $selected = get_option('pricing_plan');
	
	echo "selected ".$selected."<br/>";
	
	if($selected == 4)
		$selected_atenas="selected";
	if($selected == 3)
		$selected_creta="selected";
	if($selected == 2)
		$selected_miscenas="selected";
	*/
		
	/*	$pricing_plan = get_option('pricing_plan');
	 $select_pid = '<select name="product_id" id="product_id">';
	while (wpsc_have_products()) : wpsc_the_product();
	$product_id = wpsc_the_product_id();
	$select_pid .= '<option value="'.$product_id.'"';
		
	if($pricing_plan == $product_id) $select_pid .= ' selected ';
		
	$select_pid .='>'.wpsc_the_product_title().'</option>';
	
	endwhile;
	
	$select_pid .= '</select>';		*/
		
		
	$rows = array();
	$rows[] = array(
			"id" => "current_plan",
			"label" => __('O seu plano actual é:', 'delibera'),
			"content" => '<input type="text" name="current_plan" id="current_plan" value="'.get_current_plan().'" disabled/>'
	);
	$rows[] = array(
			"id" => "expire_date",
			"label" => __('Válido até:', 'delibera'),
			"content" => '<input type="text" name="current_plan" id="current_plan" value="'.get_current_plan_expire_date().'" disabled/>'
	);
		
	/*	$rows[] = array(
	 "id" => "product_id",
			"label" => __( 'Escolha novo plano Delibers','delibera'),
			"content" => '<select name="pricing_plan" id="pricing_plan" value="<?php  echo esc_attr($pricing_plan) ?>">
			<option value="4"'.$selected_atenas.'>Atenas</option>
			<option value="3"'.$selected_creta.'>Creta</option>
			<option value="2" '.$selected_miscenas.'>Miscenas</option>
			</select>'
	);	*/
		
	$table = delibera_form_table($rows);
		
	// echo $table.'<div class="submit"><input type="submit" class="button-primary" name="submit" value="'.__('Actualizar Plano','delibera').'" /></form></div>';
	echo $table;
	//	wpsc_add_to_cart();
	?>
			
						
				
						
				</form>
			</div>
		</div>
		</div>
	<?php
}

add_action('delibera_config_page_extra', 'delibera_plan_config_page');

?>
