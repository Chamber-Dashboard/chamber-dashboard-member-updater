<?php
/* Options Page for Chamber Dashboard Member Updater */

//Disable the admin bar for non-admin users
cdashmu_remove_admin_bar();


// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'cdashmu_delete_plugin_options')
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function cdashmu_delete_plugin_options() {
	//delete_option('cdashmu_options');
    delete_option( 'cdash_mu_edd_license_key' );
	delete_option( 'cdash_mu_edd_license_status' );
}

// --------------------------------------------------------------------------------------
//  REDIRECTING THE PLUGIN TO THE LICENSE PAGE AFTER ACTIVATION
// --------------------------------------------------------------------------------------


function cdashmu_plugin_activate(){
  //cdash_license_page();
  add_option('cdashmu_do_activation_redirect', true);
  //cdashmu_plugin_redirect();
}

function cdashmu_remove_admin_bar() {
	if(!function_exists('wp_get_current_user')) {
    	include(ABSPATH . "wp-includes/pluggable.php");
	}
	if ( ! current_user_can( 'manage_options' ) ) {
    	add_filter('show_admin_bar', '__return_false');
	}
}

function cdashmu_plugin_redirect(){
    if (get_option('cdashmu_do_activation_redirect', false)) {
        delete_option('cdashmu_do_activation_redirect');
    }else{
      if(!isset($_GET['activate-multi']))
      {
          //wp_safe_redirect(admin_url('admin.php?page=cdash-mu&tab=cdashmu_license_page'));
          wp_safe_redirect(admin_url('admin.php?page=chamber_dashboard_license'));
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
			'edit_post'   => true,
			'edit_posts'   => true,
			'edit_others_pages' => true,
			'edit_published_pages' => true,
            'delete_posts' => false, // Use false to explicitly deny
            'delete_published_posts' => false,
            'upload_files' => true,
            'publish_posts'=> true,
        )
    );
}

function cdashmu_add_role_cap(){
	$role = get_role( 'cdashmu_business_editor' );
  	$role->add_cap('upload_files');
}
//add_action( 'admin_init', 'cdashmu_add_role_cap');

function remove_business_editor_role(){
	remove_role( 'cdashmu_business_editor' );
}

// ----------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'cdashmu_add_defaults')
// ----------------------------------------------------------------------------------

// Define default option settings
function cdashmu_add_defaults() {
	$tmp = get_option('cdashmu_options');

	if( !isset( $tmp['user_registration_page'] ) ) {
		$tmp['user_registration_page'] = '';
	}

    if(!isset($tmp['custom_registration_message'])){
        $tmp['custom_registration_message'] = __('Thank you for registering as a member. You will be able to update your business after you have been approved by the site admin. If you have questions, please contact us.', 'cdash-mu');
    }

    if(!isset($tmp['custom_admin_message'])){
        $tmp['custom_admin_message'] = __('You have a new user registered on your site', 'cdash-mu');
    }

    if(!isset($tmp['custom_business_message'])){
        $tmp['custom_business_message'] = __('A new user has been added to your business listing', 'cdash-mu');
    }

    if(!isset($tmp['custom_user_message'])){
        $tmp['custom_user_message'] = __('You have been successfully registered as a user for ' . bloginfo('name') . '. We will follow up shortly as soon as we have confirmed your status as an editor for this listing.', 'cdash-mu');
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

add_action('admin_enqueue_scripts', function()
{
    wp_enqueue_media();
});

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
		__( 'General Settings', 'cdashmu' ),
		'cdashmu_settings_page_section_callback',
		'cdashmu_settings_page'
	);

    add_settings_section(
		//'cdashmu_options_section',
        'cdashmu_message_page_section',
		__( 'Custom Messages', 'cdashmu' ),
		'cdashmu_message_page_section_callback',
		'cdashmu_settings_page'
	);

    add_settings_section(
		//'cdashmu_options_section',
        'cdashmu_form_page_section',
		__( 'Form Details', 'cdashmu' ),
		'cdashmu_form_page_section_callback',
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
		'cdashmu_message_page_section',
		array(
			__( 'Enter the message you would like your users to see <b>on your website</b> after they sign up as a user connected to a business.', 'cdashmu' )
		)
	);

    add_settings_field(
		'custom_admin_message',
		__( 'Custom Message for admins when a new user is registered.', 'cdashmu' ),
		'cdashmu_custom_admin_message_page_render',
		'cdashmu_settings_page',
		'cdashmu_message_page_section',
		array(
			__( 'Enter the message you would like to add to the admin email message when a new user is registered.', 'cdashmu' )
		)
	);

    add_settings_field(
		'custom_business_message',
		__( 'Custom Message for businesses when a new user is registered.', 'cdashmu' ),
		'cdashmu_custom_business_message_page_render',
		'cdashmu_settings_page',
		'cdashmu_message_page_section',
		array(
			__( 'Enter the message you would like to add to the default business email when a new user is registered.
', 'cdashmu' )
		)
	);

    add_settings_field(
		'custom_user_message',
		__( 'Custom Message for the users when they register on your site.', 'cdashmu' ),
		'cdashmu_custom_user_message_page_render',
		'cdashmu_settings_page',
		'cdashmu_message_page_section',
		array(
			__( 'Enter the message you would like to add to the default new user email when they register on your site.', 'cdashmu' )
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
		'cdashmu_form_page_section',
		array(
			__( 'Here you can specify the maximum width of the logo image that the businesses can upload. The default is 200px.', 'cdashmu' )
		)
	);

    add_settings_field(
		'bus_logo_image_height',
		__( 'Business Logo Image Height', 'cdashmu' ),
		'cdashmu_business_logo_image_height_render',
		'cdashmu_settings_page',
		'cdashmu_form_page_section',
		array(
			__( 'Here you can specify the maximum height of the logo image that the businesses can upload. The default is 200px.', 'cdashmu' )
		)
	);

    add_settings_field(
		'bus_featured_image_width',
		__( 'Business Featured Image Width', 'cdashmu' ),
		'cdashmu_business_featured_image_width_render',
		'cdashmu_settings_page',
		'cdashmu_form_page_section',
		array(
			__( 'Here you can specify the maximum width of the featured image that the businesses can upload. The default is 400px.', 'cdashmu' )
		)
	);

    add_settings_field(
		'bus_featured_image_height',
		__( 'Business Featured Image height', 'cdashmu' ),
		'cdashmu_business_featured_image_height_render',
		'cdashmu_settings_page',
		'cdashmu_form_page_section',
		array(
			__( 'Here you can specify the maximum height of the featured image that the businesses can upload. The default is 400px.', 'cdashmu' )
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

function cdashmu_settings_page_section_callback(){
    echo '<span class="desc"></span>';
}

function cdashmu_message_page_section_callback(){
    echo '<span class="desc"></span>';
}

function cdashmu_form_page_section_callback(){
    echo '<span class="desc"></span>';
}

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

		$args = array("wpautop" => true, "media_buttons" => true, "textarea_name" => "cdashmu_options[custom_registration_message]", "textarea_rows" => "5");

		wp_editor( $options['custom_registration_message'], "registration", $args );

	?>
	<?php

}

function cdashmu_custom_admin_message_page_render( $args ) {

	$options = get_option( 'cdashmu_options' );
	?>
	<!--<input type='textarea' name='cdashmu_options[custom_admin_message]' value='<?php echo $options['custom_admin_message']; ?>'>
	<br />--><span class="description"><?php echo $args[0]; ?></span>
	<?php

		$args = array("wpautop" => true, "media_buttons" => true, "textarea_name" => "cdashmu_options[custom_admin_message]", "textarea_rows" => "5");

		wp_editor( $options['custom_admin_message'], "admin", $args );

	?>
	<?php

}

function cdashmu_custom_business_message_page_render( $args ) {

	$options = get_option( 'cdashmu_options' );
	?>
	<!--<input type='textarea' name='cdashmu_options[custom_business_message]' value='<?php echo $options['custom_business_message']; ?>'>
	<br />--><span class="description"><?php echo $args[0]; ?></span>
	<?php

		$args = array("wpautop" => true, "media_buttons" => true, "textarea_name" => "cdashmu_options[custom_business_message]", "textarea_rows" => "5");

		wp_editor( $options['custom_business_message'], "business", $args );

	?>
	<?php

}

function cdashmu_custom_user_message_page_render( $args ) {

	$options = get_option( 'cdashmu_options' );
	?>
	<!--<input type='textarea' name='cdashmu_options[custom_user_message]' value='<?php echo $options['custom_user_message']; ?>'>
	<br />--><span class="description"><?php echo $args[0]; ?></span>
	<?php

		$args = array("wpautop" => true, "media_buttons" => true, "textarea_name" => "cdashmu_options[custom_user_message]", "textarea_rows" => "5");

		wp_editor( $options['custom_user_message'], "user", $args );

	?>
	<?php

}


function cdashmu_user_login_page_render( $args ) {

	$options = get_option( 'cdashmu_options' );
  $cdashmm_options = get_option('cdashmm_options');
  if(isset($options['user_login_page'])){
      $cdashmu_login_page = $options['user_login_page'];
    }else{
        $cdashmu_login_page = '';
    }

  $cdashmm_login_page = $cdashmm_options['cdashmm_member_login_form'];
  if(isset($cdashmm_login_page) && $cdashmm_login_page != ''){
    $disabled = "disabled";
    $description = __("Login page is already set in the Chamber Dashboard Member Manager settings.", "cdashmu");
  }else{
    $disabled = '';
    $description = __( 'Enter the url for your user login page. Members will be directed to this page after they register as a user.', 'cdashmu' );
  }
	?>
	<input type='text' name='cdashmu_options[user_login_page]' value='<?php echo $cdashmu_login_page; ?>' <?php echo $disabled; ?>>
	<br /><span class="description"><?php echo $description; ?></span>
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
	<input type='email' name='cdashmu_options[additional_admin_email]' value='<?php echo $options['additional_admin_email']; ?>'>
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
    	//$input['custom_registration_message'] = wp_filter_nohtml_kses( $input['custom_registration_message'] );
      $input['custom_registration_message'] = wp_kses_post( $input['custom_registration_message'] );
    }
    if( isset( $input['custom_admin_message'] ) ) {
    	$input['custom_admin_message'] = wp_kses_post( $input['custom_admin_message'] );
    }
    if( isset( $input['custom_business_message'] ) ) {
    	$input['custom_business_message'] = wp_kses_post( $input['custom_business_message'] );
    }
    if( isset( $input['custom_user_message'] ) ) {
    	$input['custom_user_message'] = wp_kses_post( $input['custom_user_message'] );
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
	//add_submenu_page( '/chamber-dashboard-business-directory/options.php', __('Member Updater Settings', 'cdashmu'), __('Member Updater Settings', 'cdashmu'), 'manage_options', 'cdash-mu', 'cdashmu_render_form' );
}

add_action( 'cdash_settings_tab', 'cdash_mu_tab', 50 );
function cdash_mu_tab(){
	global $cdash_active_tab; ?>
    <a class="nav-tab <?php echo $cdash_active_tab == 'cdash-mu' ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=cd-settings&tab=cdash-mu' ); ?>"><?php _e( 'Member Updater', 'cdash' ); ?> </a>
	<?php
}

add_action( 'cdash_settings_content', 'cdash_mu_settings' );
function cdash_mu_settings(){
    global $cdash_active_tab;

	switch($cdash_active_tab){
		case 'cdash-mu':
		cdashmu_render_form();
		break;
	}
}

// Render the Plugin options form
function cdashmu_render_form() {
?>
    <div class="wrap">
        <?php
        $page = $_GET['page'];
        if(isset($_GET['tab'])){
            $tab = $_GET['tab'];
        }
        if(isset($_GET['section'])){
            $section = $_GET['section'];
        }else{
            $section = "cdmu_settings";
        }
        ?>
       <!-- Display Plugin Icon, Header, and Description -->
       <div class="icon32" id="icon-options-general"><br></div>
 	    <h1><?php _e('Member Updater Settings', 'cdashmu'); ?></h1>
		<?php settings_errors(); ?>
      <?php
            //$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'cdashmu_settings_page';
        ?>
        <div id="main" class="cd_settings_tab_group" style="width: 100%; float: left;">
            <div class="cdash section_group">
                <ul>
                    <li class="<?php echo $section == 'cdmu_settings' ? 'section_active' : ''; ?>">
                        <a href="?page=cd-settings&tab=cdash-mu&section=cdmu_settings" class="<?php echo $section == 'cdmu_settings' ? 'section_active' : ''; ?>"><?php esc_html_e( 'Member Updater Settings', 'cdash' ); ?></a><span>|</span>
                    </li>
                    <li class="<?php echo $section == 'cdmu_docs' ? 'section_active' : ''; ?>">
                        <a href="?page=cd-settings&tab=cdash-mu&section=cdmu_docs" class="<?php echo $section == 'cdmu_docs' ? 'section_active' : ''; ?>"><?php esc_html_e( 'Shortcodes', 'cdash-mu' ); ?></a>
                    </li>
                </ul>
            </div>
          <div class="cdash_section_content">
            <!-- Beginning of the Plugin Options Form -->
            <?php
            if( $section == 'cdmu_settings' )
            {
                cdmu_settings();
            }else if($section == 'cdmu_docs'){
                cdashmu_quick_setup_guide();
            }
          ?>
          </div><!--end of cdash_section_content-->
        </div><!--end of #main-->
    </div><!--end of wrap-->
<?php
}

function cdmu_settings(){
    ?>
	<div id="cdmu_settings" class="cdash_plugin_settings">
		<form method="post" action="options.php">
			<?php settings_fields( 'cdashmu_settings_page' );
			?>
			<div class="settings_sections">
			<?php
			do_settings_sections( 'cdashmu_settings_page' );
			?>
			</div>
		<?php
			submit_button(); ?>
		</form>
	</div>
<?php
}

function cdashmu_quick_setup_guide(){
    ?>
    <div id="sidebar">
    	<div class="cdash_top_blocks">
    		<div class="cdash_block">
    			<h3><?php echo __('Member Registration Form', 'cdashmu'); ?></h3>
    			<p><span class="bold">[cdashmu_registration_form]</span> - <?php echo __('Displays the member registation form', 'cdashmu'); ?><br />

    			</p>
    			<p><a target="_blank" href="https://chamberdashboard.com/docs/plugin-features/member-claimed-listings/"><?php echo __('Member Updater Docs', 'cdashmu'); ?></a></p>
    		</div>
            <div class="cdash_block">
    			<h3><?php echo __('Member Update Business Form', 'cdashmu'); ?></h3>
    			<p><span class="bold">[cdashmu_update_business]</span> - <?php echo __('Displays the member/business update form', 'cdashmu'); ?><br />

    			</p>
    			<p><a target="_blank" href="https://chamberdashboard.com/docs/plugin-features/member-claimed-listings/"><?php echo __('Member Updater Docs', 'cdashmu'); ?></a></p>
    		</div>
        </div>
    </div>
    <?php
}
?>
