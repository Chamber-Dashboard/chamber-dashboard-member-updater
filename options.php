<?php
/* Options Page */


// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'cdashmu_delete_plugin_options')
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function cdashmu_delete_plugin_options() {
	delete_option('cdashmu_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'cdashmu_add_defaults')
// ------------------------------------------------------------------------------

// Define default option settings
function cdashmu_add_defaults() {
	$tmp = get_option('cdashmm_options');
	
	if( !isset( $tmp['user_registration_page'] ) ) {
		$tmp['user_registration_page'] = get_bloginfo( 'home' );
	}
	
	if( !isset( $tmp['user_login_page'] ) ) {
		$tmp['user_login_page'] = '';
	}
    
    if( !isset( $tmp['business_update_page'] ) ) {
		$tmp['business_update_page'] = '';
	}
    
    if( !isset($tmp['bus_logo_image_width'])){
        $tmp['bus_logo_image_width'] = '200';
    }
    
    if( !isset($tmp['bus_logo_image_height'])){
        $tmp['bus_logo_image_height'] = '200';
    }
	
	update_option( 'cdashmm_options', $tmp );
}

add_action( 'admin_init', 'cdashmu_options_init' );

function cdashmu_options_init(  ) { 

	add_settings_section(
		'cdashmu_options_section', 
		__( 'Member Updater Settings', 'cdashmu' ), 
		'cdashmu_options_section_callback', 
		'cdashmm_plugin_options'
	);
	
	add_settings_field( 
		'user_registration_page', 
		__( 'User Registration Page', 'cdashmu' ), 
		'cdashmu_user_registration_page_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Enter the url for your user registration page. Members will be directed to this page after they join.', 'cdashmu' )
		)
	);
	
	add_settings_field( 
		'user_login_page', 
		__( 'User Login Page', 'cdashmu' ), 
		'cdashmu_user_login_page_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Enter the url for your user login page. Members will be directed to this page after they register as a user.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'business_update_page', 
		__( 'Business Update Page', 'cdashmu' ), 
		'cdashmu_business_update_page_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Enter the url for your business update page. Members will be able to edit/update their business here after they login.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_logo_image_width', 
		__( 'Business Logo Image Width', 'cdashmu' ), 
		'cdashmu_business_logo_image_width_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Here you can specify the maximum width of the logo image that the businesses can upload. The default is 200px.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_logo_image_height', 
		__( 'Business Logo Image Height', 'cdashmu' ), 
		'cdashmu_business_logo_image_height_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Here you can specify the maximum height of the logo image that the businesses can upload. The default is 200px.', 'cdashmu' )
		)
	);
}

function cdashmu_user_registration_page_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[user_registration_page]' value='<?php echo $options['user_registration_page']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_user_login_page_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[user_login_page]' value='<?php echo $options['user_login_page']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_update_page_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[business_update_page]' value='<?php echo $options['business_update_page']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_logo_image_width_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[bus_logo_image_width]' value='<?php echo $options['bus_logo_image_width']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_logo_image_height_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[bus_logo_image_height]' value='<?php echo $options['bus_logo_image_height']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}



function cdashmu_options_section_callback(  ) { 

	echo __( 'Chamber Dashboard Member Updater General Settings', 'cdashmu' );

}
?>