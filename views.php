<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// ------------------------------------------------------------------------
// WORDPRESS GET CURRENT USER ID
// ------------------------------------------------------------------------

function cdashmu_get_current_user_id(){
    //if(is_user_logged_in()){
    if ( function_exists('cdashmm_is_user_logged_in') && cdashmm_is_user_logged_in() ){
        $user = wp_get_current_user();
        $user_id = $user->ID;
        return $user_id;
    }
    return null;
}


// ------------------------------------------------------------------------
// WORDPRESS FUNCTION TO REDIRECT USERS ON LOGIN BASED ON USER ROLE
// ------------------------------------------------------------------------
//add_filter('login_redirect', 'cdashmu_user_login_redirect', 10, 3 );
function cdashmu_user_login_redirect( $url, $request, $user ){
    if( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
        /*if( $user->has_cap( 'administrator' ) ) {
            $url = admin_url();
        }
        else*/
        if( $user->has_cap( 'cdashmu_business_editor' ) ) {
            $user_id = $user->ID;
            $business_url = cdashmu_get_business_url($user_id, true);
            if($business_url != null){
                    //$url = home_url();
                return $business_url;
            }
        }
    }
    return $url;
}

// ------------------------------------------------------------------------
// GET THE BUSINESS URL FROM THE USER INFO
// ------------------------------------------------------------------------


function cdashmu_get_business_url($user_id, $include_pending){

    $person_id = cdashmu_get_person_id_from_user_id($user_id, $include_pending);

    $business_id = cdashmu_get_business_id_from_person_id($person_id, $include_pending);

    return cdashmu_get_business_url_from_business_id($business_id);
}


// ------------------------------------------------------------------------
// GET THE BUSINESS EMAIL FROM THE USER INFO
// ------------------------------------------------------------------------


function cdashmu_get_business_email($user_id, $include_pending){

    $person_id = cdashmu_get_person_id_from_user_id($user_id, $include_pending);

    $business_id = cdashmu_get_business_id_from_person_id($person_id, $include_pending);

    return cdashmu_get_business_email_from_business_id($business_id);
}

//get people connected to the user, get the first person from the connection
//if there is no such person, logout
function cdashmu_get_person_id_from_user_id($user_id, $include_pending){
    if($user_id == null){
        return null;
    }

    //Find connected people

    $connection_params = array(
        'connected_type' => 'people_to_user',
        'connected_items' => $user_id,
        'nopaging' => true
    );

    if($include_pending){
        $connection_params['post_status'] = 'any';
    }

    $connected = new WP_Query($connection_params);

    //Get the person ID
    if($connected->have_posts()):

    while($connected->have_posts() ): $connected->the_post();
        //get the person connected to the user
        $person_id = get_the_ID();
        break;
    endwhile;

    //Prevent wierdness
    wp_reset_postdata();

    else:
    $person_id = null;
    endif;

    return $person_id;
}


//get businesses connected to the person and get the first business id
function cdashmu_get_business_id_from_person_id($person_id, $include_pending) {
    if($person_id == null){
        return null;
    }
    // Find connected businesses

    $connection_params = array(
	  'connected_type' => 'businesses_to_people',
	  'connected_items' => $person_id,
	  'nopaging' => true
	);

    if($include_pending){
        $connection_params['connected_query'] = array('post_status' => 'any');
    }
    $connected = new WP_Query( $connection_params);

    // Get the business ID
    if ( $connected->have_posts() ) :

    while ( $connected->have_posts() ) : $connected->the_post();
            //get the business connected to the person
            $business_id = get_the_ID();
            break;
    endwhile;

    // Prevent weirdness
    wp_reset_postdata();

    else:
        $business_id = null;
    endif;

    return $business_id;
}

//given a business id, get the business slug, build the business url and return
function cdashmu_get_business_url_from_business_id($business_id) {
    if($business_id == null){
        return null;
    }
    $business = get_post($business_id);
    $business_slug = $business->post_name;
    return home_url() . '/business/' . $business_slug;
}

//given a business id, get the business email and return
function cdashmu_get_business_email_from_business_id($business_id) {
    if($business_id == null){
        return null;
    }

    global $buscontact_metabox;
    $contactmeta = $buscontact_metabox->the_meta($business_id);

    global $billing_metabox;
    $billingmeta = $billing_metabox->the_meta($business_id);

    if(isset($contactmeta['location'])){
       if( is_array( $contactmeta['location'] ) && !empty( $contactmeta['location'] ) ) {
           $location = $contactmeta['location'][0];
           if(isset($location) && is_array($location)){
               if( isset( $location['email'][0] ) && !empty( $location['email'][0] ) ) {
                   $business_email = $contactmeta['location'][0]['email'][0]['emailaddress'];
               }else{
                   $business_email = '';
               }
           }
       }
    }else{
        if(isset($billingmeta['billing_email'])){
            $business_email = $billingmeta['billing_email'];
        }else{
            $business_email = '';
        }
    }
    return $business_email;
}


// ------------------------------------------------------------------------
// CUSTOM REGISTRATION EMAILS SENT TO ADMIN AND TO THE CONNECTED BUSINESS
// ------------------------------------------------------------------------
/**
 * Snippet Name: Customize registration emails sent to the connected business
 * Snippet URL: http://www.wpcustoms.net/snippets/customize-registration-emails-sent-to-new-users/
 */
 function cdashmu_wp_new_user_notification($user_id, $business_id, $bus_name, $name) {
    $bus_name = wp_specialchars_decode($bus_name, ENT_QUOTES);
    $member_options = get_option('cdashmu_options');
	$user = get_userdata( $user_id );
    //$bus_email1 = cdashmu_get_business_email_from_business_id($business_id);
    $bus_email = cdashmu_get_business_email_from_business_id($business_id);
    $user_email = $user->user_email;
    $user_login = $user->user_login;
    //$headers = array('Content-Type: text/html; charset=UTF-8');
    $headers = "MIME-Version: 1.0\r\n";
    //$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";

    if($member_options['additional_admin_email'] == ""){
        $admin_email = get_option('admin_email');
    }else{
        $admin_email = $member_options['additional_admin_email'];
    }
   // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    cdashmu_send_admin_email($blogname, $name, $user_email, $bus_name, $bus_email, $admin_email, $headers);

   cdashmu_send_bus_email($bus_name, $name, $user_email, $headers, $bus_email);

   cdashmu_send_user_email($bus_name, $user_login, $user_email, $blogname, $headers);
	 //if ( empty($plaintext_pass) )
		//return;
}

function cdashmu_send_admin_email($blogname, $name, $user_email, $bus_name, $bus_email, $admin_email, $headers){
  $member_options = get_option('cdashmu_options');
  $message = sprintf(__('You have a new user registered on your site %s:'), $blogname) . "<br />";
  $message .= sprintf(__('Name: %s'), $name) . "<br />";
  $message .= sprintf(__('Registered E-mail: %s'), $user_email) . "<br />";
  $message .= sprintf(__('Business Connected to: %s'), $bus_name) . "<br />";
  $message .= sprintf(__('Business Email: %s'), $bus_email) . "<br />";
  //$message .= sprintf(__('Message: %s'), $member_options['custom_admin_message']) . "<br />";
  $message .= nl2br($member_options['custom_admin_message']);

  @wp_mail($admin_email, sprintf(__('[%s] New User Registration'), $blogname), $message, $headers);
}

function cdashmu_send_bus_email($bus_name, $name, $user_email, $headers, $bus_email){
  $member_options = get_option('cdashmu_options');
 //This email goes to the first business email listed under the business listing.
 $message  = sprintf(__('New user connected to your business %s:'), $bus_name) . "<br />";
 $message .= sprintf(__('Name: %s'), $name) . "<br />";
 $message .= sprintf(__('Registered E-mail: %s'), $user_email) . "<br />";
 $message .= nl2br($member_options['custom_business_message']);
 @wp_mail($bus_email, sprintf(__('[%s] New User added to your business listing'), $bus_name), $message, $headers);
}

function cdashmu_send_user_email($bus_name, $user_login, $user_email, $blogname, $headers){
  $member_options = get_option('cdashmu_options');
  //This email goes to the registered user
  $message  = sprintf(__('You have been successfully registered as a user for  %s:'), $bus_name) . "<br />";
  $message .= sprintf(__('Here is your username: %s'), $user_login) . "<br />";
  $message .= sprintf(__('Registered E-mail: %s'), $user_email) . "<br />";
  $message .= nl2br($member_options['custom_user_message']);
  @wp_mail($user_email, sprintf(__('[%s] Your Registration was Successful.'), $blogname), $message, $headers);
}


// ------------------------------------------------------------------------
// GENERATING THE BUSINESS EDIT LINK
// ------------------------------------------------------------------------
//This function works with the cdashmm_member_account_hook to display the member info
function cdashmu_get_business_edit_link($user_id){
    $business_edit_link = "";
    $member_options = get_option('cdashmu_options');
    $user = get_userdata( $user_id );
    $business_edit_url = $member_options['business_update_page'];
    $logout_url = wp_logout_url();
    $business_edit_link = '<p class="cdashmu_bus_edit_link"><a href="' . $business_edit_url . '">'. __('Edit your business listing', 'cdash-mu').'</a><p>';
    return $business_edit_link;
}

// ------------------------------------------------------------------------
// DISPLAYING THE BUSINESS EDIT LINK
// ------------------------------------------------------------------------
function cdashmu_display_business_edit_link($business_id){
    $member_options = get_option('cdashmu_options');
    $mm_options = get_option('cdashmm_options');
    if(is_user_logged_in()){
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $logout_url = wp_logout_url();
            //return $user_id;

            $user_can_update_approved = cdashmu_can_user_update_business($user_id, $business_id, false);
            if(!$user_can_update_approved){
                $user_can_update_pending = cdashmu_can_user_update_business($user_id, $business_id, true);
                if(!$user_can_update_pending){
                    return null;
                }
                else{
                    $message .= '<br />' . __('<p>Your connection to the business has not been approved yet. Please contact your site admin.</p>', 'cdash-mu');
                    $message .= '<p><a href="' . $logout_url . '">Click here to logout.</a></p>';
                    return $message;
                }

            }else{
                $link = cdashmu_get_business_edit_link($user_id);
                return $link;
            }

        }else{
          if(isset($mm_options['cdashmm_member_login_form'])){
            $login_page = cdashmm_get_login_page_url();
          }elseif(isset($member_options['user_login_page'])){
            $login_page = $member_options['user_login_page'];
          }
            $login_link = "<p>Please login <a href='" . $login_page . "'>here</a> to update your business.</p>";
            return $login_link;
        }
}

// ------------------------------------------------------------------------
// CHECKING TO SEE IF THE CURRENT USER CAN UPDATE THIS BUSINESS
// ------------------------------------------------------------------------

function cdashmu_can_user_update_business($user_id, $business_id, $include_pending){
    $url = cdashmu_get_business_url($user_id, $include_pending);
    $business_url = cdashmu_get_business_url_from_business_id($business_id);

    if ($url == $business_url){
        return true;
    }
    else{
        return false;
    }
}
?>