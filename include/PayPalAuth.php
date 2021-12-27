<?php 

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

$wpgv_paypal_client_id = get_option('wpgv_paypal_client_id') ? get_option('wpgv_paypal_client_id') : '';
$wpgv_paypal_secret_key = get_option('wpgv_paypal_secret_key') ? get_option('wpgv_paypal_secret_key') : '';

$apiContext = wpgv_getApiContext($wpgv_paypal_client_id, $wpgv_paypal_secret_key);
// return $apiContext;
function wpgv_getApiContext($clientId, $clientSecret) {

    global $wpdb;
    $setting_table  = $wpdb->prefix . 'giftvouchers_setting';
    $setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );

    $mode = (!$setting_options->test_mode) ? 'live' : 'sandbox';

    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];

    $apiContext = new ApiContext(
        new OAuthTokenCredential(
            $clientId,
            $clientSecret
        )
    );

    $apiContext->setConfig(
        array(
            'mode' => $mode,
            'log.LogEnabled' => true,
            'log.FileName' => $upload_dir . '/voucherpdfuploads/PayPal.log',
            'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true,
        )
    );

    return $apiContext;
}
