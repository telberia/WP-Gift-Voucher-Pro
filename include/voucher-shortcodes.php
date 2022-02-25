<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// Function for Voucher Payment Successful Shortcode
function wpgv_voucher_successful_shortcode() {
	global $wpdb;
	$return = '';

	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	if (isset($_GET['voucheritem'])) {
		$voucheritem = sanitize_text_field($_GET['voucheritem']);
		$voucher_options = $wpdb->get_row( "SELECT * FROM $voucher_table WHERE id = $voucheritem" );
		if((strtotime($voucher_options->voucheradd_time)+3600) < strtotime(current_time('mysql')) ) {
			return '<div class="error"><p>'.__('This URL is invalid. You can not access this page directly.', 'gift-voucher').'</p></div>';
		}
		if(isset($_GET['per_invoice']) && $_GET['per_invoice'] == 1) { 
		} else {
			$voucherrow = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}giftvouchers_list` WHERE `id` = $voucheritem AND `pay_method` <> 'Per Invoice'" );
			
			if($voucherrow) {
				$wpdb->update(
					$voucher_table,
					array( 
						'payment_status' 	=> __('Paid'),
						'voucheradd_time'	=> current_time( 'mysql' )
					),
					array('id' => $voucheritem),
					array( 
						'%s'
					), 
					array( '%d' )
				);
				if(isset($_GET['paymentId']) && $voucherrow->payment_status != 'Paid') {
					require_once( WPGIFT__PLUGIN_DIR .'/vendor/autoload.php');
					require_once( WPGIFT__PLUGIN_DIR .'/include/PayPalAuth.php');
					session_start();
					$client = PayPalAuth::client();
					// Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
					// $response->result->id gives the orderId of the order created above
					$request = new OrdersCaptureRequest($_SESSION["paypal_order_id"]);
					$request->prefer('return=representation');
					$result_getId = null;
					try {
						// Call API with your client and get a response for your call
						
						$response = $client->execute($request);
						
						$result_getId = $response->result->id;

					}catch (HttpException $ex) {
						
						echo $ex->statusCode;
						print_r($ex->getMessage());
					}
					
					update_post_meta( $voucheritem, 'wpgv_paypal_payment_key', $result_getId, true );
					update_post_meta( $voucheritem, 'wpgv_paypal_mode_for_transaction', (!$setting_options->test_mode) ? 'Livemode' : 'Testmode', true );
				}
				WPGV_Gift_Voucher_Activity::record( $voucheritem, 'firsttransact', $voucherrow->amount, 'Voucher payment recieved.' );
			}
		}
		$wpgv_invoice_mail_enable = (get_option('wpgv_invoice_mail_enable') != '') ? get_option('wpgv_invoice_mail_enable') : 1;
		if(isset($_GET['per_invoice']) && $_GET['per_invoice'] == 1 && $wpgv_invoice_mail_enable == 0){
			// Mail not send 

			$upload = wp_upload_dir();
	 		$upload_dir = $upload['basedir'];
			$attachments[0] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'.pdf';
			$attachments[1] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'-receipt.pdf';

			$adminemailsubject = get_option('wpgv_adminemailsubject') ? get_option('wpgv_adminemailsubject') : 'New Voucher Order Received from {customer_name}  (Order No: {order_number})!';
			$adminemailbody = get_option('wpgv_adminemailbody') ? get_option('wpgv_adminemailbody') : '<p>Hello, New Voucher Order received.</p><p><strong>Order Id:</strong> {order_number}</p><p><strong>Name:</strong> {customer_name}<br /><strong>Email:</strong> {customer_email}<br /><strong>Address:</strong> {customer_address}<br /><strong>Postcode:</strong> {customer_postcode}</p>';

			$toadmin = stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>';
			$subadmin = wpgv_mailvarstr($adminemailsubject, $setting_options, $voucher_options);
			$bodyadmin = wpgv_mailvarstr($adminemailbody, $setting_options, $voucher_options);
			$headersadmin = 'Content-type: text/html;charset=utf-8' . "\r\n";
			$headersadmin .= 'From: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";
			$headersadmin .= 'Reply-to: '.stripslashes($voucher_options->from_name).' <'.$voucher_options->email.'>' . "\r\n";
			wp_mail( $toadmin, $subadmin, $bodyadmin, $headersadmin, $attachments );

			$successpagemessage = get_option('wpgv_successpagemessage') ? get_option('wpgv_successpagemessage') : 'We have got your order! <br>Please complete payment process and contact us for further details';
			$return .= '<div class="success">'.sprintf(stripslashes($successpagemessage), $voucher_options->email).'</div>';

			if($setting_options->bank_info != ''){
				$return .= $setting_options->bank_info;
			}
		}
		else{
			$emailsubject = get_option('wpgv_emailsubject') ? get_option('wpgv_emailsubject') : 'Order Confirmation - Your Order with {company_name} (Voucher Order No: {order_number} ) has been successfully placed!';
			$recipientemailsubject = get_option('wpgv_recipientemailsubject') ? get_option('wpgv_recipientemailsubject') : 'Gift Voucher - Your have received voucher from {company_name}';
			$recipientemailbody = get_option('wpgv_recipientemailbody') ? get_option('wpgv_recipientemailbody') : '<p>Dear <strong>{recipient_name}</strong>,</p><p>You have received gift voucher from <strong>{customer_name}</strong>.</p><p>You can download the voucher from {pdf_link}.</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
			if(isset($_GET['per_invoice']) && $_GET['per_invoice'] == 1) {
				$emailbody = get_option('wpgv_emailbodyperinvoice') ? get_option('wpgv_emailbodyperinvoice') : '<p>Dear <strong>{customer_name}</strong>,</p><p>Order successfully placed.</p><p>We are pleased to confirm your order no {order_number}</p><p>Thank you for shopping with <strong>{company_name}</strong>!</p><p>You can download the voucher from {pdf_link}.</p><p>You will pay us directly into bank. Our bank details are below:</p><p><strong>Account Number: </strong>XXXXXXXXXXXX<br /><strong>Bank Code: </strong>XXXXXXXX</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
			} else {
				$emailbody = get_option('wpgv_emailbody') ? get_option('wpgv_emailbody') : '<p>Dear <strong>{customer_name}</strong>,</p><p>Order successfully placed.</p><p>We are pleased to confirm your order no {order_number}</p><p>Thank you for shopping with <strong>{company_name}</strong>!</p><p>You can download the voucher from {pdf_link}.</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
			}

			$adminemailsubject = get_option('wpgv_adminemailsubject') ? get_option('wpgv_adminemailsubject') : 'New Voucher Order Received from {customer_name}  (Order No: {order_number})!';
			$adminemailbody = get_option('wpgv_adminemailbody') ? get_option('wpgv_adminemailbody') : '<p>Hello, New Voucher Order received.</p><p><strong>Order Id:</strong> {order_number}</p><p><strong>Name:</strong> {customer_name}<br /><strong>Email:</strong> {customer_email}<br /><strong>Address:</strong> {customer_address}<br /><strong>Postcode:</strong> {customer_postcode}</p>';

			$upload = wp_upload_dir();
	 		$upload_dir = $upload['basedir'];
			$attachments[0] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'.pdf';
			$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
			$headers .= 'From: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";
			$headers .= 'Reply-to: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";
			
			/* Recipient Mail */
			if($voucher_options->shipping_type != 'shipping_as_post') {
				$recipientsub = wpgv_mailvarstr($recipientemailsubject, $setting_options, $voucher_options);
				$recipientmsg = wpgv_mailvarstr($recipientemailbody, $setting_options, $voucher_options);
				$recipientto = stripslashes($voucher_options->from_name) .'<'.$voucher_options->shipping_email.'>';
				if($voucher_options->buying_for == 'yourself') {
					$recipientto = stripslashes($voucher_options->from_name) .'<'.$voucher_options->email.'>';
				}
				
				if($voucher_options->email_send_date_time == 'send_instantly')
				{
					wp_mail( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
				}
				else
				{

					$save_zone = date_default_timezone_get();
					
					if(get_option('timezone_string') != "")
					{
						date_default_timezone_set(get_option('timezone_string'));
						$send_gift_voucher_email_event_date_time = strtotime($voucher_options->email_send_date_time);
					}
					else
					{
						date_default_timezone_set($save_zone);
						$send_gift_voucher_email_event_date_time = strtotime($voucher_options->email_send_date_time);
					}
			
					date_default_timezone_set($save_zone);
			
					$send_gift_voucher_email_event_args = array ( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
					wp_schedule_single_event( $send_gift_voucher_email_event_date_time, 'send_gift_voucher_email_event', $send_gift_voucher_email_event_args );
				}
			}
			$attachments[1] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'-receipt.pdf';
			if (!isset($_GET['per_invoice'])) {
				$attachments[2] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'-invoice.pdf';		
			}

			/* Buyer Mail */
			$buyersub = wpgv_mailvarstr($emailsubject, $setting_options, $voucher_options);
			$buyermsg = wpgv_mailvarstr($emailbody, $setting_options, $voucher_options);
			$buyerto = $voucher_options->email;
			$mail_sent = wp_mail( $buyerto, $buyersub, $buyermsg, $headers, $attachments );
			if($mail_sent == true) {
				$successpagemessage = get_option('wpgv_successpagemessage') ? get_option('wpgv_successpagemessage') : 'We have got your order! <br>E-Mail Sent Successfully to %s';
				$return .= '<div class="success">'.sprintf(stripslashes($successpagemessage), $voucher_options->email).'</div>';

				if(isset($_GET['per_invoice']) && $_GET['per_invoice'] == 1) {
					$return .= $setting_options->bank_info;
				}

				$toadmin = stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>';
				$subadmin = wpgv_mailvarstr($adminemailsubject, $setting_options, $voucher_options);
				$bodyadmin = wpgv_mailvarstr($adminemailbody, $setting_options, $voucher_options);
				$headersadmin = 'Content-type: text/html;charset=utf-8' . "\r\n";
				$headersadmin .= 'From: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";
				$headersadmin .= 'Reply-to: '.stripslashes($voucher_options->from_name).' <'.$voucher_options->email.'>' . "\r\n";

				wp_mail( $toadmin, $subadmin, $bodyadmin, $headersadmin, $attachments );
			}
			else {
				$return .= '<div class="error"><p>'.__('Some Error Occurred From Sending this Email! <br>(Reload and Retry Again!) or Contact Us', 'gift-voucher').'</p></div>';
			}
		}
		
	} else {
		return '<div class="error"><p>'.__('This URL is invalid. You can not access this page directly.', 'gift-voucher').'</p></div>';
	}
	return $return;
}
add_shortcode( 'wpgv_giftvouchersuccesspage', 'wpgv_voucher_successful_shortcode' );

// Function for Voucher Payment Cancel Shortcode
function wpgv_voucher_cancel_shortcode() {
	global $wpdb;
	$return = '';
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	if (isset($_GET['voucheritem'])) {
		$cancelpagemessage = get_option('wpgv_cancelpagemessage') ? get_option('wpgv_cancelpagemessage') : 'You cancelled your order. Please place your order again from <a href="'.get_site_url().'/gift-voucher">here</a>.';
		$voucheritem = sanitize_text_field($_GET['voucheritem']);
		$wpdb->delete( $voucher_table, array( 'id' => $voucheritem ), array( '%d' ) );
		$return .= stripslashes($cancelpagemessage);
	}
	return $return;
}
add_shortcode( 'wpgv_giftvouchercancelpage', 'wpgv_voucher_cancel_shortcode' );

//Function for Stripe Success Page
function wpgv_stripe_success_page_shortcode() {
	global $wpdb;
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );

	//check whether stripe token is not empty
	if(!empty($_GET['sessionid'])) {
    	$orderid = $_GET['voucheritem'];

    	$voucher_options = $wpdb->get_row( "SELECT * FROM $voucher_table WHERE id = $orderid" );

    	if((strtotime($voucher_options->voucheradd_time)+3600) < strtotime(current_time('mysql')) ) {
			return '<div class="error"><p>'.__('This URL is invalid. You can not access this page directly.', 'gift-voucher').'</p></div>';
		}
    
    	//set api key
    	$stripe = array(
      		"publishable_key" => $setting_options->stripe_publishable_key,
      		"secret_key"      => $setting_options->stripe_secret_key,
    	);

    	\Stripe\Stripe::setApiKey($stripe['secret_key']);
    	\Stripe\Stripe::setVerifySslCerts(false);

		$checkout_session = \Stripe\Checkout\Session::retrieve($_GET['sessionid']);

		//retrieve charge details
    	$sessionJson = $checkout_session->jsonSerialize();

    	\Stripe\PaymentIntent::update(
  			$sessionJson['payment_intent'],
  			['metadata' => ['order_id' => $orderid]]
		);

    	// To create a requires_capture PaymentIntent, see our guide at: 
		// https://stripe.com/docs/payments/payment-intents/use-cases#separate-auth-capture
		$intent = \Stripe\PaymentIntent::retrieve($sessionJson['payment_intent']);

    	//retrieve charge details
    	$intentJson = $intent->jsonSerialize();
    	$chargeJson = $intentJson['charges']['data'][0];

    	//check whether the charge is successful
    	if($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1) {
        	//order details
        	$amount = $chargeJson['amount'];
        	$balance_transaction = $chargeJson['balance_transaction'];
        	$currency = $chargeJson['currency'];
        	$status = $chargeJson['status'];
        	$date = date("Y-m-d H:i:s");
        
        	//if order inserted successfully
        	if($status == 'succeeded') {

				$wpdb->update(
					$voucher_table,
					array( 
						'payment_status' 	=> __('Paid'),
						'voucheradd_time'	=> current_time( 'mysql' )
					),
					array('id' => $orderid, 'pay_method' => __('Stripe')),
					array( 
						'%s'
					), 
					array('%d', '%s')
				);
				update_post_meta( $orderid, 'wpgv_stripe_session_key', $_GET['sessionid'], true );
				update_post_meta( $orderid, 'wpgv_stripe_mode_for_transaction', $setting_options->stripe_publishable_key, true );

				$voucherrow = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}giftvouchers_list` WHERE `id` = $orderid" );
				WPGV_Gift_Voucher_Activity::record( $orderid, 'firsttransact', $voucherrow->amount, 'Voucher payment recieved.' );

				$emailsubject = get_option('wpgv_emailsubject') ? get_option('wpgv_emailsubject') : 'Order Confirmation - Your Order with {company_name} (Voucher Order No: {order_number} ) has been successfully placed!';
				$emailbody = get_option('wpgv_emailbody') ? get_option('wpgv_emailbody') : '<p>Dear <strong>{customer_name}</strong>,</p><p>Order successfully placed.</p><p>We are pleased to confirm your order no {order_number}</p><p>Thank you for shopping with <strong>{company_name}</strong>!</p><p>You can download the voucher from {pdf_link}.</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
				$recipientemailsubject = get_option('wpgv_recipientemailsubject') ? get_option('wpgv_recipientemailsubject') : 'Gift Voucher - Your have received voucher from {company_name}';
				$recipientemailbody = get_option('wpgv_recipientemailbody') ? get_option('wpgv_recipientemailbody') : '<p>Dear <strong>{recipient_name}</strong>,</p><p>You have received gift voucher from <strong>{customer_name}</strong>.</p><p>You can download the voucher from {pdf_link}.</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
				$adminemailsubject = get_option('wpgv_adminemailsubject') ? get_option('wpgv_adminemailsubject') : 'New Voucher Order Received from {customer_name}  (Order No: {order_number})!';
				$adminemailbody = get_option('wpgv_adminemailbody') ? get_option('wpgv_adminemailbody') : '<p>Hello, New Voucher Order received.</p><p><strong>Order Id:</strong> {order_number}</p><p><strong>Name:</strong> {customer_name}<br /><strong>Email:</strong> {customer_email}<br /><strong>Address:</strong> {customer_address}<br /><strong>Postcode:</strong> {customer_postcode}</p>';

				$upload = wp_upload_dir();
 				$upload_dir = $upload['basedir'];
				$attachments[0] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'.pdf';
				$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
				$headers .= 'From: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";
				$headers .= 'Reply-to: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";

				/* Recipient Mail */
				if($voucher_options->shipping_type != 'shipping_as_post') {
					$recipientsub = wpgv_mailvarstr($recipientemailsubject, $setting_options, $voucher_options);
					$recipientmsg = wpgv_mailvarstr($recipientemailbody, $setting_options, $voucher_options);
					$recipientto = stripslashes($voucher_options->from_name) .'<'.$voucher_options->shipping_email.'>';
					if($voucher_options->buying_for == 'yourself') {
						$recipientto = stripslashes($voucher_options->from_name) .'<'.$voucher_options->email.'>';
					}

					if($voucher_options->email_send_date_time == 'send_instantly')
					{
						wp_mail( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
					}
					else
					{
						$save_zone = date_default_timezone_get();
				
						if(get_option('timezone_string') != "")
						{
							date_default_timezone_set(get_option('timezone_string'));
							$send_gift_voucher_email_event_date_time = strtotime($voucher_options->email_send_date_time);
						}
						else
						{
							date_default_timezone_set($save_zone);
							$send_gift_voucher_email_event_date_time = strtotime($voucher_options->email_send_date_time);
						}
				
						date_default_timezone_set($save_zone);
				
						$send_gift_voucher_email_event_args = array ( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
						wp_schedule_single_event( $send_gift_voucher_email_event_date_time, 'send_gift_voucher_email_event', $send_gift_voucher_email_event_args );

						// $send_gift_voucher_email_event_args = array ( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
						// wp_schedule_single_event( time() + 300, 'send_gift_voucher_email_event', $send_gift_voucher_email_event_args );
					}
					// wp_mail( $recipientto, $recipientsub, $recipientmsg, $headers, $attachments );
				}

				$attachments[1] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'-receipt.pdf';
				$attachments[2] = $upload_dir.'/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'-invoice.pdf';

				/* Buyer Mail */
				$buyersub = wpgv_mailvarstr($emailsubject, $setting_options, $voucher_options);
				$buyermsg = wpgv_mailvarstr($emailbody, $setting_options, $voucher_options);
				$buyerto = stripslashes($voucher_options->from_name) .'<'.$voucher_options->email.'>';
				$mail_sent = wp_mail( $buyerto, $buyersub, $buyermsg, $headers, $attachments );

				if($mail_sent == true) {
					$successpagemessage = get_option('wpgv_successpagemessage') ? get_option('wpgv_successpagemessage') : 'We have got your order! <br>E-Mail Sent Successfully to %s';
					$statusMsg = '<div class="success">'.sprintf(stripslashes($successpagemessage), $voucher_options->email).'</div>';

					$toadmin = stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>';
					$subadmin = wpgv_mailvarstr($adminemailsubject, $setting_options, $voucher_options);
					$bodyadmin = wpgv_mailvarstr($adminemailbody, $setting_options, $voucher_options);
					$headersadmin = 'Content-type: text/html;charset=utf-8' . "\r\n";
					$headersadmin .= 'From: '.stripslashes($setting_options->sender_name).' <'.$setting_options->sender_email.'>' . "\r\n";
					$headersadmin .= 'Reply-to: '.stripslashes($voucher_options->from_name).' <'.$voucher_options->email.'>' . "\r\n";

					wp_mail( $toadmin, $subadmin, $bodyadmin, $headersadmin, $attachments );
				} else {
					$statusMsg = '<div class="error"><p>'.__('Some Error Occurred From Sending this Email! <br>(Reload and Retry Again!) or Contact Us', 'gift-voucher').'</p></div>';
				}
        	} else {
	            $statusMsg = __("Transaction has been failed", 'gift-voucher');
        	}
    	} else {
	        $statusMsg = __("Transaction has been failed!", 'gift-voucher');
	    }
	} else {
	    $statusMsg = "Form submission error.......";
	}

	return $statusMsg;
}
add_shortcode( 'wpgv_stripesuccesspage', 'wpgv_stripe_success_page_shortcode' );
function wpgv_check_voucher_balance_shortcode() {
	ob_start();
	$voucher_code = '';
	if(isset($_REQUEST['voucher_code'])) {
		$voucher_code = $_REQUEST['voucher_code'];
	} ?>
	<form action="" method="post" id="scrollVoucherBalance">
        <input type="text" name="voucher_code" autocomplete="off" placeholder="<?php echo  __('Search by Gift voucher code', 'gift-voucher'); ?>" value="<?php echo $voucher_code ?>" style="width: 400px;" required>
        <input type="submit" class="button button-primary" value="<?php echo __('Check Balance', 'gift-voucher'); ?>">
    </form>
	<?php 
	if($voucher_code) {
		global $wpdb;
		$gift_voucher = new WPGV_Gift_Voucher( $voucher_code );
		if ( $gift_voucher->get_id() ) {
		?>
		<style type="text/css">
			.wpgv-balance-activity-negative {
				color: #f00;
			}
			.wpgv-balance-activity-table {
				font-size: 14px;
			}
			.wpgv-balance-activity-table td, 
			.wpgv-balance-activity-table th {
				padding: 10px;
			}
		</style>
		
		<h4>
			<strong><?php echo __('Current Voucher Balance:', 'gift-voucher'); ?> <?php echo wpgv_price_format( $gift_voucher->get_balance() ); ?></strong>
		</h4>
		<table class="wpgv-balance-activity-table">
            <tr>
                <th><?php _e( 'Date', 'gift-voucher' ); ?></th>
                <th><?php _e( 'Action', 'gift-voucher' ); ?></th>
                <th><?php _e( 'Note', 'gift-voucher' ); ?></th>
                <th><?php _e( 'Amount', 'gift-voucher' ); ?></th>
                <th><?php _e( 'Balance', 'gift-voucher' ); ?></th>
                <th><?php _e( 'Expiry Date', 'gift-voucher' ); ?></th>
            </tr>
            <?php
            $running_balance = $gift_voucher->get_balance();
            foreach ( $gift_voucher->get_activity() as $activity ) {

            ?>
            <tr>
                <td>
                <?php 
                $format_date = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';
                echo date_i18n( $format_date . ' ' . get_option( 'time_format' ), strtotime( $activity->activity_date ) ); ?>
                </td>
                <td>
                <?php 
                    if ($activity->action == 'create') {
                        _e( 'Create', 'gift-voucher' );
                    }elseif($activity->action == 'transaction'){
                        _e( 'Transaction', 'gift-voucher' );
                    }else{
                        ucwords( $activity->action );
                    }
                ?>
                </td>
                <td>
                <?php 
                    if ($activity->note == 'Voucher payment recieved.') {
                        _e( 'Voucher payment recieved.', 'gift-voucher' );
                    }else{
                        echo $activity->note;
                    }
                ?>
                </td>
                <td class="wpgv-balance-activity <?php echo ( $activity->amount < 0 ) ? 'wpgv-balance-activity-negative' : ''; ?>">
                    <?php
                        if ( $activity->amount != 0 ) {
                            echo wpgv_price_format( $activity->amount );
                        }
                    ?>
                </td>
                <td class="wpgv-balance-activity">
                    <?php echo wpgv_price_format( $running_balance ); ?>
                </td>
                <td class="expiry_date">
                    <?php echo $gift_voucher->get_expiration_date(); ?>
                </td>
            </tr>
            <?php
            }
            ?>
        </table>
		<?php
		} else {
			echo __( 'This is a invalid voucher code.', 'gift-voucher' );
		}?>
		<script>
			jQuery(document).ready(function ($) {
				jQuery('html, body').animate({
					scrollTop: jQuery("#scrollVoucherBalance").offset().top
				}, 1000);
			});
		</script>
	<?php }
	return ob_get_clean();
}
add_shortcode( 'wpgv-check-voucher-balance', 'wpgv_check_voucher_balance_shortcode' );