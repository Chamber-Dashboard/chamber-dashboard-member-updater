<?php
/* Settings Page */


function cdmu_render_license_key_form(){      
?>
    <div class="wrap">
        <!--<h1><?= esc_html(get_admin_page_title()); ?></h1>-->
        <?php cdash_mu_edd_license_page(); ?>
    </div>
    
<?php
}
?>
<?php
/*function edd_sample_license_menu() {
	add_plugins_page( 'Plugin License', 'Plugin License', 'manage_options', CDASHMU_EDD_PLUGIN_LICENSE_PAGE, 'cdash_mu_edd_license_page' );
}
add_action('admin_menu', 'edd_sample_license_menu');*/

function cdash_mu_edd_license_page() {
	$license = get_option( 'cdash_mu_edd_license_key' );
	$status  = get_option( 'cdash_mu_edd_license_status' );
	?>
	<div class="wrap">
		<!--<h2><?php _e('Member Updater License'); ?></h2>-->
		<form method="post" action="options.php">

			<?php settings_fields('cdash_mu_edd_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('Member Updater License Key'); ?>
						</th>
						<td>
							<input id="cdash_mu_edd_license_key" name="cdash_mu_edd_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="cdash_mu_edd_license_key"><?php _e('Enter your license key'); ?></label>
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Activate License'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active'); ?></span>
									<?php wp_nonce_field( 'cdash_mu_edd_nonce', 'cdash_mu_edd_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } else {
									wp_nonce_field( 'cdash_mu_edd_nonce', 'cdash_mu_edd_nonce' ); ?>
									<input type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function cdash_mu_edd_register_option() {
	// creates our settings in the options table
	register_setting('cdash_mu_edd_license', 'cdash_mu_edd_license_key', 'edd_sanitize_license' );
}
add_action('admin_init', 'cdash_mu_edd_register_option');

function edd_sanitize_license( $new ) {
	$old = get_option( 'cdash_mu_edd_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'cdash_mu_edd_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}



/************************************
* this illustrates how to activate
* a license key
*************************************/

function cdash_mu_edd_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'cdash_mu_edd_nonce', 'cdash_mu_edd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'cdash_mu_edd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( CDASHMU_EDD_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( CDASH_MU_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), CDASHMU_EDD_ITEM_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.' );
						break;

					default :

						$message = __( 'An error occurred, please try again.' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'admin.php?page=' . CDASHMU_EDD_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'cdash_mu_edd_license_status', $license_data->license );
		wp_redirect( admin_url( 'admin.php?page=' . CDASHMU_EDD_PLUGIN_LICENSE_PAGE ) );
		exit();
	}
}
add_action('admin_init', 'cdash_mu_edd_activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function cdash_mu_edd_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'cdash_mu_edd_nonce', 'cdash_mu_edd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'cdash_mu_edd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( CDASHMU_EDD_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( CDASH_MU_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( 'admin.php?page=' . CDASHMU_EDD_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'cdash_mu_edd_license_status' );
		}

		wp_redirect( admin_url( 'admin.php?page=' . CDASHMU_EDD_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action('admin_init', 'cdash_mu_edd_deactivate_license');


/************************************
* this illustrates how to check if
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function cdash_mu_edd_check_license() {

	global $wp_version;

	$license = trim( get_option( 'cdash_mu_edd_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( CDASHMU_EDD_ITEM_NAME ),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( CDASH_MU_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
    
	if( $license_data->license == 'valid' ) {
		echo 'valid'; 
        exit;
		// this license is still valid
	} else {
		echo 'invalid'; 
        exit;
		// this license is no longer valid
	}
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function cdash_mu_edd_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'cdash_mu_edd_admin_notices' );

?>