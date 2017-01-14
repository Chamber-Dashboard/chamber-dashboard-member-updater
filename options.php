<?php
/* Options Page for Chamber Dashboard Member Updater */

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'cdashmu_delete_plugin_options')
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function cdashmu_delete_plugin_options() {
	delete_option('cdashmu_options');
    delete_option( 'cdash_mu_edd_license_key' );
	delete_option( 'cdash_mu_edd_license_status' );
}

// --------------------------------------------------------------------------------------
//  REDIRECTING THE PLUGIN TO THE LICENSE PAGE AFTER ACTIVATION
// --------------------------------------------------------------------------------------


function cdashmu_plugin_activate(){
    add_option('cdashmu_do_activation_redirect', true);
}

function cdashmu_plugin_redirect(){
    if (get_option('cdashmu_do_activation_redirect', false)) {
        delete_option('cdashmu_do_activation_redirect');
        if(!isset($_GET['activate-multi']))
        {
            wp_safe_redirect(admin_url('admin.php?page=cdash-mu&tab=cdashmu_license_page'));
            //wp_save_redirect(admin_url('?page=cdash-mu&tab=cdashmu_license_page'));
            //wp_redirect("admin.php?page=member-updater-license");
        }
    }
}

// --------------------------------------------------------------------------------------
// ADDING THE ROLE 'BUSINESS EDITOR' WHEN THE PLUGIN IN ACTIVATED
// --------------------------------------------------------------------------------------

function cdashmu_add_new_user_role() {
	add_role(
        'cdashmu_business_editor',
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

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'cdashmu_add_defaults')
// ------------------------------------------------------------------------------

// Define default option settings
function cdashmu_add_defaults() {
	$tmp = get_option('cdashmu_options');
	
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
	
	update_option( 'cdashmu_options', $tmp );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'cdashmu_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

function cdashmu_init(){
	//register_setting( 'cdashmu_plugin_options', 'cdashmu_options');
    register_setting( 'cdashmu_settings_page', 'cdashmu_options', 'cdashmu_validate_options');
    register_setting( 'cdashmu_licence_page', 'cdashmu_options' );
}

// ------------------------------------------------------------------------------
// ADDING SECTIONS AND FIELDS TO THE SETTINGS PAGE
// ------------------------------------------------------------------------------

add_action( 'admin_init', 'cdashmu_options_init' );

function cdashmu_options_init(  ) { 

	add_settings_section(
		//'cdashmu_options_section', 
        'cdashmu_settings_page_section',
		__( 'Member Updater Settings', 'cdashmu' ), 
		'cdashmu_settings_page_section_callback', 
		'cdashmu_settings_page'
	);
	
	add_settings_field( 
		'user_registration_page', 
		__( 'User Registration Page', 'cdashmu' ), 
		'cdashmu_user_registration_page_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Enter the url for your user registration page. Members will be directed to this page after they join.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'custom_registration_message', 
		__( 'Custom Registration Message', 'cdashmu' ), 
		'cdashmu_custom_registration_message_page_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Enter the message you would like your users to see after they sign up as a user connected to a business.', 'cdashmu' )
		)
	);
	
	add_settings_field( 
		'user_login_page', 
		__( 'User Login Page', 'cdashmu' ), 
		'cdashmu_user_login_page_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Enter the url for your user login page. Members will be directed to this page after they register as a user.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'business_update_page', 
		__( 'Business Update Page', 'cdashmu' ), 
		'cdashmu_business_update_page_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Enter the url for your business update page. Members will be able to edit/update their business here after they login.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_logo_image_width', 
		__( 'Business Logo Image Width', 'cdashmu' ), 
		'cdashmu_business_logo_image_width_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Here you can specify the maximum width of the logo image that the businesses can upload. The default is 200px.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_logo_image_height', 
		__( 'Business Logo Image Height', 'cdashmu' ), 
		'cdashmu_business_logo_image_height_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Here you can specify the maximum height of the logo image that the businesses can upload. The default is 200px.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_featured_image_width', 
		__( 'Business Featured Image Width', 'cdashmu' ), 
		'cdashmu_business_featured_image_width_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Here you can specify the maximum width of the featured image that the businesses can upload. The default is 400px.', 'cdashmu' )
		)
	);
    
    add_settings_field( 
		'bus_featured_image_height', 
		__( 'Business Featured Image height', 'cdashmu' ), 
		'cdashmu_business_featured_image_height_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'Here you can specify the maximum height of the featured image that the businesses can upload. The default is \400px.', 'cdashmu' )
		)
	);


    
    add_settings_field( 
		'additional_admin_email', 
		__( 'Additional Admin Email', 'cdashmu' ), 
		'cdashmu_additional_admin_email_page_render', 
		'cdashmu_settings_page', 
		'cdashmu_settings_page_section',
		array(
			__( 'When a new Business Editor registers, an email will be sent to the Business Owner AND the Chamber/Site Owner.  Enter the Chamber/Site Owner\'s email address here. (If blank, this will default to site\'s admin email.)', 'cdashmu' )
		)
	);
    
    // license tab
	add_settings_section(
		'cdashmu_license_page_section', 
		__( 'License', 'cdashmu' ), 
		'cdashmu_license_section_callback', 
		'cdashmu_licence_page'
	);
}

//All the Callback functions that render the fields

function cdashmu_user_registration_page_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[user_registration_page]' value='<?php echo $options['user_registration_page']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_custom_registration_message_page_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<!--<input type='textarea' name='cdashmu_options[custom_registration_message]' value='<?php echo $options['custom_registration_message']; ?>'>
	<br />--><span class="description"><?php echo $args[0]; ?></span>
	<?php

		$args = array("wpautop" => false, "media_buttons" => true, "textarea_name" => "cdashmu_options[custom_registration_message]", "textarea_rows" => "5");

		wp_editor( $options['custom_registration_message'], "registration", $args );

	?>
	<?php

}

function cdashmu_user_login_page_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[user_login_page]' value='<?php echo $options['user_login_page']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_update_page_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[business_update_page]' value='<?php echo $options['business_update_page']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_logo_image_width_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[bus_logo_image_width]' value='<?php echo $options['bus_logo_image_width']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_logo_image_height_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[bus_logo_image_height]' value='<?php echo $options['bus_logo_image_height']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_featured_image_width_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[bus_featured_image_width]' value='<?php echo $options['bus_featured_image_width']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmu_business_featured_image_height_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='text' name='cdashmu_options[bus_featured_image_height]' value='<?php echo $options['bus_featured_image_height']; ?>' style="width:50px;">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}


function cdashmu_additional_admin_email_page_render( $args ) { 

	$options = get_option( 'cdashmu_options' );
	?>
	<input type='email' name='cdashmu_options[additional_admin_email]' value='<?php echo $options['additional_admin_email']; ?>'">
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

// Sanitize and validate input. 
function cdashmu_validate_options($input) {
	// $msg = "<pre>" . print_r($input, true) . "</pre>";
	// wp_die($msg);
	if( isset( $input['user_registration_page'] ) ) {
    	$input['user_registration_page'] = wp_filter_nohtml_kses( $input['user_registration_page'] );
    }
    if( isset( $input['custom_registration_message'] ) ) {
    	$input['custom_registration_message'] = wp_filter_nohtml_kses( $input['custom_registration_message'] );
    }
    if( isset( $input['user_login_page'] ) ) {
    	$input['user_login_page'] = wp_filter_nohtml_kses( $input['user_login_page'] );
    }
    if( isset( $input['business_update_page'] ) ) {
    	$input['business_update_page'] = wp_filter_nohtml_kses( $input['business_update_page'] );
    }
    if( isset( $input['bus_logo_image_width'] ) ) {
    	$input['bus_logo_image_width'] = wp_filter_nohtml_kses( $input['bus_logo_image_width'] );
    }
    if( isset( $input['bus_logo_image_height'] ) ) {
    	$input['bus_logo_image_height'] = wp_filter_nohtml_kses( $input['bus_logo_image_height'] );
    }
    if( isset( $input['bus_featured_image_width'] ) ) {
    	$input['bus_featured_image_width'] = wp_filter_nohtml_kses( $input['bus_featured_image_width'] );
    }
    if( isset( $input['bus_featured_image_height'] ) ) {
    	$input['bus_featured_image_height'] = wp_filter_nohtml_kses( $input['bus_featured_image_height'] );
    }
    if( isset( $input['additional_admin_email'] ) ) {
    	$input['additional_admin_email'] = sanitize_email( $input['additional_admin_email'] );
    }

	return $input;
}


// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'cdashmu_add_options_page');
// ------------------------------------------------------------------------------

// Add menu page
function cdashmu_add_options_page() {
	add_submenu_page( '/chamber-dashboard-business-directory/options.php', __('Member Updater Settings', 'cdashmu'), __('Member Updater Settings', 'cdmu'), 'manage_options', 'cdash-mu', 'cdashmu_render_form' );
}

// Render the Plugin options form
function cdashmu_render_form() {
?>
    <div class="wrap">
       <!-- Display Plugin Icon, Header, and Description -->
       <div class="icon32" id="icon-options-general"><br></div>
 	    <h2><?php _e('Chamber Dashboard Member Updater Settings', 'cdashmu'); ?></h2>
		<?php settings_errors(); ?>
      <?php  
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'cdashmu_settings_page';  
        ?> 

        <h2 class="nav-tab-wrapper">  
            <a href="?page=cdash-mu&tab=cdashmu_settings_page" class="nav-tab <?php echo $active_tab == 'cdashmu_settings_page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'cdashmu' ); ?></a>  
            <a href="?page=cdash-mu&tab=cdashmu_license_page" class="nav-tab <?php echo $active_tab == 'cdashmu_license_page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'License', 'cdashmu' ); ?></a>  
        </h2>
        
        <div id="main" style="width: 70%; min-width: 350px; float: left;">
        <!-- Beginning of the Plugin Options Form -->
           <?php
            if( $active_tab == 'cdashmu_settings_page' ) 
            { ?>
	            <form method="post" action="options.php">
	                <?php settings_fields( 'cdashmu_settings_page' );
					do_settings_sections( 'cdashmu_settings_page' ); 
					submit_button(); ?>
				</form>
       <?php
            }else if($active_tab == 'cdashmu_license_page'){
                cdmu_render_license_key_form();
            }
        ?>
        </div><!--end of #main-->
    </div><!--end of wrap-->	
<?php
}
?>

