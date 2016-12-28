<?php
/*
Plugin Name: Chamber Dashboard Member Updater
Plugin URI: http://chamberdashboard.com
Description: Enables members to update their businesses
Version: 1.0
Author: Chandrika Guntur
Author URI: http://www.gcsdesign.com
Text Domain: cdash
*/

/*  Copyright 2016 Morgan Kay, Chandrika Guntur and Chamber Dashboard (email : info@chamberdashboard.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

/* some plugin defines */
define('CDASH_MU_PLUGIN_URL',       		plugins_url().'/chamber-dashboard-member-updater/');
define('CDASH_MU_INCLUDES_DIR',	    		dirname( __FILE__ ) . '/includes/' );
define('CDASHMU_VERSION',   				'1.0');


// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
// ------------------------------------------------------------------------

function cdashmu_requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "4.2", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 4.2 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'cdashmu_requires_wordpress_version' );

// ------------------------------------------------------------------------
// REQUIRE CHAMBER DASHBOARD MEMBER MANAGER
// ------------------------------------------------------------------------

add_action( 'admin_init', 'cdashmu_require_member_manager' );
function cdashmu_require_member_manager() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !function_exists( 'cdash_requires_wordpress_version' ) ) {
        add_action( 'admin_notices', 'cdashmu_member_manager_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function cdashmu_member_manager_notice(){
    ?><div class="error"><p><?php _e('Sorry, but the Chamber Dashboard Member Updater requires the <a href="https://wordpress.org/plugins/chamber-dashboard-member-manager/" target="_blank">Chamber Dashboard Member Manager</a> to be installed and active.', 'cdashmu' ); ?></p></div><?php
}

add_action('show_admin_bar', '__return_false');


// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
//What to do when the plugin is activated
register_activation_hook(__FILE__, 'cdashmu_add_defaults');
register_activation_hook(__FILE__, 'cdashmu_add_new_user_role');

add_action('admin_menu', 'cdmu_add_settings_page');

//What to do when the plugin is uninstalled
register_uninstall_hook(__FILE__, 'cdashmu_delete_plugin_options');

// Require options stuff
require_once( plugin_dir_path( __FILE__ ) . 'options.php' );

// Require Settings Page
require_once( plugin_dir_path( __FILE__ ) . 'settings.php' );

// Require views
require_once( plugin_dir_path( __FILE__ ) . 'views.php' );

// Require business update form
require_once( plugin_dir_path( __FILE__ ) . 'cdashmu-edit-business.php' );

// Initialize language so it can be translated
function cdashmu_language_init() {
  load_plugin_textdomain( 'cdashmu', false, 'chamber-dashboard-member-manager/languages' );
}
add_action('init', 'cdashmu_language_init');


// ------------------------------------------------------------------------
// ADD THE EDD LICENSE INFORMATION
// ------------------------------------------------------------------------

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'EDD_SAMPLE_STORE_URL', 'http://easydigitaldownloads.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
define( 'EDD_SAMPLE_ITEM_NAME', 'Sample Plugin' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of the settings page for the license input to be displayed
define( 'EDD_SAMPLE_PLUGIN_LICENSE_PAGE', 'pluginname-license' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function edd_sl_sample_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'edd_sample_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( EDD_SAMPLE_STORE_URL, __FILE__, array(
			'version'   => '1.0',                // current version number
			'license'   => $license_key,         // license key (used get_option above to retrieve from DB)
			'item_name' => EDD_SAMPLE_ITEM_NAME, // name of this plugin
			'author'    => 'Pippin Williamson'   // author of this plugin
		)
	);

}
add_action( 'admin_init', 'edd_sl_sample_plugin_updater', 0 );



// ------------------------------------------------------------------------
// MEMBER REGISTRATION FORM
// ------------------------------------------------------------------------

function cdashmu_user_registration_form( $first_name, $last_name, $username, $password, $email, $bus_name, $reg_errors ) {
    // Enqueue stylesheet
	wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
	wp_enqueue_script( 'user-registration-form', plugin_dir_url(__FILE__) . 'js/member_updater.js', array( 'jquery' ) );
	wp_localize_script( 'user-registration-form', 'userregistrationformajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	if ( is_wp_error( $reg_errors ) ) {
 
		foreach ( $reg_errors->get_error_messages() as $error ) {
		 
			echo '<div>';
			echo '<strong>ERROR</strong>:';
			echo $error . '<br/>';
			echo '</div>';
			 
		} 
	}
	
    echo '
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post" id="cdashmu_user_registration_form" class="cdash_form">
	
	<p class="explain">' . __( '* = Required') . '</p>
	<input name="cdashmu_user_registration_nonce" id="cdashmu_user_registration_nonce" type="hidden" value="' . wp_create_nonce( 'cdashmu_user_registration_nonce' ) . '">
    
	<p>
		<label>' . __( 'First Name', 'cdashmu' ) . ' *</label>
		<input type="text" name="fname" id="first_name" value="' . ( isset( $first_name) ? $first_name : null ) . '">
    </p>
	
	<p>
		<label>' . __( 'Last Name', 'cdashmu' ) . ' *</label>
		<input type="text" name="lname" id="last_name" value="' . ( isset( $last_name) ? $last_name : null ) . '">
    </p>
	
	<p>
		<label>' . __( 'User Name', 'cdashmu' ) . ' *</label>
		<input type="text" name="username" id="user_name" value="' . ( isset( $username ) ? $username : null ) . '">
    </p>
     
    <p>
		<label>' . __( 'Password', 'cdashmu' ) . ' *</label>
		<input type="password" name="password" id="password" value="' . ( isset( $password ) ? $password : null ) . '">
    </p>
     
    <p>
		<label>' . __( 'Email', 'cdashmu' ) . ' *</label>
		<input type="text" name="email" id="email" value="' . ( isset( $email) ? $email : null ) . '">
    </p>
	
	<p>
		<label>' . __( 'Business Name', 'cdashmu' ) . ' *</label>
		<input name="bus_name" type="text" id="bus_name" required value="' . ( isset( $bus_name) ? $bus_name : null ) . '">
	</p>
    
    <div id="business-picker"></div>
	    <input name="business_id" type="hidden" id="business_id" value="">
		
	<input type="submit" name="submit" value="Register"/>
    </form>
    ';
}

function cdashmu_user_registration_validation( $first_name, $last_name, $username, $password, $email, $bus_name, $business_id )  {
	$reg_errors = new WP_Error;
	
	//Check if Username, Password and Email are not empty	
	if ( empty( $username ) || empty( $password ) || empty( $email ) || empty( $bus_name) ) {
    	$reg_errors->add('field', 'Required form field is missing');
	}
	
	//Check if username is 4 characters or more in length
	if ( 4 > strlen( $username ) ) {
    	$reg_errors->add( 'username_length', 'Username too short. At least 4 characters is required' );
	}
	
	//Check if username is already registered
	if ( username_exists( $username ) ){
    	$reg_errors->add('user_name', 'Sorry, that username already exists!');
	}
	
	//Check if username is valid
	if ( ! validate_username( $username ) ) {
 	   $reg_errors->add( 'username_invalid', 'Sorry, the username you entered is not valid' );
	}
	
	//Check if password is greater than 5 characters
	if ( 5 > strlen( $password ) ) {
        $reg_errors->add( 'password', 'Password length must be greater than 5' );
    }
	
	//Check if email is valid
	if ( !is_email( $email ) ) {
    	$reg_errors->add( 'email_invalid', 'Email is not valid' );
	}
	
	//Check if email is already registered
	if ( email_exists( $email ) ) {
    	$reg_errors->add( 'email', 'Email Already in use' );
	}
	
	return $reg_errors;
}

function cdashmu_complete_user_registration($first_name, $last_name, $username, $password, $email, $bus_name, $business_id) {
	$options = get_option( 'cdashmm_options' );
        $userdata = array(
		'first_name'    =>   $first_name,
        'last_name'     =>   $last_name,
        'user_login'    =>   $username,
        'user_pass'     =>   $password,
        'user_email'    =>   $email        
        );		
        $user = wp_insert_user( $userdata );
		$name = $first_name . ' ' . $last_name;
		$person_details = array(
			'post_type' => 'person',
 		    'post_title' => $name,
		    'post_content' => '',
		    'post_status' => 'pending'
		);
 
		$person = wp_insert_post( $person_details );
        p2p_type('businesses_to_people')->connect($business_id, $person, array('date' => current_time('mysql')));
        cdashmu_connect_user_to_people($user, $person);
		
	        //echo 'Registration complete. Goto <a href="' . $options['user_login_page'] . '">Login page</a>.';   
            echo $options['custom_registration_message'];
			// send an email to the admin alerting them of the registration
            //$user_id =  $user->ID;
			cdashmu_wp_new_user_notification($user, $business_id, $bus_name, $name);
}

function cdashmu_member_custom_registration_function() {
    if ( isset($_POST['submit'] ) ) {
        // sanitize user form input
        $first_name =   sanitize_text_field( $_POST['fname'] );
        $last_name  =   sanitize_text_field( $_POST['lname'] );
        $username   =   sanitize_user( $_POST['username'] );
        $password   =   esc_attr( $_POST['password'] );
        $email      =   sanitize_email( $_POST['email'] );
		$bus_name	=	$_POST['bus_name'];	
        $business_id =  $_POST['business_id'];

        $reg_errors = cdashmu_user_registration_validation($first_name, $last_name, $username, $password, $email, $bus_name, $business_id);

        // call @function complete_registration to create the user
        // only when no WP_error is found
		if(count($reg_errors -> get_error_messages()) < 1) {
            cdashmu_complete_user_registration($first_name, $last_name, $username, $password, $email, $bus_name, $business_id);
		}
		else {
   		    cdashmu_user_registration_form($first_name, $last_name, $username, $password, $email, $bus_name, $reg_errors, $business_id);
		}
    }
    else { 
        cdashmu_user_registration_form(null, null, null, null, null, null, null, null);
	}
}

// Register a new shortcode: [cdashmu_registration_form]
add_shortcode( 'cdashmu_registration_form', 'custom_registration_shortcode' );
 

function custom_registration_shortcode() {
    ob_start();
    cdashmu_member_custom_registration_function();
    return ob_get_clean();
}

// ------------------------------------------------------------------------
// MEMBER LOGIN FORM SHORTCODE
// ------------------------------------------------------------------------

function cdashmu_member_login_form_shortcode() {
	if ( is_user_logged_in() )
		return '';
		
	/* Set up some defaults. */
	$defaults = array(
		'label_username' => 'Username',
		'label_password' => 'Password'
	);
	
	/* Merge the user input arguments with the defaults. */
	$attr = shortcode_atts( $defaults, $attr );
	
	/* Set 'echo' to 'false' because we want it to always return instead of print for shortcodes. */
	$attr['echo'] = false;
	$attr['redirect'] = site_url();

	return wp_login_form( $attr );
}

function cdashmu_add_login_shortcode() {
	add_shortcode( 'cdashmu_member_login_form', 'cdashmu_member_login_form_shortcode' );
}

add_action( 'init', 'cdashmu_add_login_shortcode' );


// ------------------------------------------------------------------------
// ADDING LOST PASSWORD TO THE LOGIN FORM
// ------------------------------------------------------------------------

add_action( 'login_form_middle', 'cdashmu_add_lost_password_link' );
function cdashmu_add_lost_password_link() {
	return '<a href="/wp-login.php?action=lostpassword">Lost Password?</a>';
}


add_action('wp_logout','cdashmu_auto_redirect_after_logout');
function cdashmu_auto_redirect_after_logout(){
    wp_redirect( home_url() );
    //cdash_custom_logout_message();
    exit();
}

function cdash_custom_logout_message(){
    echo "You have been logged out as you do no have permissions. Please contact the administrator";
}
// ------------------------------------------------------------------------
// Connect Users to People
// https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
// ------------------------------------------------------------------------

if( defined( 'CDASH_PATH' ) ) {
    // Create the connection between users and people
    function cdashmu_user_and_people() {
        p2p_register_connection_type( array(
            'name' => 'people_to_user',
            'from' => 'person',
            'to' => 'user',
            'reciprocal' => true,
            'admin_column' => 'any',
            'admin_box' => array(
			    'context' => 'side'
			  	),
	        'title' => array(
			    'from' => __( 'Connected Users', 'cdcrm' ),
			    'to' => __( 'Connected People', 'cdcrm' )
			  	)
        ) );
    }
    add_action( 'p2p_init', 'cdashmu_user_and_people' );
}

function cdashmu_connect_user_to_people($user, $people) {
    p2p_type('people_to_user')->connect($people, $user, array('date' => current_time('mysql')));	
}

// AJAX - when a business name is entered, check whether the business is already in the database
function cdashmu_find_existing_business() {
	
    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmu_user_registration_nonce")) {
        exit( "There was an error." );
    }
 
    $bus_name = $_POST['bus_name'];
    $results = '';

    $args = array( 
        'post_type' => 'business',
        'post_title_like' => $bus_name,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    $bus_query = new WP_Query( $args );
    
    // The Loop
    if ( $bus_query->have_posts() ) :
    	$results .= '<div class="alert"><p>' . __( 'It looks like your business is already in our database!  To verify, select your business below:', 'cdashmu' ) . '</p>';
	    while ( $bus_query->have_posts() ) : $bus_query->the_post();
	    	$results .= '<input type="radio" name="business_id" class="business_id" value="' . get_the_id() . '">&nbsp;' . get_the_title() . '<br />';
	    endwhile;
	    $results .= '<input type="radio" name="business_id" class="business_id" value="new">&nbsp;None of the above<br /></div>';
    endif;
    
    // Reset Post Data
    wp_reset_postdata();
    
    die($results);
}
add_action( 'wp_ajax_cdashmu_find_existing_business', 'cdashmu_find_existing_business' );
add_action( 'wp_ajax_nopriv_cdashmu_find_existing_business', 'cdashmu_find_existing_business' );


// AJAX - when an existing business is selected, fill in the form
function cdashmu_prefill_user_registration_form() {
	
    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmu_user_registration_nonce")) {
        exit( "There was an error." );
    }
 
    $business_id = $_POST['business_id'];
    if( "new" == $_POST['business_id'] ) {
    	die();
    }
    $results = array();

    $args = array( 
        'post_type' => 'business',
        'p' => $business_id,
    );
    
    $bus_query = new WP_Query( $args );
    
    // The Loop
    if ( $bus_query->have_posts() ) :
	    while ( $bus_query->have_posts() ) : $bus_query->the_post();
			$results['business_id'] = get_the_id();
			$results['business_name'] = get_the_title();
	    endwhile;
    endif;
    
    // Reset Post Data
    wp_reset_postdata();

    // $results = json_encode($results);
   	wp_send_json($results);
    
    die();
}
add_action( 'wp_ajax_nopriv_cdashmu_prefill_user_registration_form', 'cdashmu_prefill_user_registration_form' );
add_action( 'wp_ajax_cdashmu_prefill_user_registration_form', 'cdashmu_prefill_user_registration_form' );

?>