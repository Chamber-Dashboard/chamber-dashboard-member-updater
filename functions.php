<?php
if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}
//Setting the transiet variable when plugin is active
function cdashmu_set_plugin_active(){
    set_transient('cdashmu_active', 'true');
}

//Deleting the transient variable when plugin is deactivated
function cdashmu_set_plugin_inactive(){
  delete_transient('cdashmu_active');
}

//Display Member Updater version in the technical details tab
function cdashmu_render_technical_details(){
  echo "<h4>Member Updater Version: " . CDASHMU_VERSION . "</h4>";
}
add_action('cdash_technical_details_hook', 'cdashmu_render_technical_details', 60);

//Taken from https://alka-web.com/blog/how-to-restrict-access-to-wordpress-dashboard-programmatically/
// Could be better adds the function to the 'init' hook and check later if it's an admin page
add_action( 'init', 'cdashmu_admin_access_handler');

function cdashmu_admin_access_handler() {
   // Check if the current page is an admin page
   // && and ensure that this is not an ajax call
   if ( is_admin() && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){

      //Get all capabilities of the current user
      $user = get_userdata( get_current_user_id() );
      $caps = ( is_object( $user) ) ? array_keys($user->allcaps) : array();

      //All capabilities/roles listed here are not able to see the dashboard
      $block_access_to = array('cdashmu_business_editor');

      if(array_intersect($block_access_to, $caps)) {
         wp_redirect( home_url() );
         exit;
      }
   }
}


function cdashmu_is_business_editor($user_id){
	$member_options = get_option('cdashmu_options');
	$user_meta=get_userdata($user_id);
	$user_roles=$user_meta->roles;
	if ( in_array( 'cdashmu_business_editor', $user_roles, true ) ) {
    // Do something.
		return true;
	}else{
		return false;
	}
}
function cdashmu_login_page(){
    $member_options = get_option('cdashmu_options');
    $mm_options = get_option('cdashmm_options');
       $login_page = '';
    if(isset($mm_options['cdashmm_member_login_form']) && $mm_options['cdashmm_member_login_form'] !=''){
      $login_page = cdashmm_get_login_page_url();
    }elseif(isset($member_options['user_login_page'])){
      $login_page = $member_options['user_login_page'];
    }
       return $login_page;
}

//Restrict media library files to user's own uploads
add_filter( 'ajax_query_attachments_args', 'cdashmu_show_current_user_attachments' );
 
function cdashmu_show_current_user_attachments( $query ) {
    $user_id = get_current_user_id();
    if ( $user_id && !current_user_can('activate_plugins') ) {
        $query['author'] = $user_id;
    }
    return $query;
} 

/*add_filter('upload_mimes','cdashmu_restrict_file_upload_types');
function cdashmu_restrict_file_upload_types($mimes){
  $user = wp_get_current_user();
  //cd_debug("User Roles: " . print_r($user->roles),true);
  if ( in_array( 'cdashmu_business_editor', (array) $user->roles ) ) {
    //The user has the "cdashmu_business_editor" role
    $mimes = array(
      'jpg|jpeg|jpe' => 'image/jpeg',
      'gif' => 'image/gif',
      'png' =>  'image/png'
      );
  }
  return $mimes;
}*/
?>