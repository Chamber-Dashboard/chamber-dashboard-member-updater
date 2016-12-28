<?php
/* Options Page */


// Add menu page
function cdmu_add_settings_page() {
	add_submenu_page( '/chamber-dashboard-business-directory/options.php', __('Member Updater License', 'cdmu'), __('Member Updater License', 'cdmu'), 'manage_options', 'cdash-mu', 'cdmu_render_license_key_form' );
}


// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'cdashmu_add_new_user_role')
// --------------------------------------------------------------------------------------

// Add a new user role when the plugin is activated
function cdashmu_add_new_user_role() {
	add_role(
    'business_editor',
    __( 'Business Editor' ),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => true,
        'delete_posts' => false, // Use false to explicitly deny
        'delete_published_posts' => false,
        'upload_files' => true,
        'publish_posts'=> true
    )
    );
}
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
    
    if(!isset($tmp['custom_registration_message'])){
        $tmp['custom_registration_message'] = 'Thank you for registering as a member. You will be able to update your business after you have been approved by the chamber admin. If you have questions, please contact us.';
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
    
    if( !isset($tmp['bus_featured_image_width'])){
        $tmp['bus_featured_image_width'] = '400';
    }
    
    if( !isset($tmp['bus_featured_image_height'])){
        $tmp['bus_featured_image_height'] = '400';
    }
    
    if( !isset($tmp['additional_admin_email'])){
        $tmp['additional_admin_email'] = '';
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
		'custom_registration_message', 
		__( 'Custom Registration Message', 'cdashmu' ), 
		'cdashmu_custom_registration_message_page_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Enter the message you would like your users to see after they sign up as a user connected to a business.', 'cdashmu' )
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
    
    add_settings_field( 
		'bus_featured_image_width', 
		__( 'Business Featured Image Width', 'cdashmu' ), 
		'cdashmu_business_featured_image_width_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Here you can specify the maximum width of the featured image that the businesses can upload. The default is 400px.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_featured_image_height', 
		__( 'Business Featured Image height', 'cdashmu' ), 
		'cdashmu_business_featured_image_height_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'Here you can specify the maximum height of the featured image that the businesses can upload. The default is \400px.', 'cdashmu' )
		)
	);


    
    add_settings_field( 
		'additional_admin_email', 
		__( 'Additional Admin Email', 'cdashmu' ), 
		'cdashmu_additional_admin_email_page_render', 
		'cdashmm_plugin_options', 
		'cdashmu_options_section',
		array(
			__( 'If you would like the new user registration emails to go to a different email than the admin email, please enter it here.', 'cdashmu' )
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

function cdashmu_custom_registration_message_page_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<!--<input type='textarea' name='cdashmm_options[custom_registration_message]' value='<?php echo $options['custom_registration_message']; ?>'>
	<br />--><span class="description"><?php echo $args[0]; ?></span>
	<?php

		$args = array("wpautop" => false, "media_buttons" => true, "textarea_name" => "cdashmm_options[custom_registration_message]", "textarea_rows" => "5");

		wp_editor( $options['custom_registration_message'], "registration", $args );

	?>
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

function cdashmu_business_featured_image_width_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[bus_featured_image_width]' value='<?php echo $options['bus_featured_image_width']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_featured_image_height_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[bus_featured_image_height]' value='<?php echo $options['bus_featured_image_height']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}


function cdashmu_additional_admin_email_page_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='email' name='cdashmm_options[additional_admin_email]' value='<?php echo $options['additional_admin_email']; ?>'">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}



function cdashmu_options_section_callback(  ) { 

	echo __( 'Chamber Dashboard Member Updater General Settings', 'cdashmu' );

}

function cdmu_render_license_key_form(){
    //echo "This is where the license key form will go.";
}
?>