<?php

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'WPGV_STORE_URL', 'https://www.codemenschen.at/' ); 

// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list (see example below)
define( 'WPGV_ITEM_ID', 80 );

// the name of your product. This should match the download name in EDD exactly
define( 'WPGV_ITEM_NAME', 'Gift Cards Pro' );

if( !class_exists( 'EDD_SL_Plugin_Updater_WPGIFT' ) ) {
	// load our custom updater if it doesn't already exist 
	include( dirname( __FILE__ ) . '/classes/EDD_SL_Plugin_Updater.php' );
}

function wpgv_sl_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'wpgv_license_key' ) );

	// setup the updater
	$wpgv_updater = new EDD_SL_Plugin_Updater_WPGIFT( WPGV_STORE_URL, __FILE__,
		array(
			'version' => WPGIFT_VERSION,	// current version number
			'license' => $license_key,	// license key (used get_option above to retrieve from DB)
			'item_id' => WPGV_ITEM_ID,	// ID of the product
			'author'  => 'codemenschen',	// author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'admin_init', 'wpgv_sl_plugin_updater', 0 );

function wpgv_register_option() {
	// creates our settings in the options table
	register_setting('wpgv_license', 'wpgv_license_key', 'wpgv_sanitize_license' );
}
add_action('admin_init', 'wpgv_register_option');

function wpgv_sanitize_license( $new ) {
	$old = get_option( 'wpgv_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'wpgv_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

function wpgv_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['wpgv_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'wpgv_nonce', 'wpgv_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'wpgv_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( WPGV_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( WPGV_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$message =  ( is_wp_error( $response ) && ! $response->get_error_message() ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'gift-voucher' );

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.', 'gift-voucher' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :

						$message = __( 'Your license key has been disabled.', 'gift-voucher' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.', 'gift-voucher' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.', 'gift-voucher' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'gift-voucher' ), WPGV_ITEM_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.', 'gift-voucher' );
						break;

					default :

						$message = __( 'An error occurred, please try again.', 'gift-voucher' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'admin.php?page=wpgv_license_page' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'wpgv_license_status', $license_data->license );
		wp_redirect( admin_url( 'admin.php?page=wpgv_license_page' ) );
		exit();
	}
}
add_action('admin_init', 'wpgv_activate_license');

/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function wpgv_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['wpgv_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'wpgv_nonce', 'wpgv_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'wpgv_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( WPGV_ITEM_NAME ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( WPGV_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$base_url = admin_url( 'admin.php?page=wpgv_license_page' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'wpgv_license_status' );
		}

		wp_redirect( admin_url( 'admin.php?page=wpgv_license_page' ) );
		exit();

	}
}
add_action('admin_init', 'wpgv_deactivate_license');


/************************************
* this illustrates how to check if
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function wpgv_check_license() {

	global $wp_version;

	$license = trim( get_option( 'wpgv_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' 	 => $license,
		'item_name'  => urlencode( WPGV_ITEM_NAME ), // the name of our product in EDD
		'url'        => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( WPGV_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function wpgv_admin_notices() {
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
add_action( 'admin_notices', 'wpgv_admin_notices' );