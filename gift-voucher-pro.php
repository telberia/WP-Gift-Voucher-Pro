<?php
/**
 * Plugin Name: Gift Cards Pro
 * Description: Let your customers buy gift cards/certificates for your services & products directly on your website.
 * Plugin URI: https://www.codemenschen.at/
 * Author: codemenschen
 * Author URI: https://www.codemenschen.at/
 * Version: 4.1.6
 * Text Domain: gift-voucher
 * Domain Path: /languages
 * License: GNU General Public License v2.0 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Plugin Variable: wpgiftv
 *
 * @package         Gift Cards Pro
 * @author          Aakash Gupta
 * @copyright       Copyright (c) 2020
 *
 */

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

define( 'WPGIFT_VERSION', '4.1.6' );
define( 'WPGIFT__MINIMUM_WP_VERSION', '4.0' );
define( 'WPGIFT__PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPGIFT__PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WPGIFT_FILE', basename( plugin_dir_path( __FILE__ ) ).'/'.basename( __FILE__ ));
define( 'WPGIFT_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'WPGIFT_SESSION_KEY', 'wpgv-gift-voucher-data' );
define( 'WPGIFT_INSTALL_DATE', 'wpgv-install-date' );
define( 'WPGIFT_ADMIN_NOTICE_KEY', 'wpgv-hide-notice' );

define( 'WPGV_PRODUCT_TYPE_SLUG', 'gift_voucher' );
define( 'WPGV_PRODUCT_TYPE_NAME', 'Gift Voucher' );
define( 'WPGV_DENOMINATION_ATTRIBUTE_SLUG', 'gift-voucher-amount' );
define( 'WPGV_MAX_MESSAGE_CHARACTERS', 500 );
define( 'WPGV_RECIPIENT_LIMIT', 999 );
define( 'WPGV_GIFT_VOUCHER_NUMBER_META_KEY', 'wpgv_gift_voucher_number' );
define( 'WPGV_AMOUNT_META_KEY', 'wpgv_gift_voucher_amount' );
define( 'WPGV_YOUR_NAME_META_KEY', 'wpgv_your_name' );
define( 'WPGV_RECIPIENT_NAME_META_KEY', 'wpgv_recipient_name' );
define( 'WPGV_RECIPIENT_EMAIL_META_KEY', 'wpgv_recipient_email' );
define( 'WPGV_YOUR_EMAIL_META_KEY', 'wpgv_your_email' );
define( 'WPGV_MESSAGE_META_KEY', 'wpgv_message' );

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function wpgiftv_plugin_init() {
  $langOK = load_plugin_textdomain( 'gift-voucher', false, dirname( plugin_basename(__FILE__) ) .'/languages' );
}
add_action('plugins_loaded', 'wpgiftv_plugin_init');

function wpgv_is_woocommerce_enable(){
    global $wpdb;
    $setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
    $options = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );
    if($options->is_woocommerce_enable) {
        return true;
    }
    else {
        return false;
    } 
}
require_once( WPGIFT__PLUGIN_DIR .'/upgrade.php');
require_once( WPGIFT__PLUGIN_DIR .'/vendor/autoload.php');
require_once( WPGIFT__PLUGIN_DIR .'/vendor/sofort/payment/sofortLibSofortueberweisung.inc.php');
require_once( WPGIFT__PLUGIN_DIR .'/vendor/multisafepay/models/API/Autoloader.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/rotation.php');
require_once( WPGIFT__PLUGIN_DIR .'/admin.php');
require_once( WPGIFT__PLUGIN_DIR .'/front.php');
require_once( WPGIFT__PLUGIN_DIR .'/giftitems.php');
require_once( WPGIFT__PLUGIN_DIR .'/giftcard.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/fpdf.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/voucher.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/template.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/page_template.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/wpgv_voucher_pdf.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/wpgv_giftcard_pdf.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/admin_wpgv_voucher_pdf.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/wpgv_item_pdf.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/voucher_posttype.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/voucher_metabox.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/voucher-shortcodes.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/wpgv-gift-voucher.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/wpgv-gift-voucher-activity.php');

if(wpgv_is_woocommerce_enable()){	
    require_once( WPGIFT__PLUGIN_DIR .'/classes/wpgv-voucher-product-list.php');
    require_once( WPGIFT__PLUGIN_DIR .'/include/wc_wpgv_voucher_pdf.php');
    require_once( WPGIFT__PLUGIN_DIR .'/classes/wpgv-check-plugin-active.php');
}

add_action( 'plugins_loaded', function () {
  WPGiftVoucherAdminPages::get_instance();
} );


add_action( 'admin_init', function () {

  // Check if user is an administrator
  if ( ! current_user_can( 'manage_options' ) ) {
    return false;
  }

  // include nag class
  require_once( WPGIFT__PLUGIN_DIR . '/classes/class-nag.php' );

  // setup nag
  $nag = new WPGIFT_Nag();
  $nag->setup();

});

add_action( 'woocommerce_init', 'wpgv_files_loaded', 10, 1 );
function wpgv_files_loaded() {
    global $wpdb;
    $setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
    $options = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );
    if($options->is_woocommerce_enable) {
      require_once( WPGIFT__PLUGIN_DIR .'/include/redeem-voucher.php');
      require_once( WPGIFT__PLUGIN_DIR .'/classes/wpgv-gift-voucher-product.php');
      require_once( WPGIFT__PLUGIN_DIR .'/classes/wc-order-item-wpgv-gift-voucher.php');
      require_once( WPGIFT__PLUGIN_DIR .'/classes/wpgv-wc-product-gift-voucher.php');
      require_once( WPGIFT__PLUGIN_DIR .'/classes/data-stores/wc-order-item-wpgv-gift-voucher-data-store.php');
      require_once( WPGIFT__PLUGIN_DIR .'/include/wpgv-product-settings.php');
      if ( is_admin() ) {
          require_once( WPGIFT__PLUGIN_DIR .'/admin/wpgv-gift-voucher-admin.php' );
      }
      add_action( 'wcml_is_variable_product', 'wcml_is_variable_product' ,10, 2 );
      add_filter( 'wpgv_to_current_currency', 'wpgv_to_current_currency' );
      add_filter( 'wpgv_to_default_currency','wpgv_to_default_currency');
    }
}

function wpgv_to_default_currency( $amount ) {
    // WooCommerce Currency Switcher by realmag777
    if ( isset( $GLOBALS['WOOCS'] ) && method_exists( $GLOBALS['WOOCS'], 'get_currencies' ) && method_exists( $GLOBALS['WOOCS'], 'back_convert' ) ) {
        $cs = $GLOBALS['WOOCS'];
        $default_currency = false;
        $currencies = $cs->get_currencies();

        foreach ( $currencies as $currency ) {
            if ( $currency['is_etalon'] === 1 ) {
                $default_currency = $currency;
                break;
            }
        }

        if ( $default_currency ) {
            if ( $cs->current_currency != $default_currency['name'] ) {
                return (float) $cs->back_convert( $amount, $currencies[ $cs->current_currency ]['rate'] );
            }
        }
    }

    // Aelia Currency Switcher
    if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) && isset( $GLOBALS['woocommerce-aelia-currencyswitcher'] ) ) {
        $cs = $GLOBALS['woocommerce-aelia-currencyswitcher'];

        $current_currency = $cs->get_selected_currency();
        $base_currency = $cs->base_currency();

        if ( $current_currency != $base_currency && !empty( $cs->current_exchange_rate() ) ) {
            return (float) number_format( ( 1 / $cs->current_exchange_rate() ) * $amount, 6, '.', '' );
        }
    }

    // WooCommerce Ultimate Multi Currency Suite
    if ( class_exists( 'WooCommerce_Ultimate_Multi_Currency_Suite_Main' ) && isset( $GLOBALS['woocommerce_ultimate_multi_currency_suite'] ) ) {
        $cs = $GLOBALS['woocommerce_ultimate_multi_currency_suite'];
        if ( is_object( $cs ) && property_exists( $cs, 'frontend' ) && !empty( $cs->frontend ) ) {
            return $cs->frontend->unconvert_price( $amount );
        }
    }

    // WPML (WooCommerce Multilingual plugin)
    if ( isset( $GLOBALS['woocommerce_wpml'] ) ) {
        $wpml = $GLOBALS['woocommerce_wpml'];
        if ( is_object( $wpml ) && property_exists( $wpml, 'multi_currency' ) && is_object( $wpml->multi_currency ) && property_exists( $wpml->multi_currency, 'prices' ) ) {
            $cs = $wpml->multi_currency->prices;
            return $cs->unconvert_price_amount( $amount );
        }
    }

    // Multi Currency for WooCommerce by VillaTheme
    if ( function_exists( 'wmc_get_price' ) ) {
        $exchange = wmc_get_price( '1' );
        return (float) number_format( ( 1 / $exchange ) * $amount, 6, '.', '' );
    }

    // WooCommerce Price Based on Country by Oscar Gare
    if ( function_exists( 'wcpbc_get_zone_by_country' ) ) {
        $zone = wcpbc_get_zone_by_country();
        if ( !empty( $zone ) && method_exists( $zone, 'get_base_currency_amount' ) ) {
            $amount = $zone->get_base_currency_amount( $amount );
        }
    }

    // Multi-Currency for WooCommerce by TIV.NET INC
    if ( class_exists( 'WOOMC\App' ) ) {
        $currency_detector = new WOOMC\Currency\Detector();
        $rate_storage = new WOOMC\Rate\Storage();
        $price_rounder = new WOOMC\Price\Rounder();
        $price_calculator = new WOOMC\Price\Calculator( $rate_storage, $price_rounder );

        $to = $currency_detector->getDefaultCurrency();
        $from = $currency_detector->currency();

        return $price_calculator->calculate( (float) $amount, $to, $from );
    }

    return $amount;
}

function wcml_is_variable_product( $is_variable_product, $product_id ) {
      $product = wc_get_product( $product_id );
      if ( is_a( $product, 'wpgv_wc_product_gift_voucher' ) ) {
          $is_variable_product = true;
      }

      return $is_variable_product;
}

function wpgv_to_current_currency( $amount ) {
    // WooCommerce Currency Switcher by realmag777
    if ( isset( $GLOBALS['WOOCS'] ) && method_exists( $GLOBALS['WOOCS'], 'woocs_convert_price' ) ) {
        return $GLOBALS['WOOCS']->woocs_convert_price( $amount );
    }

    // Aelia Currency Switcher
    if ( class_exists( 'WC_Aelia_CurrencySwitcher' ) && isset( $GLOBALS['woocommerce-aelia-currencyswitcher'] ) ) {
        $cs = $GLOBALS['woocommerce-aelia-currencyswitcher'];
        return $cs->convert( $amount, $cs->base_currency(), $cs->get_selected_currency() );
    }

    // WooCommerce Ultimate Multi Currency Suite
    if ( class_exists( 'WooCommerce_Ultimate_Multi_Currency_Suite_Main' ) && isset( $GLOBALS['woocommerce_ultimate_multi_currency_suite'] ) ) {
        $cs = $GLOBALS['woocommerce_ultimate_multi_currency_suite'];
        if ( is_object( $cs ) && property_exists( $cs, 'frontend' ) && !empty( $cs->frontend ) ) {
            return $cs->frontend->convert_price( $amount );
        }
    }

    // WPML (WooCommerce Multilingual plugin)
    if ( isset( $GLOBALS['woocommerce_wpml'] ) ) {
        $wpml = $GLOBALS['woocommerce_wpml'];
        if ( is_object( $wpml ) && property_exists( $wpml, 'multi_currency' ) && is_object( $wpml->multi_currency ) && property_exists( $wpml->multi_currency, 'prices' ) ) {
            $cs = $wpml->multi_currency;
            return $cs->prices->convert_price_amount( $amount );
        }
    }

    // Multi Currency for WooCommerce by VillaTheme
    if ( function_exists( 'wmc_get_price' ) ) {
        return wmc_get_price( $amount );
    }

    // WooCommerce Price Based on Country by Oscar Gare
    if ( function_exists( 'wcpbc_the_zone' ) ) {
        $zone = wcpbc_the_zone();
        if ( !empty( $zone ) && method_exists( $zone, 'get_exchange_rate_price' ) ) {
            return $zone->get_exchange_rate_price( $amount );
        }
    }

    // Multi-Currency for WooCommerce by TIV.NET INC
    if ( class_exists( 'WOOMC\App' ) ) {
        $user = WOOMC\App::instance()->getUser();

        $currency_detector = new WOOMC\Currency\Detector();
        $rate_storage = new WOOMC\Rate\Storage();
        $price_rounder = new WOOMC\Price\Rounder();
        $price_calculator = new WOOMC\Price\Calculator( $rate_storage, $price_rounder );

        $to = $currency_detector->currency();
        $from = $currency_detector->getDefaultCurrency();

        return $price_calculator->calculate( (float) $amount, $to, $from );
    }

    return $amount;
}


add_action( 'plugins_loaded', 'wpgv_voucher_imagesize_setup' );
function wpgv_voucher_imagesize_setup() {
    add_image_size( 'voucher-thumb', 300 );
    add_image_size( 'voucher-medium', 450 );
}

/** Setting menu link */
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'wpgv_settings_page_link');
function wpgv_settings_page_link( $links ) {
    $links[] = '<a href="' .admin_url( 'admin.php?page=voucher-setting' ) .'">' . __('Settings', 'gift-voucher') . '</a>';
    $links[] = '<a href="https://codemenschen.at/docs/wordpress-gift-vouchers-documentation/" target="_blank">' . __('Documentation', 'gift-voucher') . '</a>';
    return $links;
}
function wpgv_front_enqueue() {
	global $wpdb;
	$setting_table = $wpdb->prefix . 'giftvouchers_setting';
  	$translations = array( 
        'ajaxurl' => admin_url('admin-ajax.php'), 
        'not_equal_email' => __('This email must be different from above email.', 'gift-voucher'), 
        'select_template' => __('Please select voucher template', 'gift-voucher'), 
        'accept_terms' => __('Please accept the terms and conditions', 'gift-voucher'), 
        'finish' => __('Finish', 'gift-voucher'), 
        'next' => __('Continue', 'gift-voucher'), 
        'previous' => __('Back', 'gift-voucher'), 
        'submitted' => __('Submitted!', 'gift-voucher'), 
        'error_occur' => __('Error occurred', 'gift-voucher'), 
        'total_character' => __('Total Characters', 'gift-voucher'), 
        'via_post' => __('Shipping via Post', 'gift-voucher'), 
        'via_email' => __('Shipping via Email', 'gift-voucher'), 
        'checkemail' => __('Please check email address.', 'gift-voucher'), 
        'required' => __('This field is required.', 'gift-voucher'), 
        'remote' => __('Please fix this field.', 'gift-voucher'), 
        'maxlength' => __('Please enter no more than {0} characters.', 'gift-voucher'), 
        'email' => __('Please enter a valid email address.', 'gift-voucher'), 
        'max' => __('Please enter a value less than or equal to {0}.', 'gift-voucher'), 
        'min' => __('Please enter a value greater than or equal to {0}.', 'gift-voucher'),
        'preview' => __('This is Preview!', 'gift-voucher'),
        'text_value' => __('Value', 'gift-voucher'),
		'error_cardnumber' => __('Enter a card number.', 'gift-voucher'),
		'your_name' => __('Your Name', 'gift-voucher'),
        'gift_to' => __('Gift To', 'gift-voucher'),
        'gift_from' => __('Gift From', 'gift-voucher'),
        'min_value' => __('Min value', 'gift-voucher'),
    );
	wp_register_style('wpgv-voucher-style',  WPGIFT__PLUGIN_URL.'/assets/css/voucher-style.css');
	wp_register_style('wpgv-item-style',  WPGIFT__PLUGIN_URL.'/assets/css/item-style.css');
	wp_register_style('wpgv-bootstrap-css',  WPGIFT__PLUGIN_URL.'/assets/css/bootstrap.min.css');
	wp_register_style('wpgv-fontawesome-css',  WPGIFT__PLUGIN_URL.'/assets/css/font-awesome.min.css');
	wp_register_style('wpgv-bootstrap-datetimepicker-css',  WPGIFT__PLUGIN_URL.'/assets/css/bootstrap-datetimepicker.min.css');
	wp_register_style('wpgv-slick-css',  WPGIFT__PLUGIN_URL.'/assets/css/slick.css');
	wp_register_style('wpgv-voucher-template-fonts-css',  WPGIFT__PLUGIN_URL.'/assets/css/voucher-template-fonts.css');
	wp_register_style('wpgv-voucher-template-style-css',  WPGIFT__PLUGIN_URL.'/assets/css/voucher-template-style.css');
	wp_register_script('wpgv-bootstrap-js', WPGIFT__PLUGIN_URL  . '/assets/js/bootstrap.min.js', array('jquery'), '1.17.0', true);
	wp_register_script('wpgv-konva-min-js', WPGIFT__PLUGIN_URL  . '/assets/js/konva.min.js', array('jquery'), '1.17.0', true);
	wp_register_script('wpgv-jspdf-js', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js', array('jquery'), '1.5.3', true);
	wp_register_script('wpgv-bootstrap-datetimepicker-js', WPGIFT__PLUGIN_URL  . '/assets/js/bootstrap-datetimepicker.min.js', array('jquery'), '1.17.0', true);
	wp_register_script('wpgv-jquery-validate', WPGIFT__PLUGIN_URL  . '/assets/js/jquery.validate.min.js', array('jquery'), '1.17.0', true);
	wp_register_script('wpgv-jquery-steps', WPGIFT__PLUGIN_URL  . '/assets/js/jquery.steps.min.js', array('jquery'), '1.1.0', true);
	
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	if($setting_options->stripe){
		wp_register_script('wpgv-stripe-js', 'https://js.stripe.com/v3/', array('jquery'), NULL, true);
	}
	wp_register_script('wpgv-slick-script', WPGIFT__PLUGIN_URL  . '/assets/js/slick.min.js', array('jquery'), WPGIFT_VERSION, true);
	wp_register_script('wpgv-voucher-script', WPGIFT__PLUGIN_URL  . '/assets/js/voucher-script.js', array('jquery'), WPGIFT_VERSION, true);
	wp_register_script('wpgv-voucher-template-script', WPGIFT__PLUGIN_URL  . '/assets/js/voucher-template-script.js', array('jquery'), WPGIFT_VERSION, true);
	
	wp_register_script('wpgv-item-script', WPGIFT__PLUGIN_URL  . '/assets/js/item-script.js', array('jquery'), WPGIFT_VERSION, true);
	wp_register_script('wpgv-woocommerce-script', WPGIFT__PLUGIN_URL  . '/assets/js/woocommerce-script.js', array('jquery'), WPGIFT_VERSION, true);
	wp_register_script( 'wpgv-voucher-product', WPGIFT__PLUGIN_URL  .'/assets/js/wpgv-voucher-product.js', array( 'jquery' ), WPGIFT_VERSION, true );

	wp_localize_script('wpgv-voucher-script', 'frontend_ajax_object', $translations );
	wp_localize_script('wpgv-voucher-template-script', 'frontend_ajax_object', $translations );
	wp_localize_script('wpgv-item-script', 'frontend_ajax_object', $translations );
	wp_localize_script('wpgv-woocommerce-script', 'frontend_ajax_object', $translations );
	if(wpgv_is_woocommerce_enable()){
		$check_plugin = new WPGV_Check_Plugin_Active();
    	if($check_plugin->wpgv_check_woo_active()) {
			wp_localize_script( 'wpgv-voucher-product', 'wpgv', array(
				'ajaxurl'                       => admin_url( 'admin-ajax.php', 'relative' ),
				'denomination_attribute_slug'   => WPGV_DENOMINATION_ATTRIBUTE_SLUG,
				'decimal_places'                => wc_get_price_decimals(),
				'max_message_characters'        => WPGV_MAX_MESSAGE_CHARACTERS,
				'i18n'                          => array(
					'custom_amount_required_error' => __( 'Required', 'gift-voucher' ),
					'min_amount_error'          => sprintf( __( 'Minimum amount is %s', 'gift-voucher' ), get_woocommerce_currency_symbol() ),
					'max_amount_error'          => sprintf( __( 'Maximum amount is %s', 'gift-voucher' ), get_woocommerce_currency_symbol() ),
					'invalid_recipient_error'   => __( 'The "To" field should only contain email addresses. The following recipients do not look like valid email addresses:', 'gift-voucher' ),
				),
				'nonces' => array(
					'check_balance'             => wp_create_nonce( 'wpgv-gift-cards-check-balance' ),
					'apply_gift_card'           => wp_create_nonce( 'wpgv-gift-cards-apply-gift-card' ),
					'remove_card'               => wp_create_nonce( 'wpgv-gift-cards-remove-card' ),
				)
			));
		}
	}
}

add_action( 'wp_enqueue_scripts','wpgv_front_enqueue');

function wpgv_plugin_activation() {
	global $wpdb;
	global $jal_db_version;

	$giftvouchers_setting = $wpdb->prefix . 'giftvouchers_setting';
	$giftvouchers_list = $wpdb->prefix . 'giftvouchers_list';
	$giftvouchers_template = $wpdb->prefix . 'giftvouchers_template';
	$giftvouchers_activity = $wpdb->prefix . 'giftvouchers_activity';
	$giftvouchers_invoice_settings = $wpdb->prefix . 'giftvouchers_invoice_settings';
	$charset_collate = $wpdb->get_charset_collate();

  	$giftvouchers_setting_sql = "CREATE TABLE $giftvouchers_setting (
        id int(11) NOT NULL AUTO_INCREMENT,
        is_woocommerce_enable int(1) DEFAULT 0,
        is_stripe_enable int(1) DEFAULT 0,
		is_order_form_enable int(1) DEFAULT 1,
        is_style_choose_enable int(1) DEFAULT 0,
        voucher_style varchar(100) DEFAULT 0,
        company_name varchar(255) DEFAULT NULL,
        currency_code varchar(10) DEFAULT NULL,
        currency varchar(10) DEFAULT NULL,
        currency_position varchar(10) DEFAULT NULL,
        voucher_bgcolor varchar(6) DEFAULT NULL,
        voucher_color varchar(6) DEFAULT NULL,
        template_col int(2) DEFAULT 3,
        voucher_min_value int(4) DEFAULT NULL,
        voucher_max_value int(6) DEFAULT NULL,
        voucher_expiry_type varchar(6) DEFAULT NULL,
        voucher_expiry varchar(10) DEFAULT NULL,
        voucher_terms_note text DEFAULT NULL,
        custom_loader text DEFAULT NULL,
        pdf_footer_url varchar(255) DEFAULT NULL,
        pdf_footer_email varchar(255) DEFAULT NULL,
        post_shipping int(1) DEFAULT NULL,
        shipping_method text DEFAULT NULL,
        preview_button int(1) DEFAULT 1,
        paypal int(11) DEFAULT NULL,
        sofort int(11) DEFAULT NULL,
        stripe int(11) DEFAULT NULL,
        paypal_email varchar(100) DEFAULT NULL,
        sofort_configure_key varchar(255) DEFAULT NULL,
        reason_for_payment varchar(255) DEFAULT NULL,
        stripe_publishable_key varchar(255) DEFAULT NULL,
        stripe_secret_key varchar(255) DEFAULT NULL,
        sender_name varchar(255) DEFAULT NULL,
        sender_email varchar(255) DEFAULT NULL,
        test_mode int(10) NOT NULL,
        per_invoice int(10) NOT NULL,
        bank_info longtext,
        landscape_mode_templates text DEFAULT NULL,
        portrait_mode_templates text DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

  	$giftvouchers_list_sql = "CREATE TABLE $giftvouchers_list (
        id int(11) NOT NULL AUTO_INCREMENT,
		order_type enum('items', 'vouchers','gift_voucher_product') NOT NULL DEFAULT 'vouchers',
        template_id int(11) NOT NULL,
		product_id int(11) NOT NULL,
        order_id int(11) NOT NULL,
        itemcat_id int(11) NOT NULL,
        item_id int(11) NOT NULL,
        buying_for enum('someone_else', 'yourself') NOT NULL DEFAULT 'someone_else',
        from_name varchar(255) NOT NULL,
        to_name varchar(255) NOT NULL,
        amount float NOT NULL,
        message text NOT NULL,
        firstname varchar(255) NOT NULL,
        lastname varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        address text NOT NULL,
        postcode varchar(30) NOT NULL,
        pay_method varchar(255) NOT NULL,
        shipping_type enum('shipping_as_email', 'shipping_as_post') NOT NULL DEFAULT 'shipping_as_email',
        shipping_email varchar(255) NOT NULL,
        shipping_method varchar(255) NOT NULL,
        expiry varchar(100) NOT NULL,
        couponcode bigint(25) NOT NULL,
        voucherpdf_link text NOT NULL,
        voucheradd_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status varchar(10) NOT NULL DEFAULT 'unused',
        payment_status varchar(10) NOT NULL DEFAULT 'Not Pay',
        email_send_date_time varchar(30) NOT NULL DEFAULT 'send_instantly',
        PRIMARY KEY (id)
    ) $charset_collate;";

  	$giftvouchers_template_sql = "CREATE TABLE $giftvouchers_template (
        id int(11) NOT NULL AUTO_INCREMENT,
        title text NOT NULL,
        image int(11) DEFAULT NULL,
        image_style varchar(100) DEFAULT NULL,
        orderno int(11) NOT NULL DEFAULT '0',
        active int(11) NOT NULL DEFAULT '0',
        templateadd_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

  	$giftvouchers_activity_sql = "CREATE TABLE $giftvouchers_activity (
        id int(11) NOT NULL AUTO_INCREMENT,
        voucher_id int(11) NOT NULL,
        user_id int(11) NOT NULL,
        action varchar(60) DEFAULT NULL,
        amount decimal(15,6),
        note text NOT NULL,
        activity_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

  	$giftvouchers_invoice_settings_sql = "CREATE TABLE $giftvouchers_invoice_settings (
        id int(11) NOT NULL AUTO_INCREMENT,
        is_invoice_active int(11) NOT NULL DEFAULT 0,
        company_name varchar(255) DEFAULT NULL,
        invoice_color varchar(255) DEFAULT NULL,
        company_logo varchar(255) DEFAULT NULL,
        address_line1 varchar(255) DEFAULT NULL,
        address_line2 varchar(255) DEFAULT NULL,
        address_line3 varchar(255) DEFAULT NULL,
        address_line4 varchar(255) DEFAULT NULL,
        bottom_line varchar(255) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $giftvouchers_setting_sql );
	dbDelta( $giftvouchers_list_sql );
	dbDelta( $giftvouchers_template_sql );
	dbDelta( $giftvouchers_activity_sql );
	dbDelta( $giftvouchers_invoice_settings_sql );
  	add_option( 'jal_db_version', $jal_db_version );

  	$demoimageurl = get_option('wpgv_demoimageurl') ? get_option('wpgv_demoimageurl') : WPGIFT__PLUGIN_URL.'/assets/img/demo.png';
  	update_option('wpgv_demoimageurl', $demoimageurl);
  	// set template lanscape
	$template_lanscape = 'template-custom-lanscape.png, template-voucher-lanscape-1.png, template-voucher-lanscape-2.png, template-voucher-lanscape-3.png, template-voucher-lanscape-4.png, template-voucher-lanscape-5.png, template-voucher-lanscape-6.png, template-voucher-lanscape-7.png, template-voucher-lanscape-8.png, template-voucher-lanscape-9.png, template-voucher-lanscape-10.png, template-voucher-lanscape-11.png, template-voucher-lanscape-12.png, template-voucher-lanscape-13.png, template-voucher-lanscape-14.png, template-voucher-lanscape-15.png, template-voucher-lanscape-16.png, template-voucher-lanscape-17.png, template-voucher-lanscape-18.png, template-voucher-lanscape-19.png, template-voucher-lanscape-20.png, template-voucher-lanscape-21.png, template-voucher-lanscape-22.png, template-voucher-lanscape-23.png, template-voucher-lanscape-24.png, template-voucher-lanscape-25.png, template-voucher-lanscape-26.png, template-voucher-lanscape-27.png, template-voucher-lanscape-28.png, template-voucher-lanscape-29.png, template-voucher-lanscape-30.png, template-voucher-lanscape-31.png, template-voucher-lanscape-32.png, template-voucher-lanscape-33.png, template-voucher-lanscape-34.png, template-voucher-lanscape-35.png, template-voucher-lanscape-36.png, template-voucher-lanscape-37.png, template-voucher-lanscape-37.png, template-voucher-lanscape-38.png, template-voucher-lanscape-39.png, template-voucher-lanscape-40.png, template-voucher-lanscape-41.png, template-voucher-lanscape-42.png, template-voucher-lanscape-43.png, template-voucher-lanscape-44.png, template-voucher-lanscape-45.png, template-voucher-lanscape-46.png, template-voucher-lanscape-47.png, template-voucher-lanscape-48.png, template-voucher-lanscape-49.png, template-voucher-lanscape-50.png, template-voucher-lanscape-51.png, template-voucher-lanscape-52.png, template-voucher-lanscape-53.png';
	$template_portail = 'template-custom-portail.png, template-voucher-portail-1.png, template-voucher-portail-2.png, template-voucher-portail-3.png, template-voucher-portail-4.png, template-voucher-portail-5.png, template-voucher-portail-6.png, template-voucher-portail-7.png, template-voucher-portail-8.png, template-voucher-portail-9.png, template-voucher-portail-10.png, template-voucher-portail-11.png, template-voucher-portail-12.png, template-voucher-portail-13.png, template-voucher-portail-14.png, template-voucher-portail-15.png, template-voucher-portail-16.png, template-voucher-portail-17.png, template-voucher-portail-18.png, template-voucher-portail-19.png, template-voucher-portail-20.png, template-voucher-portail-21.png, template-voucher-portail-22.png, template-voucher-portail-23.png, template-voucher-portail-24.png, template-voucher-portail-25.png, template-voucher-portail-26.png, template-voucher-portail-27.png, template-voucher-portail-28.png, template-voucher-portail-29.png, template-voucher-portail-30.png, template-voucher-portail-31.png, template-voucher-portail-32.png, template-voucher-portail-33.png, template-voucher-portail-34.png, template-voucher-portail-35.png, template-voucher-portail-36.png, template-voucher-portail-37.png, template-voucher-portail-38.png, template-voucher-portail-39.png, template-voucher-portail-40.png, template-voucher-portail-41.png, template-voucher-portail-42.png, template-voucher-portail-43.png';
	$company_name = get_bloginfo( 'name' );
	$paypal_email = get_option('admin_email');
  
	if(!$wpdb->get_var( "SELECT * FROM $giftvouchers_setting WHERE id = 1" )) {
		$wpdb->insert( 
		$giftvouchers_setting,
			array( 
				'is_woocommerce_enable' => 0,
				'is_stripe_enable' => 0,
				'is_order_form_enable' => 1,
				'is_style_choose_enable' => 0,
				'voucher_style'      => 0,
				'company_name'       => $company_name,
				'paypal_email'       => $paypal_email,
				'reason_for_payment' => 'Payment for Gift Cards',
				'sender_name'        => $company_name,
				'sender_email'       => $paypal_email,
				'currency_code'      => 'USD',
				'currency'           => '$',
				'paypal'             => 1,
				'sofort'             => 0,
				'stripe'             => 0,
				'voucher_bgcolor'    => '81c6a9',
				'voucher_color'      => '555555',
				'template_col'       => 4,
				'voucher_min_value'  => 0,
				'voucher_max_value'  => 10000,
				'voucher_expiry_type'=> 'days',
				'voucher_expiry'     => 60,
				'voucher_terms_note' => 'Note: The voucher is valid for 60 days and can be redeemed at '.$company_name.'. A cash payment is not possible.',
				'custom_loader'      => WPGIFT__PLUGIN_URL.'/assets/img/loader.gif',
				'pdf_footer_url'     => get_site_url(),
				'pdf_footer_email'   => $paypal_email,
				'post_shipping'      => 1,
				'shipping_method'    => '5.99 : Express Shipping - $5.99, 3.99 : Standard Shipping - $3.99',
				'preview_button'     => 1,
				'currency_position'  => 'Left',
				'test_mode'          => 0,
				'per_invoice'        => 0,
				'landscape_mode_templates' => $template_lanscape,
				'portrait_mode_templates' => $template_portail,
			)
		);
		$wpdb->insert( 
		$giftvouchers_template,
			array( 
				'title'  => "Demo Template",
				'active' => 1,
			)
		);
	}  
	// Check and update template
	$data_setting = $wpdb->get_row( "SELECT * FROM $giftvouchers_setting WHERE id = 1");
	if(empty($data_setting->landscape_mode_templates)) {
		$wpdb->update(
			$giftvouchers_setting,
			array( 
			'landscape_mode_templates' => $template_lanscape,
			'portrait_mode_templates' => $template_portail,
			),
			array('id'=>1)
		);
	}else{
		$wpdb->update(
			$giftvouchers_setting,
			array( 
			'landscape_mode_templates' => $template_lanscape,
			'portrait_mode_templates' => $template_portail,
			),
			array('id'=>1)
		);
	}
	//setting database json 
	$upload = wp_upload_dir();
	$upload_dir = $upload['basedir'];
	$upload_dir = $upload_dir . '/voucherpdfuploads';
	if (! is_dir($upload_dir)) {
		mkdir( $upload_dir, 0755 );
		$file = fopen($upload_dir.'/index.html',"wb");
		echo fwrite($file,"Silence is golden.");
		fclose($file);
	}

	if (! wp_next_scheduled ( 'wpgv_check_voucher_status' )) {
		wp_schedule_event(time(), 'hourly', 'wpgv_check_voucher_status');
	}
	set_transient( 'wpgv_activated', 1 );
	require_once( WPGIFT__PLUGIN_DIR . '/classes/class-nag.php' );
	WPGIFT_Nag::insert_install_date();
}
register_activation_hook( __FILE__, 'wpgv_plugin_activation' );

function wpgv_upgrade_completed( $upgrader_object, $options ) {
	// The path to our plugin's main file
	$our_plugin = plugin_basename( __FILE__ );
	// If an update has taken place and the updated type is plugins and the plugins element exists
 	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
	// Iterate through the plugins being updated and check if ours is there
		foreach( $options['plugins'] as $plugin ) {
			if( $plugin == $our_plugin ) {
				global $wpdb;
				$giftvouchers_setting = $wpdb->prefix . 'giftvouchers_setting';
				$giftvouchers_list = $wpdb->prefix . 'giftvouchers_list';
				$giftvouchers_template = $wpdb->prefix . 'giftvouchers_template';
				$giftvouchers_activity = $wpdb->prefix . 'giftvouchers_activity';
				$charset_collate = $wpdb->get_charset_collate();
				$giftvouchers_activity_sql = "CREATE TABLE IF NOT EXISTS $giftvouchers_activity (
					id int(11) NOT NULL AUTO_INCREMENT,
					voucher_id int(11) NOT NULL,
					user_id int(11) NOT NULL,
					action varchar(60) DEFAULT NULL,
					amount decimal(15,6),
					note text NOT NULL,
					activity_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (id)
				) $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $giftvouchers_activity_sql );

				$wpdb->query("ALTER TABLE $giftvouchers_template ADD image_style varchar(100) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD is_woocommerce_enable int(1) DEFAULT 0");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD post_shipping int(1) DEFAULT 0");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD preview_button int(1) DEFAULT 1");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD pdf_footer_url varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD is_woocommerce_enable varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD is_style_choose_enable int(1) DEFAULT 0");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD voucher_style varchar(100) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD currency varchar(10) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD currency_code varchar(10) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_list ADD email_send_date_time varchar(30) NOT NULL DEFAULT 'send_instantly'");

				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD COLUMN is_stripe_enable int(1) DEFAULT 0");
				$wpdb->query("ALTER TABLE $giftvouchers_setting ADD COLUMN is_order_form_enable int(1) DEFAULT 1");

				$wpdb->query("ALTER TABLE $giftvouchers_list MODIFY COLUMN order_type enum('items','vouchers','gift_voucher_product')");
				$wpdb->query("ALTER TABLE $giftvouchers_setting MODIFY COLUMN stripe_publishable_key varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_setting MODIFY COLUMN stripe_secret_key varchar(255) DEFAULT NULL");
				
				
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN company_name varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN invoice_color varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN company_logo varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN address_line1 varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN address_line2 varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN address_line3 varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN address_line4 varchar(255) DEFAULT NULL");
				$wpdb->query("ALTER TABLE $giftvouchers_invoice_settings MODIFY COLUMN bottom_line varchar(255) DEFAULT NULL");

				$orders = $wpdb->get_results( "SELECT id,from_name,amount FROM $giftvouchers_list WHERE id NOT IN (SELECT voucher_id FROM $giftvouchers_activity) AND `status` = 'unused' AND `payment_status` = 'Paid'" );
				foreach ($orders as $order) {
					WPGV_Gift_Voucher_Activity::record( $order->id, 'create', '', 'Voucher ordered by'. $order->from_name );
					WPGV_Gift_Voucher_Activity::record( $order->id, 'transaction', $order->amount, 'Voucher payment recieved.' );
				}

				$templates = $wpdb->get_results( "SELECT id,image FROM $giftvouchers_template WHERE `image_style` IS NULL" );
				foreach ($templates as $template) {
					$wpdb->update(
						$giftvouchers_template,
						array( 
						'image_style' => '["'.$template->image.'","",""]',
						),
						array('id'=>$template->id)
					);
				}

				$items = get_posts(array('posts_per_page' => -1,'post_type' => 'wpgv_voucher_product'));
				foreach ($items as $item) {
				update_post_meta($item->ID, 'style1_image', get_post_thumbnail_id($item->ID));
				}

				// Set a transient to record that our plugin has just been updated
				set_transient( 'wpgv_updated', 1 );
			}
		}
	}
}
add_action( 'upgrader_process_complete', 'wpgv_upgrade_completed', 10, 2 );

function wpgv_display_update_notice() {
 	if( get_transient( 'wpgv_updated' ) ) {
  		$class = 'notice notice-info';
		$message = sprintf('Thanks for Updating <b>Gift Cards</b> plugin. Please see the new plugin settings features from <a href="%s" target="_blank">here</a>. We upgraded PayPal (New Checkout) and Stripe (SCA-ready) payment process so you need to update the fields of these payment methods in the settings page. Please see here the documentation of new payment settings <a href="%s" target="_blank">here</a>.<br><br>We have noticed that you have been using Gift Cards plugin from long time. We hope you love it, and we would really appreciate it if you would <a href="%s" target="_blank">give us a 5 stars rating</a>.', admin_url( 'admin.php' ).'?page=voucher-setting', 'https://www.codemenschen.at/docs/wordpress-gift-vouchers-documentation/plugin-settings/payment-settings/', 'https://wordpress.org/support/plugin/gift-voucher/reviews/#new-post');
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		delete_transient( 'wpgv_updated' );
	}
}
add_action( 'admin_notices', 'wpgv_display_update_notice' );

function wpgv_display_install_notice() {
 	if( get_transient( 'wpgv_activated' ) ) {
  		$class = 'notice notice-info';
		$message = sprintf('Thanks for Installing <b>Gift Cards</b> plugin. Please setup your plugin settings from <a href="%s" target="_blank">here</a>.', admin_url( 'admin.php' ).'?page=voucher-setting');
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
		delete_transient( 'wpgv_activated' );
	}
}
add_action( 'admin_notices', 'wpgv_display_install_notice' );

function wpgv_plugin_deactivation() {
  	wp_clear_scheduled_hook('wpgv_check_voucher_status');
}
register_deactivation_hook(__FILE__, 'wpgv_plugin_deactivation');

add_action('init', 'wpgv_do_output_buffer');
function wpgv_do_output_buffer() {
    ob_start();
}

// Filter page template
add_filter('page_template', 'wpgv_catch_plugin_template');

// Page template filter callback
function wpgv_catch_plugin_template($template) {
    if( is_page_template('wpgv_voucher_pdf.php') ) {
        $template = WPGIFT__PLUGIN_DIR .'/templates/wpgv_voucher_pdf.php';
    } elseif( is_page_template('wpgv_item_pdf.php') ) {
        $template = WPGIFT__PLUGIN_DIR .'/templates/wpgv_item_pdf.php';
    }
    return $template;
}

function wpgv_hex2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

//conversion pixel -> millimeter at 72 dpi
function wpgv_px2mm($px){
    return $px*25.4/72;
}

function wpgv_txtentities($html) {
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

function wpgv_em($word) 
{
	if(get_locale() == "el"){
		$word = html_entity_decode(strip_tags(stripslashes($word)), ENT_NOQUOTES, 'UTF-8');
		$word = iconv('UTF-8', 'ISO-8859-7', $word);
		return $word;
	}else{
		$word = html_entity_decode(strip_tags(stripslashes($word)), ENT_NOQUOTES, 'UTF-8');
		$word = iconv('UTF-8', 'windows-1252', $word);
		return $word;
	}
	
}

function wpgv_mailvarstr_multiple($string, $setting_options, $voucher_options_results) {
	
	$get_link_pdf = array();
	$get_order_number = array();
	$from_name = null;
	$to_name = null;
	$email = null;
	$amount = null;
	foreach($voucher_options_results as $get_value){
		$get_link_pdf[] = get_home_url().'/wp-content/uploads/voucherpdfuploads/'.$get_value->voucherpdf_link.'.pdf';
		$get_order_number[] = $get_value->id;
		$from_name = $get_value->from_name;
		$to_name = $get_value->to_name;
		if($get_value->email){
			$email = $get_value->email;
		}else{
			$email = $get_value->shipping_email;
		}
		$amount = $get_value->amount;
	}
	
	
    $vars = array(
      '{order_type}'        => ($voucher_options_results->order_type) ? $voucher_options_results->order_type : 'vouchers',
      '{company_name}'      => ($setting_options->company_name) ? stripslashes($setting_options->company_name) : '',
      '{website_url}'       => get_site_url(),
      '{sender_email}'      => $setting_options->sender_email,
      '{sender_name}'       => stripslashes($setting_options->sender_name),
      '{order_number}'      => implode(', ', $get_order_number),
      '{amount}'            => $amount,
      '{customer_name}'     => stripslashes($from_name),
      '{recipient_name}'    => stripslashes($to_name),
      '{customer_email}'    => $email,
      '{customer_address}'  => $voucher_options_results->address,
      '{customer_postcode}' => $voucher_options_results->postcode,
      '{coupon_code}'       => $voucher_options_results->couponcode,
      '{payment_method}'    => $voucher_options_results->pay_method,
      '{payment_status}'    => $voucher_options_results->payment_status,
      '{pdf_link}'          => implode(', ', $get_link_pdf),
      '{receipt_link}'      => get_home_url().'/wp-content/uploads/voucherpdfuploads/'.$voucher_options_results->voucherpdf_link.'-receipt.pdf',
    );
    return strtr($string, $vars);
	
}
function wpgv_mailvarstr($string, $setting_options, $voucher_options) {
    $vars = array(
      '{order_type}'        => ($voucher_options->order_type) ? $voucher_options->order_type : 'vouchers',
      '{company_name}'      => ($setting_options->company_name) ? stripslashes($setting_options->company_name) : '',
      '{website_url}'       => get_site_url(),
      '{sender_email}'      => $setting_options->sender_email,
      '{sender_name}'       => stripslashes($setting_options->sender_name),
      '{order_number}'      => $voucher_options->id,
      '{amount}'            => $voucher_options->amount,
      '{customer_name}'     => stripslashes($voucher_options->from_name),
      '{recipient_name}'    => stripslashes($voucher_options->to_name),
      '{customer_email}'    => ($voucher_options->email) ? $voucher_options->email : $voucher_options->shipping_email,
      '{customer_address}'  => $voucher_options->address,
      '{customer_postcode}' => $voucher_options->postcode,
      '{coupon_code}'       => $voucher_options->couponcode,
      '{payment_method}'    => $voucher_options->pay_method,
      '{payment_status}'    => $voucher_options->payment_status,
      '{pdf_link}'          => get_home_url().'/wp-content/uploads/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'.pdf',
      '{receipt_link}'      => get_home_url().'/wp-content/uploads/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'-receipt.pdf',
    );

    return strtr($string, $vars);
}
// This function is use for Multisite WP environment
function wpgv_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
    if ( is_plugin_active_for_network( 'gift-voucher-pro/gift-voucher-pro.php' ) ) {
        switch_to_blog($blog_id);
        wpgv_plugin_activation();
        restore_current_blog();
    } 
}

add_action('wpmu_new_blog', 'wpgv_new_blog', 10, 6 );

add_action('wpgv_check_voucher_status', 'do_wpgv_check_voucher_status');

function do_wpgv_check_voucher_status() {
	global $wpdb;
	$setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
	$options = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );
	if($options->is_woocommerce_enable) {
		$vouchers = $wpdb->get_results( "SELECT id, couponcode FROM {$wpdb->prefix}giftvouchers_list WHERE `status` = 'unused' AND `payment_status` = 'Paid'");
		foreach($vouchers as $voucher) {
			$gift_voucher = new WPGV_Gift_Voucher( $voucher->couponcode );
			$balance = $gift_voucher->get_balance();
			if ( empty( $balance ) || ($balance == 0) ) {
				$wpdb->update(
				"{$wpdb->prefix}giftvouchers_list",
				array('id'=>$voucher->id, 'status'=>'used'),
				array('id'=>$voucher->id)
				);
			}
		}
	}
}
add_action( 'wp_ajax_wpgv_redeem_voucher', 'wpgv_redeem_voucher' );

function wpgv_redeem_voucher() {
	global $wpdb;
	$setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );

	$voucher_id = isset($_POST['voucher_id']) ? $_POST['voucher_id'] : '';
	$voucher_amount = isset($_POST['voucher_amount']) ? $_POST['voucher_amount'] : '';
	WPGV_Gift_Voucher_Activity::record( $voucher_id, 'transaction', '-'.$voucher_amount, 'Voucher amount '.$setting_options->currency.$voucher_amount.' used directly by administrator.' );

	echo 'Successfully redeemed voucher amount '.$setting_options->currency.$voucher_amount;
	wp_die(); // this is required to terminate immediately and return a proper response
}

function wpgv_price_format( $price ) { 
	global $wpdb;
	$setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );
	$price = html_entity_decode(strip_tags(stripslashes($price)), ENT_NOQUOTES, 'UTF-8');
	$price = iconv('UTF-8', 'windows-1252', $price);
	$price = number_format((float)$price, 2, '.', ',');
	$currency = ($setting_options->currency_position == 'Left') ? $setting_options->currency.' '.$price : $price.' '.$setting_options->currency;
	return $currency;
}
function wpgv_create_plugin_pages() {
  // Create Pages
	$voucherPage = array(
		'post_title'    => 'Gift Voucher',
		'post_content'  => '[wpgv_giftvoucher]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
    );
	$giftItemsPage = array(
		'post_title'    => 'Gift Items',
		'post_content'  => '[wpgv_giftitems]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
    );  
	$voucherPDFPage = array(
		'post_title'    => 'Voucher PDF Preview',
		'post_content'  => ' ',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
    );
	$giftItemPDFPage = array(
		'post_title'    => 'Gift Item PDF Preview',
		'post_content'  => ' ',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
    );
	$voucherSuccessPage = array(
		'post_title'    => 'Voucher Payment Successful',
		'post_content'  => '[wpgv_giftvouchersuccesspage]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	);
	$voucherCancelPage = array(
		'post_title'    => 'Voucher Payment Cancel',
		'post_content'  => '[wpgv_giftvouchercancelpage]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	);
	$voucherCheckBalance = array(
		'post_title'    => 'Voucher Check Balance',
		'post_content'  => '[wpgv-check-voucher-balance]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
    );
	$giftCardPage = array(
		'post_title'    => 'Gift Cards',
		'post_content'  => '[wpgv_giftcard]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
    );
	$lastpageIds[0] = wp_insert_post( $voucherPage, '' );
	$lastpageIds[1] = wp_insert_post( $giftItemsPage, '' );
	$lastpageIds[2] = wp_insert_post( $voucherPDFPage, '' );
	$lastpageIds[3] = wp_insert_post( $giftItemPDFPage, '' );
	$lastpageIds[4] = wp_insert_post( $voucherSuccessPage, '');
	$lastpageIds[5] = wp_insert_post( $voucherCancelPage, '');
	$lastpageIds[6] = wp_insert_post( $voucherCheckBalance, '');
	$lastpageIds[7] = wp_insert_post( $giftCardPage, '');

	$lastCategoryID = wp_insert_term(
		'Demo Category',
		'wpgv_voucher_category',
		array(
		'description' => 'Demo Category Description',
		'slug'        => 'demo-category',
		)
	);

	$demoItem = array(
		'post_title'    => 'Demo Item',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'wpgv_voucher_product',
	);
	$lastItemID = wp_insert_post( $demoItem );
	add_post_meta($lastItemID, 'description', 'Demo Description');
	add_post_meta($lastItemID, 'price', '100');
	add_post_meta($lastItemID, 'special_price', '80');
	wp_set_object_terms( $lastItemID, $lastCategoryID, 'wpgv_voucher_category' );

	if( !$lastpageIds[2] )
		wp_die('Error creating template page');
	else
		update_post_meta( $lastpageIds[2], '_wp_page_template', 'wpgv_voucher_pdf.php' );

	if( !$lastpageIds[3] )
		wp_die('Error creating template page');
	else
		update_post_meta( $lastpageIds[3], '_wp_page_template', 'wpgv_item_pdf.php' );

	return array($lastpageIds);
}

// Send gift voucher email event
add_action( "send_gift_voucher_email_event", "send_gift_voucher_email_event_fun", 1, 5 );
function send_gift_voucher_email_event_fun($recipientto, $recipientsub, $recipientmsg, $headers, $attachments) {
  	wp_mail( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
}

function wpgv_display_testmode_notice() {
	global $wpdb;
	$setting_table  = $wpdb->prefix . 'giftvouchers_setting';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	$wpgv_paypal_client_id = get_option('wpgv_paypal_client_id') ? get_option('wpgv_paypal_client_id') : '';
	$wpgv_paypal_secret_key = get_option('wpgv_paypal_secret_key') ? get_option('wpgv_paypal_secret_key') : '';  
	if( $setting_options->paypal && $setting_options->test_mode ) {
		$class = 'notice notice-info';
		$message = sprintf('PayPal Testmode has enabled in the <a href="%s" target="_blank">plugin settings</a> in <b>Gift Cards</b> plugin.', admin_url( 'admin.php' ).'?page=voucher-setting#payment');
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
	}
	if( $setting_options->paypal && (!$wpgv_paypal_client_id || !$wpgv_paypal_secret_key) ) {
		$class = 'notice notice-info';
		$message = sprintf('PayPal has enabled but empty client id or secret key in the <a href="%s" target="_blank">plugin settings</a> in <b>Gift Cards</b> plugin.', admin_url( 'admin.php' ).'?page=voucher-setting#payment');
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
	}

	if( $setting_options->stripe && (!$setting_options->stripe_publishable_key || !$setting_options->stripe_secret_key) ) {
		$class = 'notice notice-info';
		$message = sprintf('Stripe has enabled but empty Publishable key or Secret key in the <a href="%s" target="_blank">plugin settings</a> in <b>Gift Cards</b> plugin.', admin_url( 'admin.php' ).'?page=voucher-setting#payment');
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
	}
	if( $setting_options->sofort && (!$setting_options->sofort_configure_key) ) {
		$class = 'notice notice-info';
		$message = sprintf('Sofort has enabled but empty Configuration Key in the <a href="%s" target="_blank">plugin settings</a> in <b>Gift Cards</b> plugin.', admin_url( 'admin.php' ).'?page=voucher-setting#payment');
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
	}
}
add_action( 'admin_notices', 'wpgv_display_testmode_notice' );

function load_media_files() {
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_media_files' );

//columns Name Customer
function wpgv_customer_name_columns($columns)
{
	unset($columns['date']);
	unset($columns['title']);
	unset($columns['taxonomy-category_voucher_template']);
	$new_columns = array(
		'images' => __('', 'gift-voucher'),
		'title' => __('Title', 'gift-voucher'),
		'category' => __('Card Categories', 'gift-voucher'),
		'status' => __('Status', 'gift-voucher'),
		'date' => __('Date', 'gift-voucher'),

	);
	return array_merge($columns, $new_columns);
}
// Show content columns Phone customer
function wpgv_customer_columns_content($column, $post_ID)
{
	global $post;
	switch ($column) {

		case 'status':
			$values = get_post_custom($post->ID);
			$status = isset($values["wpgv_customize_template_status"]) ? $values["wpgv_customize_template_status"]['0'] : __('Inactive', 'gift-voucher');
			if ($status == 'Active') {
				echo '<p class="wpgv-status-active" style="font-weight: 500; "><span>' . $status . '</span></p>';
			} else {
				echo '<p class="wpgv-status-unactive" style="font-weight: 500; "><span>' . $status . '</span></p>';
			}

			break;
		case 'images':

				$values = get_post_custom($post->ID);
				$images_s3 = isset($values["wpgv_customize_template_template-style"]) ? $values["wpgv_customize_template_template-style"]['0'] : __('', 'gift-voucher');
				$images_media = isset($values["wpgv_customize_template_bg_result"]) ? wp_get_attachment_image_url($values["wpgv_customize_template_bg_result"]['0'], 'full') : __('', 'gift-voucher');
				$chosse_template = isset($values["wpgv_customize_template_chosse_template"]) ? $values["wpgv_customize_template_chosse_template"]['0'] : __('', 'gift-voucher');
				
				if($chosse_template != "0"){
					if(isset($values["wpgv_customize_template_template-style"])){
						if(isset($values["wpgv_customize_template_select_template"])){
							if($values["wpgv_customize_template_select_template"]["0"] == "custom"){
								echo '<img class="" src="'. $images_media . '" width="79px" height="auto" />';
							}else{
								if($values["wpgv_customize_template_template-style"]["0"] != "0"){
									echo '<img class="2" src="https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/png/' . $images_s3 . '" width="79px" height="auto" />';
								}
							}	
						}else{
							echo '<img class="2" src="https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/png/' . $images_s3 . '" width="79px" height="auto" />';
						}
					}
				}
				
				
				break;
		case 'category':
			$term_list = get_the_terms($post->ID, 'category_voucher_template');
			$types = '';
			if (!empty($term_list)) {
				foreach ($term_list as $term_single) {
					$term_link = get_term_link($term_single);
					$types .= '<a href="' . admin_url() . 'edit.php?post_type=voucher_template&category_voucher_template='.ucfirst($term_single->slug).'">' . ucfirst($term_single->slug) . ',' . '</a> ';
				}
			
			}
			$typesz = rtrim($types, ', ');
			echo $types;
			break;

		default:
			break;
	}
}

add_filter('manage_voucher_template_posts_columns', 'wpgv_customer_name_columns');
add_action('manage_voucher_template_posts_custom_column', 'wpgv_customer_columns_content', 10, 2);

// save image from canvas voucher_metabox.php
function wpgv_save_image()
{

	$base64_img = (isset($_POST['base64_img'])) ? esc_attr($_POST['base64_img']) : '';
	$title = (isset($_POST['title'])) ? esc_attr($_POST['title']) : '';
	$get_id_bg_result = (isset($_POST['get_id_bg_result'])) ? esc_attr($_POST['get_id_bg_result']) : '';
	$data_id_result = (isset($_POST['data_id_result'])) ? esc_attr($_POST['data_id_result']) : '';
	
	$upload_dir = wp_upload_dir();
	$upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;
	$image_parts = explode(";base64,", $base64_img);
	$decoded = base64_decode($image_parts[1]);
	$filename = $title . '.png';
	$hashed_filename = md5($filename . microtime()) . '_' . $filename;
	$image_upload = file_put_contents($upload_path . $hashed_filename, $decoded);
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');

	$file             = array();
	$file['error']    = '';
	$file['tmp_name'] = $upload_path . $hashed_filename;
	$file['name']     = $hashed_filename;
	$file['type']     = 'image/png';
	$file['size']     = filesize($upload_path . $hashed_filename);
	// upload file to server

	// use $file instead of $image_upload
	$file_return = wp_handle_sideload($file, array('test_form' => false));
	$filename = $file_return['file'];
	$attachment = array(
		'post_mime_type' => $file_return['type'],
		'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
		'post_content' => '',
		'post_status' => 'inherit',
		'guid' => $wp_upload_dir['url'] . '/' . basename($filename)
	);

	$attach_id = wp_insert_attachment($attachment, $filename);
	/// generate thumbnails of newly uploaded image
	wp_delete_attachment( $data_id_result, true );
	$attachment_meta = wp_generate_attachment_metadata($attach_id, $filename);
	wp_update_attachment_metadata($attach_id, $attachment_meta);
	set_post_thumbnail($post_id, $attach_id);
	
	echo $attach_id;
	
	die(); 
}
add_action('wp_ajax_wpgv_save_image', 'wpgv_save_image');
add_action('wp_ajax_nopriv_wpgv_save_image', 'wpgv_save_image');
  
// save image from canvas

