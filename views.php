<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// ------------------------------------------------------------------------
// WORDPRESS GET CURRENT USER ID
// ------------------------------------------------------------------------

function cdashmu_get_current_user_id(){
    if(is_user_logged_in()){
        $user = wp_get_current_user();   
        $user_id = $user->ID;        
        return $user_id;     
    }
    return null;
}


// ------------------------------------------------------------------------
// WORDPRESS FUNCTION TO REDIRECT USERS ON LOGIN BASED ON USER ROLE
// ------------------------------------------------------------------------
add_filter('login_redirect', 'cdashmu_user_login_redirect', 10, 3 );
function cdashmu_user_login_redirect( $url, $request, $user ){
    if( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
        if( $user->has_cap( 'administrator' ) ) {
            $url = admin_url();    
        }
        else if( $user->has_cap( 'author' ) ) {
            $user_id = $user->ID;
            $url = cdashmu_get_business_url($user_id, false);
            if($url == null){
                    wp_logout();
            }
        }else{
            wp_logout();
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
    return home_url() . '/' . $business_slug;    
}

//given a business id, get the business email and return
function cdashmu_get_business_email_from_business_id($business_id) {
    if($business_id == null){
        return null;        
    }
    
    $business = new WP_Query(array('p' => $business_id));
    //$business = get_post($business_id);
    while($business->have_posts()) : $business->the_post();
        global $buscontact_metabox;
        $contactmeta = $buscontact_metabox->the_meta();
        
        if( isset( $buscontactmeta['location'] ) ) {
            $locations = $buscontactmeta['location'];
            if( is_array( $locations ) && !empty( $locations ) ) {
                foreach( $locations as $location ) {
                    if( isset( $location['email'] ) && !empty( $location['email'] ) ) {
                        foreach( $location['email'] as $email_addr ) {
                            $business_email = $email_addr['emailaddress'];                            
                        }
                    } 
                    break; // we're cheating and just grabbing the first one
                }
            }
        }
        else{
            $business_email = 'sushmasomu@gmail.com';
        }
    endwhile;        
    
    //$business_email = $business->email;    
    //$business_email = 'chandrika+1149@chamberdashboard.com';
    return $business_email;    
}


// ------------------------------------------------------------------------
// CUSTOM REGISTRATION EMAILS SENT TO ADMIN AND TO THE CONNECTED BUSINESS
// ------------------------------------------------------------------------
/**
 * Snippet Name: Customize registration emails sent to the connected business
 * Snippet URL: http://www.wpcustoms.net/snippets/customize-registration-emails-sent-to-new-users/
 */
 function cdashmu_wp_new_user_notification($user_id) {
	$user = get_userdata( $user_id );
    $bus_email = cdashmu_get_business_email($user_id, true);    
    $bus_email = 'chandrika@chamberdashboard.com';

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

	//if ( empty($plaintext_pass) )
		//return;

	$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
	//$message .= sprintf(__('Password: %s'), '') . "\r\n";
	$message .= 'To log into the admin area please us the following address ' . wp_login_url() . "\r\n";

	//wp_mail($user-user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);
    wp_mail($bus_email, sprintf(__('[%s] Your username and password'), $blogname), $message);

}



// ------------------------------------------------------------------------
// GENERATING THE BUSINESS EDIT LINK
// ------------------------------------------------------------------------
function cdashmu_get_business_edit_link(){
    $member_options = get_option('cdashmm_options');
    $business_edit_url = $member_options['business_update_page'];
    //$business_edit_url = plugins_url('cdashmu-edit-business.php', __FILE__);;
    $edit_post = add_query_arg( 'post', get_the_ID(), get_permalink(  + $_POST['_wp_http_referer'] ) );
    $business_edit_link = '<a href="' . $business_edit_url . '">Edit Your Business Listing</a>'; 

    return $business_edit_link;
    
}//cdashmu_business_edit_link


// ------------------------------------------------------------------------
// CHECKING TO SEE IF THE CURRENT USER CAN UPDATE THIS BUSINESS
// ------------------------------------------------------------------------

function cdashmu_can_user_update_business($user_id, $business_id){
    $url = cdashmu_get_business_url($user_id, false);
    $business_url = cdashmu_get_business_url_from_business_id($business_id);   
    
    if ($url == $business_url){
        return true;
    }
    else{
        return false;
    }
}


?>
