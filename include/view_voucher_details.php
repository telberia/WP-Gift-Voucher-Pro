<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly
global $wpdb;
$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
$template_table = $wpdb->prefix . 'giftvouchers_template';

if ( !current_user_can( 'manage_options' ) )
{
	wp_die( 'You are not allowed to be on this page.' );
}

$voucher_id = $_REQUEST['voucher_id'];
$voucher_options = $wpdb->get_row( "SELECT * FROM $voucher_table WHERE id = $voucher_id" );

$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
if($voucher_options->order_type == 'vouchers'):
	$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $voucher_options->template_id" );
	$images = $template_options->image_style ? json_decode($template_options->image_style) : ['','',''];
	$image_attributes = wp_get_attachment_image_src( $images[0], 'voucher-medium' );
else:
	$style_image = get_post_meta($voucher_options->item_id, 'style1_image', true);
	$image_attributes = wp_get_attachment_image_src( $style_image, 'voucher-medium' );
endif;
?>
<div class="wrap">
	<h1><?php echo __( 'Voucher Order ID', 'gift-voucher' ) ?>: <?php echo $voucher_options->id; ?></h1>
		<p class="description"><?php echo __( 'Here you can find detailed information for a voucher code.', 'gift-voucher' ) ?></p><br>
	<div id="voucher-details">
		<table class="widefat main">
			<thead>
				<th><?php echo __( 'Voucher Code', 'gift-voucher' )?></th>
				<th><?php echo __( 'Order Date', 'gift-voucher' )?></th>
				<th><?php echo __( 'Status', 'gift-voucher' )?></th>
				<th><?php echo __( 'See Receipt (PDF)', 'gift-voucher' )?></th>
			</thead>
			<tbody>
				<tr>
					<td><h3><?php echo $voucher_options->couponcode; ?></h3></td>
					<td><abbr title="<?php echo date('Y/m/d H:i:s a', strtotime($voucher_options->voucheradd_time)); ?>"><?php echo date('Y/m/d', strtotime($voucher_options->voucheradd_time)); ?></abbr></td>
					<td><?php if($voucher_options->status == 'unused') echo '<span class="vunused">'.__('Unused', 'gift-voucher' ).'</span>'; else echo '<span class="vused">'.__('Voucher Used', 'gift-voucher' ).'</span>'; ?></td>
					<td><?php echo '<a href="'.get_home_url().'/wp-content/uploads/voucherpdfuploads/'.$voucher_options->voucherpdf_link.'.pdf" title="click to show order receipt" target="_blank"><img src="'.WPGIFT__PLUGIN_URL. '/assets/img/pdf.png" width="50"/></a>'; ?></td>
				</tr>
			</tbody>
		</table><br>
		<?php if($voucher_options->order_type == 'vouchers'): ?>
		<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Template Information', 'gift-voucher' )?></span></h2>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php echo __( 'Template Name', 'gift-voucher' )?></th>
					<th><?php echo __( 'Template Image', 'gift-voucher' )?></th>
				</tr>
			</thead>
			<tbody>
				<?php if($voucher_options->item_id): ?>
					<tr>
						<td><?php echo $template_options->title; ?></td>
						<td><img src="<?php echo $image_attributes[0]; ?>" height="60"></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php else: ?>
		<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Item Information', 'gift-voucher' )?></span></h2>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php echo __( 'Item Name', 'gift-voucher' )?></th>
					<th><?php echo __( 'Item Description', 'gift-voucher' )?></th>
					<th><?php echo __( 'Item Image', 'gift-voucher' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo get_the_title($voucher_options->item_id); ?></td>
					<td><?php echo get_post_meta( $voucher_options->item_id, 'description', true ); ?></td>
					<td><img src="<?php echo $image_attributes[0] ?>" height="60"></td>
				</tr>
			</tbody>
		</table>
		<?php endif; ?>
		<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Voucher Information', 'gift-voucher' )?></span></h2>
		<table class="widefat">
			<thead>
				<tr>
					<th width="15%"><?php echo __( 'Buying For', 'gift-voucher' )?></th>
					<th width="15%"><?php echo __( 'Your Name', 'gift-voucher' )?></th>
					<?php if ($voucher_options->buying_for != 'yourself') { ?>
						<th width="15%"><?php echo __( 'Recipient Name', 'gift-voucher' )?></th>
					<?php } ?>
					<th width="10%"><?php echo __( 'Amount', 'gift-voucher' )?></th>
					<th width="60%"><?php echo __( 'Message', 'gift-voucher' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo ($voucher_options->buying_for == 'yourself') ? 'Yourself' : 'Someone Else'; ?></td>
					<td><?php echo $voucher_options->from_name; ?></td>
					<?php if ($voucher_options->buying_for != 'yourself') { ?>
						<td><?php echo $voucher_options->to_name; ?></td>
					<?php } ?>
					<td><?php echo wpgv_price_format($voucher_options->amount); ?></td>
					<td><?php echo $voucher_options->message; ?></td>
				</tr>
			</tbody>
		</table>
		<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Buyers Information', 'gift-voucher' ) ?></span></h2>
		<table class="widefat">
			<thead>
				<tr>
					<th width="10%"><?php echo __( 'Shipping', 'gift-voucher' ) ?></th>
					<?php if ($voucher_options->shipping_type == 'shipping_as_email') { ?>
						<th width="10%"><?php echo __( 'Email', 'gift-voucher' ) ?></th>
					<?php } else { ?>
					<th width="10%"><?php echo __( 'Name', 'gift-voucher' ) ?></th>
					<th width="20%"><?php echo __( 'Email', 'gift-voucher' ) ?></th>
					<th width="40%"><?php echo __( 'Address', 'gift-voucher' ) ?></th>
					<th width="10%"><?php echo __( 'Postcode', 'gift-voucher' ) ?></th>
					<th width="10%"><?php echo __( 'Shipping Method', 'gift-voucher' ) ?></th>
					<?php } ?>
					<th width="10%"><?php echo __( 'Payment Method', 'gift-voucher' ) ?></th>
					<th width="10%"><?php echo __( 'Transaction ID', 'gift-voucher' ) ?></th>
					<th width="10%"><?php echo __( 'Expiry Date', 'gift-voucher' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo ($voucher_options->shipping_type == 'shipping_as_email') ? __('Shipping as Email', 'gift-voucher') : __('Shipping as Post', 'gift-voucher'); ?></td>
					<?php if ($voucher_options->shipping_type == 'shipping_as_email') { ?>
						<td><?php echo $voucher_options->shipping_email; ?></td>
					<?php } else { ?>
					<td><?php echo $voucher_options->firstname.' '. $voucher_options->lastname; ?></td>
					<td><?php echo $voucher_options->email; ?></td>
					<td><?php echo $voucher_options->address; ?></td>
					<td><?php echo $voucher_options->postcode; ?></td>
					<td><?php echo $voucher_options->shipping_method; ?></td>
					<?php } ?>
					<td><?php echo $voucher_options->pay_method; ?></td>
					<?php if($voucher_options->pay_method == 'Stripe' && get_post_meta($voucher_options->id, 'wpgv_stripe_session_key', true)) { ?>
		<td><span style="width: 150px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo get_post_meta($voucher_options->id, 'wpgv_stripe_session_key', true) ?>"><?php echo get_post_meta($voucher_options->id, 'wpgv_stripe_session_key', true) ?></span></td>
	<?php } elseif($voucher_options->pay_method == 'Paypal' && get_post_meta($voucher_options->id, 'wpgv_paypal_payment_key', true)) { ?>
		<td><span style="width: 150px; display: block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?php echo get_post_meta($voucher_options->id, 'wpgv_paypal_payment_key', true) ?>"><?php echo get_post_meta($voucher_options->id, 'wpgv_paypal_payment_key', true) ?></span></td>
	<?php } else { ?>
		<td></td>
	<?php } ?>
					</td>
					<td><abbr title="<?php echo $voucher_options->expiry; ?>"><?php echo $voucher_options->expiry; ?></abbr></td>
				</tr>
			</tbody>
		</table><br>
		<a href="<?php echo admin_url( 'admin.php' ); ?>?page=vouchers-lists" class="button button-primary"><?php echo __( 'Back to Vouchers List', 'gift-voucher' )?></a>
	</div>
</div>