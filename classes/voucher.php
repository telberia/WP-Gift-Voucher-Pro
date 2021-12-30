<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

if ( ! class_exists( 'WPGV_Voucher_List' ) ) :

/**
* WPGV_Voucher_List Class
*/
class WPGV_Voucher_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() 
	{
		parent::__construct( array(
			'singular' => __( 'Voucher Order', 'gift-voucher' ), //singular name of the listed records
			'plural'   => __( 'Voucher Orders', 'gift-voucher' ), //plural name of the listed records
			'ajax'     => true //does this table support ajax?
		) );
	}

	/**
	 * Retrieve vouchers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_vouchers( $per_page = 20, $page_number = 1 ) 
	{
		global $wpdb;
		$page = isset($_GET['page']) ? $_GET['page'] : 'vouchers-lists';
		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$itemorder = isset($_GET['items']) ? $_GET['items'] : '';
		$wcproductorder = isset($_GET['wcproductorder']) ? $_GET['wcproductorder'] : '';
		$voucher_code = isset($_GET['voucher_code']) ? $_GET['voucher_code'] : '';
		$search_email = '';
		if ($voucher_code && filter_var($voucher_code, FILTER_VALIDATE_EMAIL)) {
  			$search_email = $voucher_code;
  			$voucher_code = 1;
		}
		if ($page == 'vouchers-lists') :
			if($itemorder):
				if($search && $voucher_code):
					$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'items' AND (`couponcode` LIKE $voucher_code OR (`email` LIKE '$search_email' AND `email` != '') OR (`shipping_email` LIKE '$search_email' AND `shipping_email` != '')) ORDER BY `id` DESC";
				else:
					$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'items' ORDER BY `id` DESC";
				endif;
			elseif($wcproductorder):
				if($search && $voucher_code):
					$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'gift_voucher_product' AND (`couponcode` LIKE $voucher_code OR (`email` LIKE '$search_email' AND `email` != '') OR (`shipping_email` LIKE '$search_email' AND `shipping_email` != '')) ORDER BY `id` DESC";
				else:
					$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'gift_voucher_product' ORDER BY `id` DESC";
				endif;
			else:
				if($search && $voucher_code):
					$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'vouchers' AND (`couponcode` LIKE $voucher_code OR (`email` LIKE '$search_email' AND `email` != '') OR (`shipping_email` LIKE '$search_email' AND `shipping_email` != '')) ORDER BY `id` DESC";
				else:
					$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'vouchers' ORDER BY `id` DESC";
				endif;
			endif;

		elseif ($page == 'redeem-voucher') :
			$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list WHERE `couponcode` = $voucher_code OR (`email` LIKE '$search_email' AND `email` != '') OR (`shipping_email` LIKE '$search_email' AND `shipping_email` != '') ORDER BY `id` DESC";
		endif;

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Set as used a voucher record.
	 *
	 * @param int $id voucher id
	 */
	public static function used_voucher( $id ) 
	{
		// Fix Minh
		global $wpdb;

		$wpdb->update(
			"{$wpdb->prefix}giftvouchers_list",
			array('id'=>$id, 'status'=>'used'),
			array('id'=>$id)
		);
		$sum_redeem = null;
		$sum_result = null;
		$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}giftvouchers_activity` WHERE `voucher_id` = $id " );
		foreach($result as $amountsum){
			if($amountsum->action == "firsttransact"){
				$sum_result = (int) $amountsum->amount;
			}
			if($amountsum->action == "transaction"){
				$sum_redeem += (int) $amountsum->amount;
			}
			
		}
		$sum_all = $sum_result + $sum_redeem;
	
		WPGV_Gift_Voucher_Activity::record( $id, 'transaction', '-'.$sum_all, 'Voucher used completely.' );
	}

	/**
	 * Set as paid a voucher record.
	 *
	 * @param int $id voucher id
	 */
	public static function paid_voucher( $id ) 
	{
		global $wpdb;

		$wpdb->update(
			"{$wpdb->prefix}giftvouchers_list",
			array('id'=>$id, 'payment_status'=>'Paid'),
			array('id'=>$id)
		);
		$result = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}giftvouchers_list` WHERE `id` = $id" );
		WPGV_Gift_Voucher_Activity::record( $id, 'transaction', $result->amount, 'Voucher payment recieved.' );
	}

	public static function send_mail( $id ) 
	{
		global $wpdb;
		$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
		$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );

		$result = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}giftvouchers_list` WHERE `id` = $id" );
		
		$upload = wp_upload_dir();
 		$upload_dir = $upload['basedir'];
		$attachments[0] = $upload_dir.'/voucherpdfuploads/'.$result->voucherpdf_link.'.pdf';
		$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
		$headers .= 'From: '.$setting_options->sender_name.' <'.$setting_options->sender_email.'>' . "\r\n";
		$headers .= 'Reply-to: '.$setting_options->sender_name.' <'.$setting_options->sender_email.'>' . "\r\n";

		$emailsubject = get_option('wpgv_emailsubject') ? get_option('wpgv_emailsubject') : 'Order Confirmation - Your Order with {company_name} (Voucher Order No: {order_number} ) has been successfully placed!';
		if(isset($_GET['per_invoice']) && $_GET['per_invoice'] == 1) {
			$emailbody = get_option('wpgv_emailbodyperinvoice') ? get_option('wpgv_emailbodyperinvoice') : '<p>Dear <strong>{customer_name}</strong>,</p><p>Order successfully placed.</p><p>We are pleased to confirm your order no {order_number}</p><p>Thank you for shopping with <strong>{company_name}</strong>!</p><p>You can download the voucher from {pdf_link}.</p><p>You will pay us directly into bank. Our bank details are below:</p><p><strong>Account Number: </strong>XXXXXXXXXXXX<br /><strong>Bank Code: </strong>XXXXXXXX</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
		} else {
			$emailbody = get_option('wpgv_emailbody') ? get_option('wpgv_emailbody') : '<p>Dear <strong>{customer_name}</strong>,</p><p>Order successfully placed.</p><p>We are pleased to confirm your order no {order_number}</p><p>Thank you for shopping with <strong>{company_name}</strong>!</p><p>You can download the voucher from {pdf_link}.</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';
		}

		$recipientemailsubject = get_option('wpgv_recipientemailsubject') ? get_option('wpgv_recipientemailsubject') : 'Gift Voucher - Your have received voucher from {company_name}';
		$recipientemailbody = get_option('wpgv_recipientemailbody') ? get_option('wpgv_recipientemailbody') : '<p>Dear <strong>{recipient_name}</strong>,</p><p>You have received gift voucher from <strong>{customer_name}</strong>.</p><p>You can download the voucher from {pdf_link}.</p><p>- For any clarifications please feel free to email us at {sender_email}.</p><p><strong>Warm Regards, <br /></strong> <strong>{company_name}<br />{website_url}</strong></p>';

		$email = ($result->shipping_email != '') ? $result->shipping_email:$result->email;

		$emailto = $result->from_name .'<'.$email.'>';
		$recipientemailsubject = wpgv_mailvarstr($recipientemailsubject, $setting_options, $result);
		$recipientemailbody = wpgv_mailvarstr($recipientemailbody, $setting_options, $result);

		$recipientmail_sent = wp_mail($emailto,$recipientemailsubject,$recipientemailbody,$headers, $attachments);

		$attachments[1] = $upload_dir.'/voucherpdfuploads/'.$result->voucherpdf_link.'-receipt.pdf';

		/* Buyer Mail */
		$buyersub = wpgv_mailvarstr($emailsubject, $setting_options, $result);
		$buyermsg = wpgv_mailvarstr($emailbody, $setting_options, $result);
		$buyerto = $result->from_name .'<'.$result->email.'>';
		$mail_sent = wp_mail( $buyerto, $buyersub, $buyermsg, $headers, $attachments );
	}

	/**
	 * Delete a voucher record.
	 *
	 * @param int $id voucher id
	 */
	public static function delete_voucher( $id ) 
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}giftvouchers_list",
			array( 'id' => $id ),
			array('%d')
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() 
	{
		global $wpdb;
		$page = isset($_GET['page']) ? $_GET['page'] : '';
		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$itemorder = isset($_GET['items']) ? $_GET['items'] : '';
		$voucher_code = isset($_GET['voucher_code']) ? $_GET['voucher_code'] : '';
		$search_email = '';
		if ($voucher_code && filter_var($voucher_code, FILTER_VALIDATE_EMAIL)) {
  			$search_email = $voucher_code;
  			$voucher_code = 1;
		}
		if($page == 'vouchers-lists') :
		if($itemorder):
			if($search && $voucher_code):
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'items' AND (`couponcode` LIKE $voucher_code OR `shipping_email` LIKE '$search_email') ORDER BY `id` DESC";
			else:
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'items' ORDER BY `id` DESC";
			endif;
		else:
			if($search && $voucher_code):
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'vouchers' AND (`couponcode` LIKE $voucher_code OR `shipping_email` LIKE '$search_email') ORDER BY `id` DESC";
			else:
				$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_list WHERE `order_type` = 'vouchers' ORDER BY `id` DESC";
			endif;
		endif;
		elseif ($page == 'redeem-voucher') :
			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_list WHERE `couponcode` = $voucher_code OR `shipping_email` = '$search_email' ORDER BY `id` DESC";
		endif;

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no voucher data is available */
	public function no_items() 
	{
		_e( 'No purchased voucher codes yet.', 'gift-voucher' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_id
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_id ) 
	{
		switch ( $column_id ) {
			case 'couponcode':
			case 'voucheradd_time':
			case 'voucher_info':
				return $item[ $column_id ];
			case 'buyer_info':
				return $item[ $column_id ];
			case 'mark_used':
				return $item[ $column_id ];
			case 'receipt':
				return $item[ $column_id ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() 
	{
		$columns = array(
			'cb'      				=> '<input type="checkbox" />',
			'id'    				=> __( 'Order id', 'gift-voucher' ),
			'couponcode'    		=> __( 'Voucher Code', 'gift-voucher' ),
			'voucher_info'			=> __( 'Voucher Information', 'gift-voucher' ),
			'buyer_info'			=> __( 'Buyer\'s Information', 'gift-voucher' ),
			'action'				=> __( 'Action', 'gift-voucher' ),
			'receipt'	 			=> __( 'Voucher', 'gift-voucher' ),
			'voucheradd_time'	 	=> __( 'Order Date', 'gift-voucher' ),
		);

		return $columns;
	}

	/**
	 * Render the bulk used checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) 
	{
		return sprintf(
			'<input type="checkbox" name="voucher_code[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_id( $item ) 
	{
		$gift_voucher = new WPGV_Gift_Voucher( $item['couponcode'] );
		$title = '<strong>' . $item['id'] . '</strong>';
		$delete_nonce = wp_create_nonce( 'delete_voucher' );
		$form = '';

		$actions = [
			'order_detail' => sprintf( '<a href="?page=%s&action=%s&voucher_id=%s">%s</a>', esc_attr( 'view-voucher-details' ), 'view_voucher', $item['id'], __('View Details', 'gift-voucher')),
			'delete' => sprintf( '<a href="?page=%s&action=%s&voucher=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce, __('Delete Voucher', 'gift-voucher') ),
			'checkbalance' => sprintf( '<a href="?page=%s&voucher_code=%s" target="_blank">%s</a>', 'check-vouchers-balance', $item['couponcode'], __('Check Voucher Balance', 'gift-voucher') ),
		];
		if($item['payment_status'] == 'Paid') {
			$form = '<div class="wpgv_wrap"><p>'.__('Redeem Partial Amount', 'gift-voucher').'</p><div class="wpgv_flex"><input type="number" class="voucher_amount" name="voucher_amount" value="'.(int)$gift_voucher->get_balance().'" id="voucher_amount'.$item['id'].'"><input type="button" value="'.__('Add', 'gift-voucher').'" class="button button-primary" onclick="redeemVoucher('.$item['id'].','.(int)$gift_voucher->get_balance().')"></div></div>';
		}

		return $title . $this->row_actions( $actions ) . $form;
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_couponcode( $item ) 
	{
		$gift_voucher = new WPGV_Gift_Voucher( $item['couponcode'] );
		$couponcode = '<strong>' . $item['couponcode'] . '</strong>';
		$remainingbalance = '';

		if($item['payment_status'] == 'Paid') {
			$remainingbalance = __('Remaining Balance:', 'gift-voucher').' '.wpgv_price_format( $gift_voucher->get_balance() );
		}

		return $couponcode . $remainingbalance;
	}

	/**
	 * Method for voucher information
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_voucher_info( $item ) 
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'giftvouchers_setting';
		$options = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = 1" );
	?>
		<table style="width: 100%;">
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Buying For', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo ($item['buying_for'] == 'yourself') ? __('Yourself', 'gift-voucher') : __('Someone Else', 'gift-voucher'); ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Buyer Name', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['from_name']; ?></td>
			</tr>
			<?php if ($item['buying_for'] != 'yourself') { ?>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Recipient Name', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['to_name']; ?></td>
			</tr>
			<?php } ?>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Voucher Value', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo wpgv_price_format($item['amount']); ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('Voucher Send Time', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo ($item['email_send_date_time'] == "send_instantly") ? __('Send Instantly', 'gift-voucher') : $item['email_send_date_time'] ; ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('Additional Charges', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo get_post_meta($item['id'], 'wpgv_extra_charges', true) ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('Total Payable Amount', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo get_post_meta($item['id'], 'wpgv_total_payable_amount', true) ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('Message', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo $item['message']; ?></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Method for buyer information
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_buyer_info( $item ) 
	{
	?>
		<table style="width: 100%;">
			<tr>
				<th width="45%;" style="font-weight:bold;"><?php echo __('Shipping', 'gift-voucher') ?>:</th>
				<td width="55%;"><?php echo ($item['shipping_type'] == 'shipping_as_email') ? __('Shipping as Email', 'gift-voucher') : __('Shipping as Post', 'gift-voucher'); ?></td>
			</tr>
			<?php if ($item['shipping_type'] == 'shipping_as_email') : ?>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Recipient Email', 'gift-voucher') ?>:</th>
				<td><?php echo $item['shipping_email']; ?></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Buyer Email', 'gift-voucher') ?>:</th>
				<td><?php echo $item['email']; ?></td>
			</tr>
			<?php else: ?>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Name', 'gift-voucher') ?>:</th>
				<td><?php echo $item['firstname'].' '.$item['lastname']; ?></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Buyer Email', 'gift-voucher') ?>:</th>
				<td><?php echo $item['email']; ?></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Address', 'gift-voucher') ?>:</th>
				<td><?php echo $item['address']; ?></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Postcode', 'gift-voucher') ?>:</th>
				<td><?php echo $item['postcode']; ?></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Shipping Method', 'gift-voucher') ?>:</th>
				<td><?php echo $item['shipping_method']; ?></td>
			</tr>
			<?php endif; ?>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Payment Method', 'gift-voucher') ?>:</th>
				<td><?php echo $item['pay_method']; ?></td>
			</tr>
			<?php if($item['pay_method'] == 'Stripe' && get_post_meta($item['id'], 'wpgv_stripe_session_key', true)) { ?>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Stripe Session ID', 'gift-voucher') ?>:</th>
				<td><span style="width: 150px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo get_post_meta($item['id'], 'wpgv_stripe_session_key', true) ?>"><?php echo get_post_meta($item['id'], 'wpgv_stripe_session_key', true) ?></span></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Stripe Publishable Key', 'gift-voucher') ?>:</th>
				<td><span style="width: 150px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo get_post_meta($item['id'], 'wpgv_stripe_mode_for_transaction', true) ?>"><?php echo get_post_meta($item['id'], 'wpgv_stripe_mode_for_transaction', true) ?></span></td>
			</tr>
			<?php } elseif($item['pay_method'] == 'Paypal' && get_post_meta($item['id'], 'wpgv_paypal_payment_key', true)) { ?>
			<tr>
				<th style="font-weight:bold;"><?php echo __('PayPal PaymentID', 'gift-voucher') ?>:</th>
				<td><span style="width: 150px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo get_post_meta($item['id'], 'wpgv_paypal_payment_key', true) ?>"><?php echo get_post_meta($item['id'], 'wpgv_paypal_payment_key', true) ?></span></td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('PayPal Mode', 'gift-voucher') ?>:</th>
				<td><span style="width: 150px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo get_post_meta($item['id'], 'wpgv_paypal_mode_for_transaction', true) ?>"><?php echo get_post_meta($item['id'], 'wpgv_paypal_mode_for_transaction', true) ?></span></td>
			</tr>
			<?php } ?>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Payment Status', 'gift-voucher') ?>:</th>
				<td>
					<?php
						if ($item['payment_status'] == 'Not Pay') {
							echo __('Not Pay', 'gift-voucher');
						}else{
							echo __($item['payment_status'], 'gift-voucher');
						}
					?>
					<?php //echo $item['payment_status']; ?>
					
				</td>
			</tr>
			<tr>
				<th style="font-weight:bold;"><?php echo __('Expiry', 'gift-voucher') ?>:</th>
				<td><abbr title="<?php echo $item['expiry']; ?>"><?php echo $item['expiry']; ?></abbr></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Method for mark as used link
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_action( $item )
	{
		$used_nonce = wp_create_nonce( 'used_voucher' );
		if($item['status'] == 'unused') {
			$actions = array(
				'used' => sprintf( '<a href="?page=%s&action=%s&voucher=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'used', absint( $item['id'] ), $used_nonce, __( 'Mark as Used', 'gift-voucher' ) ),
				 );
			$mark_used = $this->row_actions( $actions, true );
		} else {
			$mark_used = '<span class="vused">'.__('Voucher Used', 'gift-voucher').'</span>';
		}

		$paid_nonce = wp_create_nonce( 'paid_voucher' );
		if($item['payment_status'] != 'Paid') {
			$actions = array(
				'paid' => sprintf( '<a href="?page=%s&action=%s&voucher=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'paid', absint( $item['id'] ), $paid_nonce, __( 'Mark as Paid', 'gift-voucher' ) )
				 );
			$mark_paid = $this->row_actions( $actions, true );
			$send_mail = '';
		} else {
			$mark_paid = '<span class="vpaid">'.__('Paid', 'gift-voucher').'</span>';

			$actions = array(
				'paid' => sprintf( '<a href="?page=%s&action=%s&voucher=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'mail', absint( $item['id'] ), $paid_nonce, __( 'Send Mail', 'gift-voucher' ) )
				 );
			$send_mail = $this->row_actions( $actions, true );
		}

		return $mark_used . $send_mail . $mark_paid;
	}

	/**
	 * Method for create receipt
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_voucheradd_time( $item )
	{
		$format_date = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';
	?>
		<abbr title="<?php echo date('Y/m/d H:i:s a', strtotime($item['voucheradd_time'])); ?>"><?php echo date($format_date, strtotime($item['voucheradd_time'])); ?></abbr>
	<?php
	}

	/**
	 * Method for create receipt
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_receipt( $item )
	{
		
		$wpgv_customer_receipt = get_option('wpgv_customer_receipt') ? get_option('wpgv_customer_receipt') : 0;
		$upload = wp_upload_dir();
		$upload_dir = $upload['baseurl'];
		$upload_dir_check = $upload['basedir'];
		$upload_dir = $upload_dir . '/voucherpdfuploads';
		$check_empty_receipt = file_exists($upload_dir_check."/voucherpdfuploads/".$item['voucherpdf_link']."-receipt.pdf");
		$voucher = '<a href="'.$upload_dir."/".$item['voucherpdf_link'].'.pdf" title="click to show voucher" target="_blank"><img src="'.WPGIFT__PLUGIN_URL. '/assets/img/pdf.png" /></a>';
		if($item['payment_status'] == 'Paid' && $wpgv_customer_receipt == "1" && $check_empty_receipt == true) {
			$voucher .= '<br><a href="'.$upload_dir."/".$item['voucherpdf_link'].'-receipt.pdf" title="click to show order receipt" target="_blank"><img src="'.WPGIFT__PLUGIN_URL. '/assets/img/pdf.png" /></a>';
		}

		return $voucher;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions()
	{
			$actions = array(
			'bulk-used' => __('Mark as Used', 'gift-voucher'),
			'bulk-paid' => __('Mark as Paid', 'gift-voucher'),
			'bulk-delete' => __('Delete', 'gift-voucher'),
		);

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() 
	{
		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'vouchers_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, 	//WE have to calculate the total number of items
			'per_page'    => $per_page 		//WE have to determine how many items to show on a page
		) );

		$this->items = self::get_vouchers( $per_page, $current_page );
	}

	/**
	 * Handles data for mark as used the bulk action
	 */
	public function process_bulk_action() 
	{
		//Detect when a bulk action is being triggered...
		if ( 'bulk-used' === $this->current_action() ) {
			foreach ($_REQUEST['voucher_code'] as $voucher) {
				self::used_voucher( absint( $voucher ) );
			}
		        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_safe_redirect( "?page=vouchers-lists");
				exit;
		} elseif ( 'bulk-paid' === $this->current_action() ) {
			foreach ($_REQUEST['voucher_code'] as $voucher) {
				self::paid_voucher( absint( $voucher ) );
			}
		        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_safe_redirect( "?page=vouchers-lists");
				exit;
		} elseif ( 'bulk-delete' === $this->current_action() ) {
			foreach ($_REQUEST['voucher_code'] as $voucher) {
				self::delete_voucher( absint( $voucher ) );
			}
		        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_safe_redirect( "?page=vouchers-lists");
				exit;
		} elseif ( 'used' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'used_voucher' ) ) {
				wp_die( 'Go get a life script kiddies' );
			}
			self::used_voucher( absint( $_GET['voucher'] ) );
			wp_safe_redirect( "?page=vouchers-lists");
			exit;
		} elseif ( 'paid' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'paid_voucher' ) ) {
				wp_die( 'Go get a life script kiddies' );
			}
			self::paid_voucher( absint( $_GET['voucher'] ) );
			wp_safe_redirect( "?page=vouchers-lists");
			exit;
		} elseif ( 'mail' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'paid_voucher' ) ) {
				wp_die( 'Go get a life script kiddies' );
			}
			self::send_mail( absint( $_GET['voucher'] ) );
			wp_safe_redirect( "?page=vouchers-lists");
			exit;
		} elseif ( 'delete' === $this->current_action() ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_voucher' ) ) {
				wp_die( 'Go get a life script kiddies' );
			}
			self::delete_voucher( absint( $_GET['voucher'] ) );
			wp_safe_redirect( "?page=vouchers-lists");
			exit;
		}

		if ( 'order_detail' === $this->current_action() ) { 

			$order_id = $_REQUEST['order_id'];
			global $wpdb;
			$voucher_table_name = $wpdb->prefix . 'giftvouchers_list';
			$order_detail = $wpdb->get_row( "SELECT * FROM $voucher_table_name WHERE id = $order_id" );
			?>
			<div class="admin-modal">
				<div class="admin-custom-modal add-new">
					<span class="close dashicons dashicons-no-alt"></span>
					<h3><?php echo __('Order Details', 'gift-voucher') ?> (Order ID: <?php echo $order_id; ?>)  <?php 
					if($order_detail->status == "unused") {
						echo "<strong style='color:#fff;font-size:14px;background:#ddd;padding:2px 5px;'>Unused</strong>";
					} 
					else if($order_detail->status == "used") { 
						echo "<strong style='color:#fff;font-size:14px;display: inline-block;background:#233dcc;padding:2px 5px;'>Used</strong>";
					} ?></h3>
				</div>
			</div>

		<?php 
		}
	}

}

endif;