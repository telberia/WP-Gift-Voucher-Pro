<?php
/*
 * Template Name: PDF Viewer Page Template
 * Description: A Page Template for pdf viewer.
 */

if(!isset($_GET['action'])) {
	exit();
}

$watermark = __('This is a preview voucher.', 'gift-voucher');
if(sanitize_text_field($_GET['action']) == 'preview') {
	$watermark = __('This is a preview voucher.', 'gift-voucher');
} else {
	exit();
}

$template = sanitize_text_field(base64_decode($_GET['template']));
$buyingfor = sanitize_text_field(base64_decode($_GET['buying_for']));
$for = sanitize_text_field(base64_decode($_GET['for']));
$from = sanitize_text_field(base64_decode($_GET['from']));
$value = sanitize_text_field(base64_decode($_GET['value']));
$message = sanitize_textarea_field(base64_decode($_GET['message']));
$expiry = base64_decode($_GET['expiry']);
$code = '################';

global $wpdb;
$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
$template_table = $wpdb->prefix . 'giftvouchers_template';
$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template" );
$images = $template_options->image_style ? json_decode($template_options->image_style) : ['','',''];

$voucher_bgcolor = wpgv_hex2rgb($setting_options->voucher_bgcolor);
$voucher_color = wpgv_hex2rgb($setting_options->voucher_color);
$currency = ($setting_options->currency_position == 'Left') ? $setting_options->currency.' '.$value : $value.' '.$setting_options->currency;

$formtype = 'voucher';
$preview = true;

if ($setting_options->is_style_choose_enable) {
	$voucher_style = sanitize_text_field(base64_decode($_GET['style']));
	$image_attributes = get_attached_file( $images[$voucher_style] );
	$image = ($image_attributes) ? $image_attributes : get_option('wpgv_demoimageurl_voucher');
} else {
	$voucher_style = 0;
	$image_attributes = get_attached_file( $images[0] );
	$image = ($image_attributes) ? $image_attributes : get_option('wpgv_demoimageurl_voucher');
}

switch ($voucher_style) {
	case 0:
		require_once( WPGIFT__PLUGIN_DIR .'/templates/pdfstyles/style1.php');
        break;
	case 1:
    	require_once( WPGIFT__PLUGIN_DIR .'/templates/pdfstyles/style2.php');
        break;
	case 2:
    	require_once( WPGIFT__PLUGIN_DIR .'/templates/pdfstyles/style3.php');
        break;
	default:
    	require_once( WPGIFT__PLUGIN_DIR .'/templates/pdfstyles/style1.php');
        break;
}
ob_clean();
$pdf->Output();