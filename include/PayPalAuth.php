<?php 

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;



class PayPalAuth
{
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }
    public static function environment()
    {
        $wpgv_paypal_client_id = get_option('wpgv_paypal_client_id') ? get_option('wpgv_paypal_client_id') : '';
        $wpgv_paypal_secret_key = get_option('wpgv_paypal_secret_key') ? get_option('wpgv_paypal_secret_key') : ''; 

        $clientId = getenv("CLIENT_ID") ?: $wpgv_paypal_client_id;
        $clientSecret = getenv("CLIENT_SECRET") ?: $wpgv_paypal_secret_key;
        
        return new SandboxEnvironment($clientId, $clientSecret);
    }
}
