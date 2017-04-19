<?php

/**
 * Allowing only registered users
 * */
// create custom plugin settings menu
add_action('admin_menu', 'ruonly_create_menu');

function ruonly_create_menu() {

	//create new top-level menu
	add_menu_page('Register Users Only Settings', 'Registered Users', 'administrator', __FILE__, 'ruonly_settings_page');

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}


function register_mysettings() {
	//register our settings
	register_setting( 'ruonly-settings-group', 'registered_users_only' );
}

function ruonly_settings_page() {
?>
<div class="wrap">


	<?php 	
	// get pricing plan level for the current blog
	$blogid = 	get_current_blog_id();
	$princing_plan = get_blog_option($blogid,'product_id');
	
	//$princing_plan = get_option('pricing_plan');	
		
	$disabled ='';
	if($princing_plan == 1 || $princing_plan == 2 || $princing_plan == 3)
		$disabled="disabled";

	 if($disabled!=''){?>
		<div id="message" class="error"><?php _e('You pricing plan does not allow to se private forums. Please upgrade your plan to do so.','delibera');?></div>
	<?php }
		
		
	?>

<h2><?php _e('Allowing only registeres users.');?></h2>

<form method="post" action="options.php">
    <?php settings_fields( 'ruonly-settings-group' ); ?>
    <?php do_settings_sections('ruonly-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e('Only registered users can access website.','delibera');?> </th>
        
        <?php $value = get_option('registered_users_only');?>
        
        <td><input <?php echo $disabled;?> type="checkbox" name="registered_users_only" <?php if(isset($value) & $value=='on') echo "checked";?> /></td>
     
        </tr>
      
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php }


?>
