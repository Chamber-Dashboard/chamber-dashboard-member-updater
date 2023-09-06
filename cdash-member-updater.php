<?php
/*
Plugin Name: Chamber Dashboard Member Updater
Plugin URI: https://github.com/Chamber-Dashboard/chamber-dashboard-member-updater
Description: Enables members to update their businesses
Version: 1.4.4
Author: Chandrika Guntur
Author URI: http://www.gcsdesign.com
Text Domain: cdash-mu
*/

/*  Copyright 2017 Chandrika Guntur and Chamber Dashboard (email : info@chamberdashboard.com)

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
define('CDASHMU_VERSION',   				'1.4.4');

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:
// ------------------------------------------------------------------------

function cdashmu_requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "5.0", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 5.0 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'cdashmu_requires_wordpress_version' );

// ------------------------------------------------------------------------
// REQUIRE CHAMBER DASHBOARD MEMBER MANAGER
// ------------------------------------------------------------------------
function cdashmu_check_member_manager(){
	if(function_exists('cdashmm_requires_wordpress_version')){
		return true;
	}else if(function_exists('cdashmm_pro_require_business_directory')){
		return true;
	}else{
		return false;
	}
}
add_action( 'admin_init', 'cdashmu_require_member_manager' );
function cdashmu_require_member_manager() {
    if ( ( is_admin() && current_user_can( 'activate_plugins' ) ) &&  !cdashmu_check_member_manager()  ) {
        add_action( 'admin_notices', 'cdashmu_member_manager_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function cdashmu_member_manager_notice(){
    ?><div class="error"><p><?php _e('Sorry, but the Chamber Dashboard Member Updater requires the <a href="https://wordpress.org/plugins/chamber-dashboard-member-manager/" target="_blank">Chamber Dashboard Member Manager</a> or <a href="https://chamberdashboard.com/downloads/member-manager-pro/" target="_blank">Chamber Dashboard Member Manager Pro</a> to be installed and active.', 'cdashmu' ); ?></p></div><?php
}

// ------------------------------------------------------------------------
// REQUIRE CHAMBER DASHBOARD CRM
// ------------------------------------------------------------------------

add_action( 'admin_init', 'cdashmu_require_crm' );
function cdashmu_require_crm() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !function_exists( 'cdcrm_requires_wordpress_version' ) ) {
        add_action( 'admin_notices', 'cdashmu_crm_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function cdashmu_crm_notice(){
    ?><div class="error"><p><?php _e('Sorry, but the Chamber Dashboard Member Updater requires the <a href="https://wordpress.org/plugins/chamber-dashboard-crm/" target="_blank">Chamber Dashboard CRM</a> to be installed and active.', 'cdashmu' ); ?></p></div><?php
}

function cdashmu_check_bd_version(){
    if ( is_admin() && current_user_can( 'activate_plugins' )){
        if(defined('CDASH_BUS_VER') && CDASH_BUS_VER < '3.1.9'){
            add_action( 'admin_notices', 'cdashmu_update_bd_notice' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }    
        }
    }
  }
  add_action( 'admin_init', 'cdashmu_check_bd_version' );
  add_action( 'upgrader_process_complete', 'cdashmu_check_bd_version');
  function cdashmu_update_bd_notice(){
    ?><div class="error"><p><?php _e('Please update Chamber Dashboard Business Directory to version 3.1.9 or later before updating the Member Updater.', 'cdashmu' ); ?></p></div>
  <?php
  } 
  

$file   = plugin_basename( __FILE__ );
$folder = dirname(__FILE__);
$hook = "in_plugin_update_message-{$folder}/{$file}";

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
//What to do when the plugin is activated
register_activation_hook(__FILE__, 'cdashmu_plugin_activate');
register_activation_hook(__FILE__, 'cdashmu_add_defaults');
register_activation_hook(__FILE__, 'cdashmu_add_new_user_role');
register_activation_hook(__FILE__, 'cdashmu_set_plugin_active');

//add_action('admin_init', 'cdashmu_plugin_redirect');
add_action('admin_menu', 'cdashmu_add_options_page');
add_action( 'admin_init', 'cdashmu_init' );

//What to do when the plugin is uninstalled
register_uninstall_hook(__FILE__, 'cdashmu_delete_plugin_options');
register_deactivation_hook( __FILE__, 'remove_business_editor_role' );


//Required Files
require_once( plugin_dir_path( __FILE__ ) . 'required_files.php' );

// Initialize language so it can be translated
function cdashmu_language_init() {
  load_plugin_textdomain( 'cdashmu', false, 'chamber-dashboard-member-manager/languages' );
}
add_action('init', 'cdashmu_language_init');

//Adding settings link on the plugins page
function cdashmu_plugin_action_links( $links ) {
  //Check transient. If it is available, display the settings link
  if(get_transient('cdashmu_active')){
    $settings_url = get_admin_url() . 'admin.php?page=cd-settings&tab=cdash-mu';
    $settings_link = '<a href="' . $settings_url . '">' . __('Settings', 'cdash-mu') . '</a>';
    array_unshift( $links, $settings_link );
  }
  return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cdashmu_plugin_action_links' );

function cdashmu_block_scripts() {
	$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');	
	wp_register_script(
		'member-updater-blocks',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version']
	);
	wp_enqueue_script('member-updater-blocks');
	wp_localize_script( 'member-updater-blocks', 'wpAjax', array( 'wpurl' => get_bloginfo('wpurl') ) );
}
add_action( 'enqueue_block_editor_assets', 'cdashmu_block_scripts', 30 );

// ------------------------------------------------------------------------
// MEMBER REGISTRATION FORM
// ------------------------------------------------------------------------

function cdashmu_display_error($code, $reg_errors){
    $retval = '';
    if ( is_wp_error( $reg_errors ) ) {
        foreach ( $reg_errors->get_error_messages($code) as $error ) {
            $retval .= "<span class='errors'>" . $error . '</span>';
				}
    }
    return $retval;
}

function cdashmu_user_registration_form( $first_name, $last_name, $username, $password, $email, $bus_name, $business_id, $reg_errors ) {
    // Enqueue stylesheet
	wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
	wp_enqueue_script( 'user-registration-form', plugin_dir_url(__FILE__) . 'js/member_updater.js', array( 'jquery' ) );
	wp_localize_script( 'user-registration-form', 'userregistrationformajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    ?>
    <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" id="cdashmu_user_registration_form" class="cdash_form">
    <?php

	if ( is_wp_error( $reg_errors ) ) {
        echo "<span class='errors'>" . __("There are some errors in the form. Please check below.", "cdash-mu") . "</span>";
	}

    ?>

	<p class="explain"><?= __( '* = Required') ?></p>
	<input name="cdashmu_user_registration_nonce" id="cdashmu_user_registration_nonce" type="hidden" value="<?= wp_create_nonce( 'cdashmu_user_registration_nonce' ) ?>">

	<p>
		<label><?= __( 'First Name', 'cdashmu' ) ?> *</label>
		<input type="text" name="fname" id="first_name" value="<?= $first_name ?>">
        <?= cdashmu_display_error('First Name', $reg_errors) ?>
    </p>

	<p>
		<label><?= __( 'Last Name', 'cdashmu' ) ?> *</label>
		<input type="text" name="lname" id="last_name" value="<?= $last_name ?>">
        <?= cdashmu_display_error('Last Name', $reg_errors) ?>
    </p>

	<p>
		<label><?= __( 'User Name', 'cdashmu' ) ?> *</label>
		<input type="text" name="username" id="user_name" value="<?= $username ?>">
        <?= cdashmu_display_error('User Name', $reg_errors) ?>
    </p>

    <p>
		<label><?= __( 'Password', 'cdashmu' ) ?> *</label>
		<input type="password" name="password" id="password" value="<?= $password ?>">
        <?= cdashmu_display_error('Password', $reg_errors) ?>
    </p>

    <p>
		<label><?= __( 'Email', 'cdashmu' ) ?> *</label>
		<input type="text" name="email" id="email" value="<?= $email ?>">
        <?= cdashmu_display_error('Email', $reg_errors) ?>
    </p>

	<p>
        <label><?= __( 'Business Name', 'cdashmu' ) ?> *</label><span><?= __('Please enter your business name and press tab to select your business from the list.')?> <small><?= __('(Your business needs to be already registered with us.)') ?></small></span>
		<input name="bus_name" type="text" id="bus_name" required value="<?= ( $business_id ? $bus_name : null ) ?>">
        <?= cdashmu_display_error('business_name', $reg_errors) ?>
	</p>

    <div id="business-picker"></div>
	    <input name="business_id" type="hidden" id="business_id" value="<?= $business_id ?>">

	<input type="submit" name="submit" value="Register"/>
    </form>
    <?php
}

function cdashmu_check_empty($field, $field_name, $reg_errors){
    if(empty($field)){
        $reg_errors->add($field_name, $field_name . ' is missing.');
    }
}

function cdashmu_user_registration_validation( $first_name, $last_name, $username, $password, $email, $bus_name, $business_id )  {
	$reg_errors = new WP_Error;

	//Check if Username, Password and Email are not empty
    cdashmu_check_empty($username, 'User Name', $reg_errors);
    cdashmu_check_empty($password, 'Password', $reg_errors);
    cdashmu_check_empty($email, 'Email', $reg_errors);
    cdashmu_check_empty($first_name, 'First Name', $reg_errors);
    cdashmu_check_empty($last_name, 'Last Name', $reg_errors);

	//Check if username is 4 characters or more in length
	if ( 4 > strlen( $username ) ) {
    	$reg_errors->add( 'User Name', 'Username too short. At least 4 characters is required' );
	}

	//Check if username is already registered
	if ( username_exists( $username ) ){
    	$reg_errors->add('User Name', 'Sorry, that username already exists!');
	}

	//Check if username is valid
	if ( ! validate_username( $username ) ) {
 	   $reg_errors->add( 'User Name', 'Sorry, the username you entered is not valid' );
	}

	//Check if password is greater than 5 characters
	if ( 5 > strlen( $password ) ) {
        $reg_errors->add( 'Password', 'Password length must be greater than 5' );
    }

	//Check if email is valid
	if ( !is_email( $email ) ) {
    	$reg_errors->add( 'Email', 'Email is not valid' );
	}

	//Check if email is already registered
	if ( email_exists( $email ) ) {
    	$reg_errors->add( 'Email', 'Email Already in use' );
	}

    if( empty( $business_id)){
        $reg_errors->add('business_name', 'Please select a business.');
    }

	return $reg_errors;
}

function cdashmu_complete_user_registration($first_name, $last_name, $username, $password, $email, $bus_name, $business_id, $show_registration_message) {
	$options = get_option( 'cdashmu_options' );
      $userdata = array(
	  'first_name'    =>   $first_name,
      'last_name'     =>   $last_name,
      'user_login'    =>   $username,
      'user_pass'     =>   $password,
      'user_email'    =>   $email,
      'role'          =>   'cdashmu_business_editor'
      );
    $user = wp_insert_user( $userdata );
		$name = $first_name . ' ' . $last_name;
		$person_details = array(
		'post_type' => 'person',
		'post_title' => $name,
	    'post_content' => 'This was created by the Member Updater.',
	    'post_status' => 'pending'
		);

		$person = wp_insert_post( $person_details );
        p2p_type('businesses_to_people')->connect($business_id, $person, array('date' => current_time('mysql')));
        cdashmu_connect_user_to_people($user, $person);
        //echo 'Registration complete. Goto <a href="' . $options['user_login_page'] . '">Login page</a>.';
		if($show_registration_message){
			echo "<p>" . nl2br($options['custom_registration_message']) . "</p>";
		}

    	// send an email to the admin alerting them of the registration
        //$user_id =  $user->ID;
		cdashmu_wp_new_user_notification($user, $business_id, $bus_name, $name);
		return $user;
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
            cdashmu_complete_user_registration($first_name, $last_name, $username, $password, $email, $bus_name, $business_id, true);
		}
		else {
   		    cdashmu_user_registration_form($first_name, $last_name, $username, $password, $email, $bus_name, $business_id, $reg_errors);
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
	//if ( is_user_logged_in() ){
	if ( function_exists('cdashmm_is_user_logged_in') && cdashmm_is_user_logged_in() ){
        //redirect to business update page
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $member_options = get_option('cdashmu_options');
		$cdashmm_options = get_option('cdashmm_options');
        //$user = get_userdata( $user_id );
        $business_edit_url = $member_options['business_update_page'];
		if(function_exists('cdashmm_display_member_info')){
			return cdashmm_display_member_info();
		}else{
			$business_edit_link = "<a href='". $business_edit_url . "'>Click here to edit your business</a>";
			return __('You are already logged in.', 'cdash-mu') . $business_edit_link;
		}


    }
	/* Set up some defaults. */
	$defaults = array(
		'label_username' => 'Username',
		'label_password' => 'Password'
	);

	/* Merge the user input arguments with the defaults. */
	//$attr = shortcode_atts( $defaults, $attr );

	/* Set 'echo' to 'false' because we want it to always return instead of print for shortcodes. */
	$attr['echo'] = false;
	$attr['redirect'] = site_url();
    $attr = shortcode_atts( $defaults, $attr );
	return wp_login_form( $attr );
}

function cdashmu_add_login_shortcode() {
	add_shortcode( 'cdashmu_member_login_form', 'cdashmu_member_login_form_shortcode' );
}

add_action( 'init', 'cdashmu_add_login_shortcode' );


// ------------------------------------------------------------------------
// ADDING LOST PASSWORD TO THE LOGIN FORM
// ------------------------------------------------------------------------

//add_action( 'login_form_middle', 'cdashmu_add_lost_password_link' );
/*function cdashmu_add_lost_password_link() {
	return '<a href="/wp-login.php?action=lostpassword">Lost Password?</a>';
}


add_action('wp_logout','cdashmu_auto_redirect_after_logout');
function cdashmu_auto_redirect_after_logout(){
    wp_redirect( home_url() );
    //cdashmu_custom_logout_message();
    exit();
}*/

function cdashmu_custom_logout_message(){
    echo __('You have been logged out as you do no have permissions. Please contact the administrator', 'cdashmu');
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
	    	$results .= '<div><input type="radio" name="business_id" class="business_id" value="' . get_the_id() . '"><span>&nbsp;' . get_the_title() . '</span></div>';
	    endwhile;
	    $results .= '</div>';
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