<?php

// namespace Sofort\SofortLib;

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

function wpgv__doajax_voucher_pdf_save_func() {
	
	$template = sanitize_text_field(base64_decode($_POST['template']));
	$buyingfor = sanitize_text_field(base64_decode($_POST['buying_for']));
	$for = isset($_POST['for']) ? sanitize_text_field(base64_decode($_POST['for'])) : '';
	$from = isset($_POST['from']) ? sanitize_text_field(base64_decode($_POST['from'])) : '';
	$value = sanitize_text_field(base64_decode($_POST['value']));
	$message = isset($_POST['message']) ? sanitize_text_field(base64_decode($_POST['message'])) : '';
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
	$paymentmethod = sanitize_text_field(base64_decode($_POST['paymentmethod']));
	$send_email_date_time = isset($_POST['send_email_date_time']) ? base64_decode($_POST['send_email_date_time']) : 'send_instantly';
	$paytext = '';
	if ($paymentmethod == 'Per Invoice') {
		$paytext = __('Per Invoice', 'gift-voucher');
	}else{
		$paytext = $paymentmethod;
	}
	$seller_company_name = isset($_POST['seller_company_name']) ? base64_decode($_POST['seller_company_name']) : '';
	$seller_addr_line1 = isset($_POST['seller_addr_line1']) ? base64_decode($_POST['seller_addr_line1']) : '';
	$seller_addr_line2 = isset($_POST['seller_addr_line2']) ? base64_decode($_POST['seller_addr_line2']) : '';
	$seller_addr_line3 = isset($_POST['seller_addr_line3']) ? base64_decode($_POST['seller_addr_line3']) : '';
	$seller_addr_line4 = isset($_POST['seller_addr_line4']) ? base64_decode($_POST['seller_addr_line4']) : '';

	global $wpdb;
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$template_table = $wpdb->prefix . 'giftvouchers_template';
	$invoice_setting_table = $wpdb->prefix . 'giftvouchers_invoice_settings';

	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template" );
	$invoice_options = $wpdb->get_row( "SELECT * FROM $invoice_setting_table WHERE id = 1" );

	$images = $template_options->image_style ? json_decode($template_options->image_style) : ['','',''];
	$voucher_bgcolor = wpgv_hex2rgb($setting_options->voucher_bgcolor);
	$voucher_color = wpgv_hex2rgb($setting_options->voucher_color);
	$invoice_color = wpgv_hex2rgb($invoice_options->invoice_color);
	$currency = wpgv_price_format($value);

	$wpgv_hide_expiry = get_option('wpgv_hide_expiry') ? get_option('wpgv_hide_expiry') : 'yes';
	$wpgv_customer_receipt = get_option('wpgv_customer_receipt') ? get_option('wpgv_customer_receipt') : 0;
	$wpgv_expiry_date_format = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';
	$wpgv_enable_pdf_saving = get_option('wpgv_enable_pdf_saving') ? get_option('wpgv_enable_pdf_saving') : 0;
	$wpgv_add_extra_charges = get_option('wpgv_add_extra_charges_voucher') ? get_option('wpgv_add_extra_charges_voucher') : 0;

	if($wpgv_hide_expiry == 'no') {
    	$expiry = __('No Expiry', 'gift-voucher' );
	} else {
		$expiry = ($setting_options->voucher_expiry_type == 'days') ? date($wpgv_expiry_date_format,strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;
	}

	if($value == 0) {
		echo '<div class="wpgvmodaloverlay"><div class="wpgvmodalcontent">'.__('Error Occurred - Please check the amount and try again.', 'gift-voucher').'<br><a href="">'.__('Load Page Again', 'gift-voucher').'</a></div></div>';
		wp_die();
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
		$image = ($image_attributes) ? $image_attributes : get_option('wpgv_demoimageurl_voucher');
        $stripeimage = (wp_get_attachment_image_src($images[$voucher_style])) ? wp_get_attachment_image_src($images[$voucher_style]) : get_option('wpgv_demoimageurl_voucher');
	} else {
		$voucher_style = 0;
		$image_attributes = get_attached_file( $images[0] );
		$image = ($image_attributes) ? $image_attributes : get_option('wpgv_demoimageurl_voucher');
        $stripeimage = (wp_get_attachment_image_src($images[0])) ? wp_get_attachment_image_src($images[0]) : get_option('wpgv_demoimageurl_voucher');
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
			'pay_method'		=> $paymentmethod,
			'expiry'			=> $expiry,
			'couponcode'		=> $code,
			'voucherpdf_link'	=> $upload_url,
			'status'			=> 'unused',
			'payment_status'	=> __('Not Paid', 'gift-voucher' ),
			'voucheradd_time'	=> current_time( 'mysql' ),
			'email_send_date_time' => $send_email_date_time
		)
	);

	$lastid = $wpdb->insert_id;
	$voucher_order_by = __("Voucher ordered by test 2", "gift-voucher");
	$message_e = __("Message:", "gift-voucher");
	WPGV_Gift_Voucher_Activity::record( $lastid, 'create', '', $voucher_order_by . " " . $for . ',' . $message_e . " " . $message );

	$shipping_charges = 0;

	if($shipping != 'shipping_as_email') {
	    $preshipping_methods = explode(',', $setting_options->shipping_method);
    	foreach ($preshipping_methods as $method) {
        	$preshipping_method = explode(':', $method);
        	if(trim(stripslashes($preshipping_method[1])) == trim(stripslashes($shipping_method))) {
	        	$value += trim($preshipping_method[0]);
	        	$shipping_charges = trim($preshipping_method[0]);
    	    	break;
        	}
        }
    }

    $value += $wpgv_add_extra_charges;

    if($invoice_options->is_invoice_active == 1){
		$upload_dir = $upload['basedir'];
		$invoiceupload_dir = $upload_dir . '/voucherpdfuploads/'.$curr_time.$_POST['code'].'-invoice.pdf';
		require_once( WPGIFT__PLUGIN_DIR .'/templates/pdfstyles/invoice-1.php');
		if($wpgv_enable_pdf_saving) {
			$invoice->Output($invoiceupload_dir,'F');
		} else {
			$invoice->Output('F',$invoiceupload_dir);
		}
	}

	//Customer Receipt
	if($wpgv_customer_receipt) {
		$upload_dir = $upload['basedir'];
		$receiptupload_dir = $upload_dir . '/voucherpdfuploads/'.$curr_time.$_POST['code'].'-receipt.pdf';
		require_once( WPGIFT__PLUGIN_DIR .'/templates/pdfstyles/receipt.php');
		if($wpgv_enable_pdf_saving) {
			$receipt->Output($receiptupload_dir,'F');
		} else {
			$receipt->Output('F',$receiptupload_dir);
		}
	}

	$currency = wpgv_price_format($value);
	update_post_meta($lastid, 'wpgv_extra_charges', $wpgv_add_extra_charges);
	update_post_meta($lastid, 'wpgv_total_payable_amount', $currency);

	$success_url = get_site_url() .'/voucher-payment-successful/?voucheritem='.$lastid;
	$cancel_url = get_site_url() .'/voucher-payment-cancel/?voucheritem='.$lastid;
	$notify_url = get_site_url() .'/voucher-payment-successful/?voucheritem='.$lastid;
	
	if ($paymentmethod == 'Paypal') {
		require_once( WPGIFT__PLUGIN_DIR .'/include/PayPalAuth.php');

		$payer = new Payer();
		$payer->setPaymentMethod("paypal");
		
		$item = new Item();
		$item->setName($template_options->title)
    		->setCurrency($setting_options->currency_code)
    		->setQuantity(1)
    		->setPrice($value);

    	$itemList = new ItemList();
		$itemList->setItems(array($item));
		
		$details = new Details();
		$details->setSubtotal($value);

		$amount = new Amount();
		$amount->setCurrency($setting_options->currency_code)
    		->setTotal($value)
    		->setDetails($details);

    	$transaction = new Transaction();
		$transaction->setAmount($amount)
    		->setItemList($itemList)
    		->setDescription($message)
    		->setInvoiceNumber(uniqid());

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($success_url)
    		->setCancelUrl($cancel_url);

    	$payment = new Payment();
		$payment->setIntent("order")
    		->setPayer($payer)
    		->setRedirectUrls($redirectUrls)
    		->setTransactions(array($transaction));

	    $request = clone $payment;
		
    	try {
		    $payment->create($apiContext);
		} catch (Exception $ex) {
    		exit(1);
		}
	
		$approvalUrl = $payment->getApprovalLink();
		// ResultPrinter::printResult("Created Payment Order Using PayPal. Please visit the URL to Approve.", "Payment", "<a href='$approvalUrl' >$approvalUrl</a>", $request, $payment);
		
		echo $approvalUrl;
		
	} elseif($paymentmethod == 'Sofort') {

		$Sofortueberweisung = new Sofortueberweisung($setting_options->sofort_configure_key);

		$Sofortueberweisung->setAmount($value);
		$Sofortueberweisung->setCurrencyCode($setting_options->currency_code);

		$Sofortueberweisung->setReason($setting_options->reason_for_payment, $lastid);
		$Sofortueberweisung->setSuccessUrl($success_url, true);
		$Sofortueberweisung->setAbortUrl($cancel_url);
		// $Sofortueberweisung->setNotificationUrl($notify_url);

		$Sofortueberweisung->sendRequest();

		if($Sofortueberweisung->isError()) {
			//SOFORT-API didn't accept the data
			echo $Sofortueberweisung->getError();
		} else {
			//buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
			$paymentUrl = $Sofortueberweisung->getPaymentUrl();
			echo $paymentUrl;
		}
	} elseif ($paymentmethod == 'Stripe') {

		$stripesuccesspageurl = get_option('wpgv_stripesuccesspage');

    	//set api key
    	$stripe = array(
      		"publishable_key" => $setting_options->stripe_publishable_key,
      		"secret_key"      => $setting_options->stripe_secret_key,
    	);
        
        $camount = ($value)*100;
        $stripeemail = ($email) ? $email : $shipping_email;

    	\Stripe\Stripe::setApiKey($stripe['secret_key']);

    	$is_stripe_ideal_enable = get_option('wpgv_stripe_ideal');

    	if($is_stripe_ideal_enable == 1){
    		$session = \Stripe\Checkout\Session::create([
	  			'payment_method_types' => ['card', 'ideal'],
	  			'line_items' => [[
	    			'name' => $template_options->title,
	    			'images' => [$stripeimage],
	    			'amount' => $camount,
	    			'currency' => $setting_options->currency_code,
	    			'quantity' => 1,
	  			]],
	  			'success_url' => get_page_link($stripesuccesspageurl) . '/?voucheritem='.$lastid.'&sessionid={CHECKOUT_SESSION_ID}',
	  			'cancel_url' => $cancel_url,
			]);
    	}
    	else{
    		$session = \Stripe\Checkout\Session::create([
	  			'payment_method_types' => ['card'],
	  			'line_items' => [[
	    			'name' => $template_options->title,
	    			'images' => [$stripeimage],
	    			'amount' => $camount,
	    			'currency' => $setting_options->currency_code,
	    			'quantity' => 1,
	  			]],
	  			'success_url' => get_page_link($stripesuccesspageurl) . '/?voucheritem='.$lastid.'&sessionid={CHECKOUT_SESSION_ID}',
	  			'cancel_url' => $cancel_url,
			]);
    	}    	

		$stripesuccesspageurl = get_option('wpgv_stripesuccesspage');
		$stripeemail = ($email) ? $email : $shipping_email;
		echo '<script type="text/javascript">
    			var stripe = Stripe("'.$stripe['publishable_key'].'");
    			stripe.redirectToCheckout({
			    	sessionId: "'.$session["id"].'"
    			}).then(function (result) {
    				console.log(result.error.message);
    			});
  			</script>';
	} elseif ($paymentmethod == 'MultiSafepay') {
		$wpgv_multisafepay_test_mode = get_option('wpgv_multisafepay_test_mode') ? get_option('wpgv_multisafepay_test_mode') : '';
		$wpgv_multisafepay_api_id = get_option('wpgv_multisafepay_api_id') ? get_option('wpgv_multisafepay_api_id') : '0ff28d5cc3a6e7475be5fa174703788fa155fc94';
		$msp_api_url = $wpgv_multisafepay_test_mode ? 'https://testapi.multisafepay.com/v1/json/' : 'https://api.multisafepay.com/v1/json/';
		$msp = new \MultiSafepayAPI\Client;
    	$msp->setApiKey($wpgv_multisafepay_api_id);
    	$msp->setApiUrl($msp_api_url);
        $camount = ($value)*100;
    	try {
		    $order_id = $lastid;

      		$order = $msp->orders->post(array(
          		"type" => "redirect",
          		"order_id" => $order_id,
          		"currency" => $setting_options->currency_code,
          		"amount" => $camount,
          		"description" => $template_options->title,
          		"payment_options" => array(
              		"notification_url" => $notify_url,
              		"redirect_url" => $success_url,
              		"cancel_url" => $cancel_url,
              		"close_window" => "true"
          		),
          		"customer" => array(
              		"ip_address" => $_SERVER['REMOTE_ADDR'],
              		"forwarded_ip" => $_SERVER['REMOTE_ADDR'],
              		"first_name" => $from,
              		"email" => $email,
          		),
      		));

		    echo $msp->orders->getPaymentLink();
    	} catch (Exception $e) {
      		echo "Error " . htmlspecialchars($e->getMessage());
    	}
	} elseif($paymentmethod == 'Per Invoice') {
		echo $success_url.'&per_invoice=1';
	}

	wp_die();
}
add_action('wp_ajax_nopriv_wpgv_doajax_voucher_pdf_save_func', 'wpgv__doajax_voucher_pdf_save_func');
add_action('wp_ajax_wpgv_doajax_voucher_pdf_save_func', 'wpgv__doajax_voucher_pdf_save_func');