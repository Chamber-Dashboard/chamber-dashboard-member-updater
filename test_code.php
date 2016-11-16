<?php
// ------------------------------------------------------------------------
// MEMBER REGISTRATION SHORTCODE
// ------------------------------------------------------------------------

function cdashmu_member_registration_form($first_name, $last_name, $user_name, $email, $password){
	global $wpdb;
	// Enqueue stylesheet
	wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );

	//Display Member Registration Form
		
	$member_registration_form .= 
		'<form id="member_registration_form" class="cdash_form" name="member_registration_form"';
	$member_registration_form .= 'action="' . $_SERVER['REQUEST_URI'] . '"'; 
	$member_registration_form .= 'method="post">
			<p class="explain">' . __( '* = Required') . '</p>
			
			<input name="cdashmu_membership_registration_nonce" id="cdashmu_membership_registration_nonce" type="hidden" value="' . wp_create_nonce( 'cdashmu_membership_registration_nonce' ) . '">
			
			<p>
				<label>' . __( 'First Name', 'cdashmu' ) . ' *</label>
				<input name="first_name" type="text" id="first_name" required>
			</p>
			
			<p>
				<label>' . __( 'Last Name', 'cdashmu' ) . ' *</label>
				<input name="last_name" type="text" id="last_name" required>
			</p>
			
			<p>
				<label>' . __( 'User Name', 'cdashmu' ) . ' *</label>
				<input name="user_name" type="text" id="user_name" required>
			</p>
			
			<p>
				<label>' . __( 'Email', 'cdashmu' ) . ' *</label>
				<input name="email" type="email" id="email" required>
			</p>
			
			<p>
				<label>' . __( 'Password', 'cdashmu' ) . ' *</label>
				<input name="password" type="password" id="password" required>
			</p>
			
			<p>
				<label>' . __( 'Business Name', 'cdashmu' ) . ' *</label>
				<input name="name" type="text" id="bus_name" required>
			</p>
			
			<div id="business-picker"></div>
			<input name="business_id" type="hidden" id="business_id" value="">	
			
			<input type="submit" name="cdash_mu_reg_form_submit" value="Register" />		
        	
	</form>';
	
	return $member_registration_form;
}
add_shortcode('register_member', 'cdashmu_member_registration_form');

// ------------------------------------------------------------------------
// MEMBER REGISTRATION VALIDATION
// ------------------------------------------------------------------------


function cdashmu_member_registration_validation( $first_name, $last_name, $user_name, $email, $password)  {
	global $reg_errors;
	$reg_errors = new WP_Error;
	
	if ( empty( $user_name ) || empty( $password ) || empty( $email ) ) {
    	$reg_errors->add('field', 'Required form field is missing');
	}
	
	if ( 5 > strlen( $user_name ) ) {
    	$reg_errors->add( 'username_length', 'Username too short. At least 5 characters required' );
	}
	
	if ( username_exists( $user_name ) ) {
    	$reg_errors->add('user_name', 'Sorry, that username already exists!');
	}
	
	if ( ! validate_username( $user_name ) ) {
 	   $reg_errors->add( 'username_invalid', 'Sorry, the username you entered is not valid' );
	}
	
	if ( 8 > strlen( $password ) ) {
        $reg_errors->add( 'password', 'Password length must be greater than 8' );
    }
	
	if ( !is_email( $email ) ) {
 	   $reg_errors->add( 'email_invalid', 'Email is not valid' );
	}
	
	if ( email_exists( $email ) ) {
 	   $reg_errors->add( 'email', 'Email Already in use' );
	}
	
	if ( is_wp_error( $reg_errors ) ) {
 
		foreach ( $reg_errors->get_error_messages() as $error ) {
		 
			echo '<div>';
			echo '<strong>ERROR</strong>:';
			echo $error . '<br/>';
			echo '</div>';
			 
		}
 
	}
}

// ------------------------------------------------------------------------
// COMPLETING MEMBER REGISTRATION
// ------------------------------------------------------------------------

function cdash_mu_complete_registration() {
    global $reg_errors, $user_name, $password, $email, $first_name, $last_name;
    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
        $userdata = array(
        'user_login'    =>   $user_name,
        'user_email'    =>   $email,
        'user_pass'     =>   $password,
        'first_name'    =>   $first_name,
        'last_name'     =>   $last_name,
        );
        $user = wp_insert_user( $userdata );
        echo 'Registration complete. Goto <a href="' . get_site_url() . '/wp-login.php">login page</a>.';   
    }
}

// ------------------------------------------------------------------------
// CUSTOM REGISTRAION FUNCTION
// ------------------------------------------------------------------------

function cdash_mu_custom_registration_function() {
    if ( isset($_POST['cdash_mu_reg_form_submit'] ) ) {
        registration_validation(
        $_POST['user_name'],
        $_POST['password'],
        $_POST['email'],
        $_POST['first_name'],
        $_POST['last_name']
     );
         
        // sanitize user form input
        global $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
        $user_name   =   sanitize_user( $_POST['user_name'] );
        $password   =   esc_attr( $_POST['password'] );
        $email      =   sanitize_email( $_POST['email'] );
        $first_name =   sanitize_text_field( $_POST['first_name'] );
        $last_name  =   sanitize_text_field( $_POST['last_name'] );
 
        // call @function complete_registration to create the user
        // only when no WP_error is found
        cdash_mu_complete_registration(
			$user_name,
			$password,
			$email,
			$first_name,
			$last_name
        );
    }
 
    cdashmu_member_registration_form(
        $user_name,
        $password,
        $email,
        $first_name,
        $last_name
    );
}


// ------------------------------------------------------------------------
// MEMBER LOGIN SHORTCODE
// ------------------------------------------------------------------------

function cdashmu_member_login_form(){
	
	// Enqueue stylesheet
	wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
	
	//only show the login form if user is not logged in
	if(!is_user_logged_in()) {		
		$output = cdashmu_member_login_form_fields();
	} else{
		//show some user info here
		//$output = user info
	}
	return $output;		
}
add_shortcode('member_login_form', 'cdashmu_member_login_form');


// ------------------------------------------------------------------------
// MEMBER LOGIN FORM FIELDS
// ------------------------------------------------------------------------

function cdashmu_member_login_form_fields(){
	// Enqueue stylesheet
	wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
	
	// show any error messages after form submission
	//cdashmu_show_error_messages();

	//Member Login Form Fields
	
	$member_login_form = '';
		
	$member_login_form .= 
		'<form id="cdashmu_member_login" class="cdash_form" name="cdashmu_member_login_form" action="" method="post">
			<p class="explain">' . __( '* = Required') . '</p>
			
			<input name="cdashmu_member_login_nonce" id="cdashmu_member_login_nonce" type="hidden" value="' . wp_create_nonce( 'cdashmu_member_login_nonce' ) . '">
			
			<p>
				<label>' . __( 'User Name', 'cdashmu' ) . ' *</label>
				<input name="cdashmu_user_name" type="text" id="user_name">
			</p>
			
			<p>
				<label>' . __( 'Password', 'cdashmu' ) . ' *</label>
				<input name="cdashmu_password" type="password" id="password">
			</p>
			
			<input type="submit" name="cdash_mu_login_form_submit" value="Login" />		
        	
	</form>';
	
	return $member_login_form;
}



// ------------------------------------------------------------------------
// PROCESSING LOGIN FORM 
// ------------------------------------------------------------------------


function cdashmu_login_member() {
	$user ="";
	$password = "";
	if(isset($_POST['cdashmu_user_name']) && wp_verify_nonce($_POST['cdashmu_login_nonce'], 'cdashmu-login-nonce')){
		$user = get_user_by('login', $_POST['cdashmu_user_name']);		
	}
	
	if(!$user) {
		// if the user name doesn't exist
		cdashmu_errors()->add('empty_username', __('Invalid username'));
	}
	
	if(!isset($_POST['cdashmu_password']) || $_POST['cdashmu_password'] == '') {
		// if no password was entered
		cdashmu_errors()->add('empty_password', __('Please enter a password'));
	}else{
		$password = $_POST['cdashmu_password'];
	}
		
	// check the user's login with their password
	if(!wp_check_password($password, $user->user_pass, $user->ID)) {
		// if the password is incorrect for the specified user
		cdashmu_errors()->add('empty_password', __('Incorrect password'));
	}
	
	// retrieve all error messages
	$errors = cdashmu_errors()->get_error_messages();
	
	// only log the user in if there are no errors
	if(empty($errors)) {
 
		wp_setcookie($_POST['cdashmu_user_name'], $_POST['cdashmu_passowrd'], true);
		wp_set_current_user($user->ID, $_POST['cdashmu_user_name']);	
		do_action('wp_login', $_POST['cdashmu_user_name']);
 
		wp_redirect(home_url()); exit;
	}
}
add_action('init', 'cdashmu_login_member');



// ------------------------------------------------------------------------
// ADDING A NEW MEMBER 
// ------------------------------------------------------------------------


function cdashmu_add_new_member() {
	if (isset( $_POST["cdashmu_user_name"] ) && wp_verify_nonce($_POST['cdashmu_register_nonce'], 'cdashmu-register-nonce')) {
		
		$cdashmu_first_name 		= $_POST["cdashmu_first_name"];
		$cdashmu_last_name	 		= $_POST["cdashmu_last_name"];
		$cdashmu_user_email			= $_POST["cdashmu_user_email"];
		$cdashmu_user_name			= $_POST["cdashmu_user_name"];	
		$cdashmu_user_password			= $_POST["cdashmu_user_password"];
		$cdashmu_confirm_user_password 	= $_POST["cdashmu_confirm_user_password"];
		$cdashmu_bus_name			= $_POST['cdashmu_bus_name'];
		
		// this is required for username checks
		//require_once(ABSPATH . WPINC . '/registration.php');
		
		if(username_exists($cdashmu_user_name)) {
			// Username already registered
			cdashmu_errors()->add('username_unavailable', __('Username already taken'));
		}
		
		if(!validate_username($cdashmu_user_name)) {
			// invalid username
			cdashmu_errors()->add('username_invalid', __('Invalid username'));
		}
		
		if($cdashmu_user_name == '') {
			// empty username
			cdashmu_errors()->add('username_empty', __('Please enter a username'));
		}
		
		if(!is_email($cdashmu_user_email)) {
			//invalid email
			cdashmu_errors()->add('email_invalid', __('Invalid email'));
		}
		
		if(email_exists($cdashmu_user_email)) {
			//Email address already registered
			cdashmu_errors()->add('email_used', __('Email already registered'));
		}
		
		if($cdashmu_user_password == '') {
			// passwords do not match
			cdashmu_errors()->add('password_empty', __('Please enter a password'));
		}
		
		if($cdashmu_user_password != $cdashmu_confirm_user_password) {
			// passwords do not match
			cdashmu_errors()->add('password_mismatch', __('Passwords do not match'));
		}
		
		$errors = cdashmu_errors()->get_error_messages();
		
		// only create the user in if there are no errors
		if(empty($errors)) {
 
			$new_user_id = wp_insert_user(array(
					'user_login'		=> $cdashmu_user_name,
					'user_pass'	 		=> $cdashmu_user_password,
					'user_email'		=> $cdashmu_user_email,
					'first_name'		=> $cdashmu_first_name,
					'last_name'			=> $cdashmu_last_name,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'subscriber'
				)
			);
			if($new_user_id) {
				// send an email to the admin alerting them of the registration
				wp_new_user_notification($new_user_id);
 
				// log the new user in
				wp_setcookie($cdashmu_user_name, $cdashmu_user_password, true);
				wp_set_current_user($new_user_id, $cdashmu_user_name);	
				do_action('wp_login', $cdashmu_user_name);
 
				// send the newly created user to the home page after logging them in
				wp_redirect(home_url()); exit;
			}
 
		}
	}
}
add_action('init', 'cdashmu_add_new_member');

// ------------------------------------------------------------------------
// REGISTRATION FORM FIELDS
// ------------------------------------------------------------------------

function cdashmu_member_registration_form_fields() {
 
	ob_start(); ?>	
		<?php 
		// show any error messages after form submission
		//cdashmu_show_error_messages(); ?>
 
		<form id="cdashmu_registration_form" class="cdashmu_form" action="" method="POST">
			
            <p>
				<label><?php _e('First Name', 'cdashmu'); ?>*</label>
				<input name="first_name" type="text" id="first_name" required>
			</p>
            
            <p>
				<label><?php _e('last Name', 'cdashmu'); ?>*</label>
				<input name="last_name" type="text" id="last_name" required>
			</p>
            
            <p>
				<label><?php _e('User Name', 'cdashmu'); ?>*</label>
				<input name="user_name" type="text" id="user_name" required>
			</p>
            
            <p>
				<label><?php _e('Password', 'cdashmu'); ?>*</label>
				<input name="password" type="text" id="password" required>
			</p>
            
            <p>
				<label><?php _e('Confirm Password', 'cdashmu'); ?>*</label>
				<input name="confirm_password" type="text" id="confirm_password" required>
			</p>
            
            <p>
				<label><?php _e('Business Name', 'cdashmu'); ?>*</label>
				<input name="bus_name" type="text" id="bus_name" required>
			</p>          
            
            <p>
				<input type="hidden" name="cdashmu_register_nonce" value="<?php echo wp_create_nonce('cdashmu-register-nonce'); ?>"/>
				<input type="submit" value="<?php _e('Register', 'cdashmu'); ?>"/>
			</p>

		</form>
	<?php
	return ob_get_clean();
}

?>