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

add_action('cdash_technical_details_hook', 'cdashmu_render_technical_details', 60);

function cdashmu_render_technical_details(){
  echo "<h4>Member Updater Version: " . CDASHMU_VERSION . "</h4>";
}

function cdashmu_display_license_notice(){
  if(get_transient('cdashmu_active')){
    $plugin_slug = plugin_basename( __FILE__ );
  	$license_url = get_admin_url() . 'admin.php?page=chamber_dashboard_license';
  	add_option( 'Activated_Plugin', $plugin_slug );
  	$license_active = cdash_mu_edd_check_license();
  	global $pagenow;
  	if(!$license_active){
  		if($pagenow == 'plugins.php'){
  			echo "<div class='notice notice is-dismissible cdash_update_notice'><p>";
  			printf (__('Your license key for Chamber Dashboard Member Updater is either invalid or not activated. Please enter your license key and activate it <a href="' . $license_url . '">here</a>') );
  			echo "</p></div>";
  		}
  	}
  }
}
add_action( 'admin_notices', 'cdashmu_display_license_notice' );

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

function cdashmu_check_license_message($license_validity, $site_count, $license_limit, $license_expiry_date){
  $message = '';
  if($license_validity == 'valid'){
    $message .= '<p class="license_information" style="font-style:italic; font-size:12px;"><span class="cdash_active_license" style="color:green;">';
    $message .= __( 'Your license key is active. ' );
    $message .= '</span>';

    if(isset($license_expiry_date) && $license_expiry_date != 'lifetime'){
      $message .= __( 'It expires on ' . date_i18n( get_option( 'date_format' ), strtotime( $license_expiry_date, current_time( 'timestamp' ) ) ) );
    }
     $message .=  __(' You have ' . $site_count .'/' .$license_limit . ' sites active.' );
     $message .= '</p>';
  }elseif($license_validity == 'invalid'){
    $message .= '<p class="license_information" style="font-style:italic; font-size:12px;"><span class="cdash_inactive_license" style="color:red;">';
    $message .= __( 'Your license key is not active.' );
    $message .= '</span></p>';
  }
  return $message;
}

?>
