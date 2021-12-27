<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly
	
	global $wpdb;
	$setting_table_name = $wpdb->prefix . 'giftvouchers_invoice_settings';

	$get_settings = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );
	$is_active 		= ($get_settings->is_invoice_active == '')?'0':$get_settings->is_invoice_active;
	$company_name 	= ($get_settings->company_name == '')?'':$get_settings->company_name;
	$invoice_color 	= ($get_settings->invoice_color == '')?'':$get_settings->invoice_color;
	$company_logo 	= ($get_settings->company_logo == '')?'':$get_settings->company_logo;
	$address_line1 	= ($get_settings->address_line1 == '')?'':$get_settings->address_line1;
	$address_line2 	= ($get_settings->address_line2 == '')?'':$get_settings->address_line2;
	$address_line3 	= ($get_settings->address_line3 == '')?'':$get_settings->address_line3;
	$address_line4 	= ($get_settings->address_line4 == '')?'':$get_settings->address_line4;
	$bottom_line 	= ($get_settings->bottom_line == '')?'':$get_settings->bottom_line;

	if ( !current_user_can( 'manage_options' ) )
	{
		wp_die( 'You are not allowed to be on this page.' );
	}

	if ( isset($_POST['company_name']) ) {

		wp_verify_nonce( $_POST['voucher_settings_verify'], 'voucher_settings_verify' );

		$is_invoice_active = sanitize_text_field( $_POST['is_invoice_active'] );
		$company_name	   = sanitize_text_field( $_POST['company_name'] );
		$voucher_bgcolor   = sanitize_text_field( $_POST['voucher_bgcolor'] );
		$company_logo	   = sanitize_text_field( $_POST['company_logo'] );
		$address_line1	   = sanitize_text_field( $_POST['address_line1'] );
		$address_line2	   = sanitize_text_field( $_POST['address_line2'] );
		$address_line3	   = sanitize_text_field( $_POST['address_line3'] );
		$address_line4	   = sanitize_text_field( $_POST['address_line4'] );
		$bottom_line	   = sanitize_text_field( $_POST['bottom_line'] );

		if(!$wpdb->get_var( "SELECT * FROM $setting_table_name WHERE id = 1" )) {
			
			$wpdb->query("INSERT INTO $setting_table_name SET is_invoice_active='".$is_invoice_active."',company_name='".$company_name."',invoice_color='".$voucher_bgcolor."',company_logo='".$company_logo."',address_line1='".$address_line1."',address_line2='".$address_line2."',address_line3='".$address_line3."',address_line4='".$address_line4."',bottom_line='".$bottom_line."'");

			$settype = 'updated';
			$setmessage = __('Your Settings Saved Successfully.', 'gift-voucher');
			add_settings_error(
    	    	'wooenc_settings_updated',
	        	esc_attr( 'settings_updated' ),
        		$setmessage,
        		$settype
	    	);
		}
		else{
			$wpdb->update(
		      	$setting_table_name,
		      	array( 
		      		'is_invoice_active' => $is_invoice_active,
			        'company_name' 	=> $company_name,
					'invoice_color' => $voucher_bgcolor,
					'company_logo' 	=> $company_logo,
					'address_line1' => $address_line1,
					'address_line2' => $address_line2,
					'address_line3' => $address_line3,
					'address_line4' => $address_line4,
					'bottom_line'	=> $bottom_line
			  	),
		      	array('id'=>1)
		    );

		    $settype = 'updated';
			$setmessage = __('Your Settings Saved Successfully.', 'gift-voucher');
			add_settings_error(
    	    	'wooenc_settings_updated',
	        	esc_attr( 'settings_updated' ),
        		$setmessage,
        		$settype
	    	);
		}

		$url = admin_url( 'admin.php' ).'?page=invoice-setting';
		wp_redirect($url);
		exit;
	}
?>

<div class="wrap wpgiftv-settings">
			<h1><?php echo __( 'Invoice Settings', 'gift-voucher' ); ?></h1>
			<hr>
	<div class="wpgiftv-row">
		<div class="wpgiftv-col75">
			<div class="white-box">
			<form method="post" name="invoice-setting" id="invoice-setting" action="<?php echo admin_url( 'admin.php' ); ?>?page=invoice-setting">
				<input type="hidden" name="action" value="save_voucher_settings_option" />
				<?php $nonce = wp_create_nonce( 'voucher_settings_verify' ); ?>
				<input type="hidden" name="voucher_settings_verify" value="<?php echo($nonce); ?>">
				<table class="form-table tab-content tab-content-active" id="general">
					<tbody>
						<tr>
							<th colspan="2" style="padding-bottom:0;padding-top: 0;">
								<h3><?php echo __( 'Invoice Settings', 'gift-voucher' ); ?></h3>
							</th>
						</tr>
						<tr>
							<th scope="row">
								<label for="is_invoice_active"><?php echo __( 'Is Invoice Active?', 'gift-voucher' ); ?></label>
							</th>
							<td>
								<input name="is_invoice_active" type="checkbox" <?php if($is_active == 1){ echo "checked"; } ?> id="is_invoice_active" value="1" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="company_name"><?php echo __( 'Company Name', 'gift-voucher' ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="company_name" type="text" id="company_name" value="<?php echo $company_name; ?>" class="regular-text" aria-required="true" required="required">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_bgcolor"><?php echo __( 'Invoice Color', 'gift-voucher' ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="voucher_bgcolor" type="text" id="voucher_bgcolor" value="<?php echo $invoice_color; ?>" class="regular-text" aria-required="true">
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="logo"><?php echo __( 'Company logo URL', 'gift-voucher' ); ?></label>
							</th>
							<td>
								<input name="company_logo" type="text" id="company_logo" value="<?php echo $company_logo; ?>" class="regular-text">
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="address_line1"><?php echo __( 'Address', 'gift-voucher' ); ?></label>
							</th>
							<td style="padding-bottom: 0px;">
								<input name="address_line1" type="text" id="address_line1" value="<?php echo $address_line1; ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td style="padding-top: 0px;padding-bottom: 0px;">
								<input name="address_line2" type="text" id="address_line2" value="<?php echo $address_line2; ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td style="padding-top: 0px;padding-bottom: 0px;">
								<input name="address_line3" type="text" id="address_line3" value="<?php echo $address_line3; ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td style="padding-top: 0px;padding-bottom: 0px;">
								<input name="address_line4" type="text" id="address_line4" value="<?php echo $address_line4; ?>" class="regular-text">
							</td>
						</tr>

						<!-- Bootom Line -->

						<tr>
							<th scope="row">
								<label for="bottom_line"><?php echo __( 'Bottom Line', 'gift-voucher' ); ?></label>
							</th>
							<td style="padding-top: 0px;padding-bottom: 0px;">
								<input name="bottom_line" type="text" id="bottom_line" value="<?php echo $bottom_line; ?>" class="regular-text">
							</td>
						</tr>

					</tbody>
				</table>
				<p class="submit"><?php submit_button( __( 'Save Settings', 'gift-voucher'), 'primary', 'submit', false ); ?></p>
			</form>
				
			</div>
		</div>
	</div>
	<span class="wpgiftv-disclaimer">Thank you for using <b>WordPress Gift Voucher</b>.</span>
</div>