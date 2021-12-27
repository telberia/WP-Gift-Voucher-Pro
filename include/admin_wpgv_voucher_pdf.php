<?php

// namespace Sofort\SofortLib;

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

function admin_wpgv_doajax_voucher_pdf_save_func() {
	$template = sanitize_text_field(base64_decode($_POST['template']));
	$buyingfor = sanitize_text_field(base64_decode($_POST['buying_for']));
	$for = sanitize_text_field(base64_decode($_POST['for']));
	$from = isset($_POST['from']) ? sanitize_text_field(base64_decode($_POST['from'])) : '';
	$value = sanitize_text_field(base64_decode($_POST['value']));
	$message = sanitize_textarea_field(base64_decode($_POST['message']));
	$expiry = base64_decode($_POST['expiry']);
	$code = sanitize_text_field(base64_decode($_POST['code']));
	$shipping = sanitize_text_field(base64_decode($_POST['shipping']));
	$shipping_email = isset($_POST['shipping_email']) ? sanitize_email(base64_decode($_POST['shipping_email'])) : '';
	$firstname = isset($_POST['firstname']) ? sanitize_text_field(base64_decode($_POST['firstname'])) : '';
	$lastname = isset($_POST['lastname']) ? sanitize_text_field(base64_decode($_POST['lastname'])) : '';
	$email = isset($_POST['email']) ? sanitize_email(base64_decode($_POST['email'])) : '';
	$address = isset($_POST['address']) ? sanitize_text_field(base64_decode($_POST['address'])) : '';
	$pincode = isset($_POST['pincode']) ? sanitize_text_field(base64_decode($_POST['pincode'])) : '';
	$shipping_method = isset($_POST['shipping_method']) ? base64_decode($_POST['shipping_method']) : '';
	
	global $wpdb;
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$template_table = $wpdb->prefix . 'giftvouchers_template';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template" );
	$images = $template_options->image_style ? json_decode($template_options->image_style) : ['','',''];
	$voucher_bgcolor = wpgv_hex2rgb($setting_options->voucher_bgcolor);
	$voucher_color = wpgv_hex2rgb($setting_options->voucher_color);
	$currency = wpgv_price_format($value);

	$wpgv_hide_expiry = get_option('wpgv_hide_expiry') ? get_option('wpgv_hide_expiry') : 'yes';
	$wpgv_customer_receipt = get_option('wpgv_customer_receipt') ? get_option('wpgv_customer_receipt') : 0;
	$wpgv_expiry_date_format = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';
	$wpgv_enable_pdf_saving = get_option('wpgv_enable_pdf_saving') ? get_option('wpgv_enable_pdf_saving') : 0;
	$wpgv_add_extra_charges = get_option('wpgv_add_extra_charges') ? get_option('wpgv_add_extra_charges') : 0;

	if($wpgv_hide_expiry == 'no') {
    	$expiry = __('No Expiry', 'gift-voucher' );
	} else {
		$expiry = ($setting_options->voucher_expiry_type == 'days') ? date($wpgv_expiry_date_format,strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;
	}

	$upload = wp_upload_dir();
 	$upload_dir = $upload['basedir'];
 	$curr_time = time();
 	$upload_dir = $upload_dir . '/voucherpdfuploads/'.$curr_time.$_POST['code'].'.pdf';
 	$upload_url = $curr_time.$_POST['code'];

	$formtype = 'voucher';
	$preview = false;

	if ($setting_options->is_style_choose_enable) {
		$voucher_style = sanitize_text_field(base64_decode($_POST['style']));
		$image_attributes = get_attached_file( $images[$voucher_style] );
		$image = ($image_attributes) ? $image_attributes : get_option('wpgv_demoimageurl');
        $stripeimage = (wp_get_attachment_image_src($images[$voucher_style])) ? wp_get_attachment_image_src($images[$voucher_style]) : get_option('wpgv_demoimageurl');
	} else {
		$voucher_style = 0;
		$image_attributes = get_attached_file( $images[0] );
		$image = ($image_attributes) ? $image_attributes : get_option('wpgv_demoimageurl');
        $stripeimage = (wp_get_attachment_image_src($images[0])) ? wp_get_attachment_image_src($images[0]) : get_option('wpgv_demoimageurl');
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

	if($wpgv_enable_pdf_saving) {
		$pdf->Output($upload_dir,'F');
	} else {
		$pdf->Output('F',$upload_dir);
	}

	$wpdb->insert(
		$voucher_table,
		array(
			'order_type'		=> 'vouchers',
			'template_id' 		=> $template,
			'buying_for'		=> $buyingfor,
			'from_name' 		=> $for,
			'to_name' 			=> $from,
			'amount'			=> $value,
			'message'			=> $message,
			'shipping_type'		=> $shipping,
			'shipping_email'	=> $shipping_email,
			'firstname'			=> $firstname,
			'lastname'			=> $lastname,
			'email'				=> $email,
			'address'			=> $address,
			'postcode'			=> $pincode,
			'shipping_method'	=> $shipping_method,
			'pay_method'		=> "admin",
			'expiry'			=> $expiry,
			'couponcode'		=> $code,
			'voucherpdf_link'	=> $upload_url,
			'payment_status'	=> 'Paid',
			'status'			=> 'unused',
			'voucheradd_time'	=> current_time( 'mysql' )
		)
	);

	$lastid = $wpdb->insert_id;
	$voucher_order_by = __("Voucher ordered by", "gift-voucher");
	$message_e = __("Message:", "gift-voucher");
	WPGV_Gift_Voucher_Activity::record( $lastid, 'create', '', $voucher_order_by . " " . $for . ',' . $message_e . " " . $message );
	
	WPGV_Gift_Voucher_Activity::record( $lastid, 'firsttransact', $value, 'Voucher payment added from admin.' );

	if($shipping != 'shipping_as_email') {
	    $preshipping_methods = explode(',', $setting_options->shipping_method);
    	foreach ($preshipping_methods as $method) {
        	$preshipping_method = explode(':', $method);
        	if(trim(stripslashes($preshipping_method[1])) == trim(stripslashes($shipping_method))) {
	        	$value += trim($preshipping_method[0]);
    	    	break;
        	}
        }
    }
    $value += $wpgv_add_extra_charges;

	$currency = wpgv_price_format($value);
	update_post_meta($lastid, 'wpgv_extra_charges', $wpgv_add_extra_charges);
	update_post_meta($lastid, 'wpgv_total_payable_amount', $currency);

	if($lastid > 0)
	{
		$url = admin_url("admin.php")."?page=add-gift-voucher-order&msg=1";
		echo json_encode( array( "status" => 1, "url" => $url) );
	}
	else
	{	$url = admin_url("admin.php")."?page=add-gift-voucher-order&msg=0";
		echo json_encode( array( "status" => 0, "url" => $url) );
	}

	wp_die();
}
add_action('wp_ajax_nopriv_admin_wpgv_doajax_voucher_pdf_save_func', 'admin_wpgv_doajax_voucher_pdf_save_func');
add_action('wp_ajax_admin_wpgv_doajax_voucher_pdf_save_func', 'admin_wpgv_doajax_voucher_pdf_save_func');