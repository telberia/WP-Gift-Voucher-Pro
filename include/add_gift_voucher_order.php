<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

	global $wpdb;
	$setting_table_name = $wpdb->prefix . 'giftvouchers_setting';

	if ( !current_user_can( 'manage_options' ) )
	{
		wp_die( 'You are not allowed to be on this page.' );
	}

	$voucher_table  = $wpdb->prefix . 'giftvouchers_list';
    $setting_table  = $wpdb->prefix . 'giftvouchers_setting';
    $template_table = $wpdb->prefix . 'giftvouchers_template';

	$template_options = $wpdb->get_results( "SELECT * FROM $template_table WHERE active = 1" );
    $nonce = wp_create_nonce( 'voucher_form_verify' );

	$coupon_code_length = get_option('wpgv_coupon_code_length') ? get_option('wpgv_coupon_code_length') : 12;
	
    $setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );

    $chooseStyle = '';
    if ($setting_options->is_style_choose_enable) {
        $voucher_styles = json_decode($setting_options->voucher_style);
        $chooseStyle = '<label for="chooseStyle">'.__('Choose Voucher Style', 'gift-voucher' ).'</label> <br><select name="chooseStyle" id="chooseStyle" class="required">';
        foreach ($voucher_styles as $key => $value) 
        {
            $chooseStyle .= '<option value="'.$value.'">'.__('Style', 'gift-voucher').' '.($value+1).'</option>';
        }
        $chooseStyle .= '</select>';
    }

    $wpgv_buying_for = get_option('wpgv_buying_for') ? get_option('wpgv_buying_for') : 'both';

    $wpgv_additional_charges_text = get_option('wpgv_additional_charges_text') ? get_option('wpgv_additional_charges_text') : 'Additional Website Charges';
    $wpgv_add_extra_charges = get_option('wpgv_add_extra_charges') ? get_option('wpgv_add_extra_charges') : 0;


    if($wpgv_buying_for == 'both') 
    {
        $buying_for_html = '<div><br>
                <label>'.__('Buying For', 'gift-voucher' ).'</label>
                    <br><select name="buying_for_selectbox" id="buying_for_selectbox">
                    	<option value="someone_else">Someone Else</option>
                    	<option value="yourself">Yourself</option>
                    </select>
                
                <input type="hidden" name="buying_for" id="buying_for" value="someone_else">
            </div>
            <div class="form-group">
                <br><label for="voucherForName">'.__('Your Name', 'gift-voucher' ).' <sup></sup></label>
                <br><input type="text" name="voucherForName" id="voucherForName" class="required">
            </div>
            <div class="form-group fromname">
                <br><label for="voucherFromName">'.__('Recipient Name', 'gift-voucher' ).' <sup></sup></label>
                <br><input type="text" name="voucherFromName" id="voucherFromName" class="required">
            </div>';
    } 
    else 
    {
        if($wpgv_buying_for == 'someone_else') 
        {
            $buying_for_html = '<input type="hidden" name="buying_for" id="buying_for" value="someone_else">
            <div class="form-group">
                <br><label for="voucherForName">'.__('Your Name', 'gift-voucher' ).' <sup></sup></label>
                <br><input type="text" name="voucherForName" id="voucherForName" class="required">
            </div>
            <div class="form-group fromname">
                <br><label for="voucherFromName">'.__('Recipient Name', 'gift-voucher' ).' <sup></sup></label>
                <br><input type="text" name="voucherFromName" id="voucherFromName" class="required">
            </div>';
        } 
        else 
        {
            $buying_for_html = '<input type="hidden" name="buying_for" id="buying_for" value="yourself">
            <div class="form-group">
                <br><label for="voucherForName">'.__('Your Name', 'gift-voucher' ).' <sup></sup></label>
                <br><input type="text" name="voucherForName" id="voucherForName" class="required">
            </div>';
        }
    }

    $minVoucherValue = $setting_options->voucher_min_value ? $setting_options->voucher_min_value : 1;
    $minVoucherValueMsg = $setting_options->voucher_min_value ? sprintf(__('(Min Voucher Value %s)', 'gift-voucher'), wpgv_price_format($setting_options->voucher_min_value)) : '';
    $maxVoucherValue = $setting_options->voucher_max_value ? $setting_options->voucher_max_value : 10000;

    $shipping_methods = explode(',', $setting_options->shipping_method);
    
    $wpgv_hide_expiry = get_option('wpgv_hide_expiry') ? get_option('wpgv_hide_expiry') : 'yes';
    $wpgv_expiry_date_format = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';

    if($wpgv_hide_expiry == 'no') {
        $expiryCard = __('No Expiry', 'gift-voucher' );
    } else {
        $expiryCard = ($setting_options->voucher_expiry_type == 'days') ? date($wpgv_expiry_date_format,strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;
    }

?>
<?php
	if(isset($_GET['msg']) && $_GET['msg'] == 1 )
	{
		?>
			<div class="updated notice is-dismissible">
	        	<p><?php _e( 'Gift Voucher Order Successfully Added...', 'gift-voucher' ); ?></p>
			</div>
		<?php
	}
?>
<div class="wrap wpgiftv-settings">
	<h1><?php echo __( 'Add Gift Voucher Order', 'gift-voucher' ); ?></h1>
	<hr>
	<div class="wpgiftv-row">
		<div class="wpgiftv-col75">
			<div class="white-box add_gift_white_box">
				<form id="example-advanced-form" action="#">
					<input type="hidden" name="voucher_form_verify" value="<?php echo $nonce; ?>">
        			<input type="hidden" name="wpgv_total_price" id="total_price">
        			<input type="hidden" name="wpgv_website_commission_price" id="website_commission_price" data-price="<?php echo $wpgv_add_extra_charges; ?>">
        			<input type="hidden" name="expiryCard" id="expiryCard" value="<?php echo $expiryCard; ?>">
        			<input type="hidden" name="codeCard" id="codeCard" class="codeCard">
        			<input type="hidden" name="coupon_code_length" id="coupon_code_length" class="coupon_code_length" value="<?php echo $coupon_code_length; ?>">

				    <h3>Select Templates</h3>
				    <fieldset>
				        <legend><h3>Select Templates</h3></legend>
				 		<br>
				 		<div class="admin_select_template">
				 			<?php
							foreach ($template_options as $key => $options) 
							{
							 	$images = $options->image_style ? json_decode($options->image_style) : ['','',''];
    							$image_attributes = wp_get_attachment_image_src( $images[0], 'voucher-thumb' );
    							$image = ($image_attributes) ? $image_attributes[0] : get_option('wpgv_demoimageurl_voucher');        	

    							echo '<div class="admin_single_template">
    								<img src="'.$image.'" width="150"/>
    								<div><span>'.$options->title.'</span></label> <input data-title="'.$options->title.'" type="radio" name="template_id" value="'.$options->id.'" id="template_id'.$options->id.'" class="required"></div>
    							</div>';
							}	
						?>
				 		</div>
				    </fieldset> 

				    <h3>Personalize</h3>
				    <fieldset>
				        <legend><h3>Profile Information</h3></legend>
				 		<div>
					 	<div class="form-group">
					 		<?php echo $chooseStyle; ?>
					 	</div>
					 	<?php echo $buying_for_html; ?>
					 	<?php
					 		echo '<div class="form-group">
					               	<br><label for="voucherAmount">'.__('Voucher Value', 'gift-voucher' ).' '.$minVoucherValueMsg.'<sup></sup></label><br>
					                <div class="currency_inside_input">
					                	<span class="currencySymbol"> '.$setting_options->currency.' </span>
					                	<input type="number" step="0.01" name="voucherAmount" id="voucherAmount" class="required" min="'.$minVoucherValue.'" max="'.$maxVoucherValue.'">
					                </div>
					            </div>
					            <div class="form-group">
					                <br><label for="voucherMessage">'.__('Personal Message (Optional)', 'gift-voucher' ).' ('.__('Max: 250 Characters', 'gift-voucher' ).')</label><br>
					                <textarea name="voucherMessage" rows="4" cols="50" id="voucherMessage" maxlength="250"></textarea>
					            </div>';
					 	?>
					</div>
				    </fieldset>
				 
				    <h3>Payment</h3>
				    <fieldset>
				        <legend><h3>Payment</h3></legend>
				 		<?php
							if($setting_options->post_shipping) 
							{
						        echo '<div>
									<div>
						                <br><label><b>'.__('Shipping', 'gift-voucher' ).'</b></label>
						                <select name="shipping_selectbox" id="shipping_selectbox">
                    						<option value="shipping_as_email"> Email </option>
                    						<option value="shipping_as_post"> Post </option>
                    					</select>
						                <input type="hidden" name="shipping" id="shipping" value="shipping_as_email">
						            </div>
						            <div class="form-group" id="wpgv-shipping_email">
						                <br><label for="shipping_email">'.__('What email address should we send it to?', 'gift-voucher' ).'</label>
						                <br><input type="email" name="shipping_email" id="shipping_email" class="form-field required">
						            </div>
						            <div class="form-group">
						                <br><label for="voucherEmail">'.__('Your email address (for the receipt)', 'gift-voucher' ).'</label>
						                <br><input type="email" name="voucherEmail" id="voucherEmail" class="form-field required">
						            </div>
						            <div class="shipping_as_post_fields" style="display:none">
							            <div class="form-group wpgv-post-data">
							                <br><label for="voucherFirstName">'.__('First Name', 'gift-voucher' ).' <sup>*</sup></label>
							                <br><input type="text" name="voucherFirstName" id="voucherFirstName" class="required">
							            </div>
							            <div class="form-group wpgv-post-data">
							                <br><label for="voucherLastName">'.__('Last Name', 'gift-voucher' ).' <sup>*</sup></label>
							                <br><input type="text" name="voucherLastName" id="voucherLastName" class="required">
							            </div>
							            <div class="form-group wpgv-post-data">
							                <br><label for="voucherAddress">'.__('Address', 'gift-voucher' ).' <sup>*</sup></label>
							                <br><input type="text" name="voucherAddress" id="voucherAddress" class="required">
							            </div>
							            <div class="form-group wpgv-post-data">
							                <br><label for="voucherPincode">'.__('Postcode', 'gift-voucher' ).'</label>
							                <br><input type="text" name="voucherPincode" id="voucherPincode">
							            </div>';
							        ?>
							        	<div class="form-group wpgv-post-data" id="wpgv-shipping_method">
							                <br><label id="shipping_method">Shipping method</label><br>
											<?php
												$shipping_methods_counter = 0;
											    foreach ($shipping_methods as $method) 
											    {
											    	$shipping_methods_counter++;
											        $shipping_method = explode(':', $method);
											        ?>
											        	<input <?php if($shipping_methods_counter == 1){ echo "checked"; } ?> type="radio" data-price="<?php echo trim($shipping_method[0]); ?>"  name="shipping_method" value="<?php echo trim($shipping_method[1]); ?>" name="shipping_method"><?php echo trim(stripslashes($shipping_method[1]))."<br>";  ?>
											        <?php
											        /*$shipping_methods_string .= '<br><input data-price="'.trim($shipping_method[0]).'" type="radio" name="shipping_method" value="'.trim(stripslashes($shipping_method[1])).'"> '.trim(stripslashes($shipping_method[1]));*/
											    }
											?>						                
							            </div>
							        </div>
							        <?php
						        } 
						        else 
						        {
						        echo '<div>
						            <input type="hidden" name="shipping" id="shipping" value="shipping_as_email">
						            <div class="form-group" id="wpgv-shipping_email">
						                <br><label for="shipping_email">'.__('What email address should we send it to?', 'gift-voucher' ).'</label>
						                <br><input type="email" name="shipping_email" id="shipping_email" class="form-field required">
						            </div>
						            <div class="form-group">
						                <br><label for="voucherEmail">'.__('Your email address (for the receipt)', 'gift-voucher' ).'</label>
						                <br><input type="email" name="voucherEmail" id="voucherEmail" class="form-field required">
						            </div></div>';

						        }

						        echo '<div class="order_details_preview">
						                <br><h3>'.__('Your Order', 'gift-voucher').'</h3>
						                <div class="wpgv_preview_box">
						                    <div>
						                        <h4 class="wpgv-itemtitle">-</h4>
						                        <span>'.__('Your Name', 'gift-voucher').': <i id="autoyourname"></i></span>
						                    </div>
						                    '.(($setting_options->currency_position == 'Left') ? '<div id="itemprice">'.$setting_options->currency.' <span></span> </div>' : '<div id="itemprice"> <span></span> '.$setting_options->currency.'</div>').'
						                </div>
						                <div class="wpgv_shipping_box" style="display:none">
						                    <div>
						                        <h4>'.__('Shipping', 'gift-voucher').'</h4>
						                    </div>
						                    '.(($setting_options->currency_position == 'Left') ? '<div id="shippingprice">'.$setting_options->currency.' <span></span> </div>' : '<div id="shippingprice"> <span></span> '.$setting_options->currency.'</div>').'
						                </div>
						                '.( $wpgv_add_extra_charges ? '<div class="wpgv_commission_box"><div><h4>'.$wpgv_additional_charges_text.'</h4></div><div id="commissionprice">'.wpgv_price_format($wpgv_add_extra_charges).'</div></div>' : '').'
						                <div class="wpgv_total_box">
						                    <div>
						                        <h4><b>'.__('Total', 'gift-voucher').'</b></h4>
						                    </div>
						                    '.(($setting_options->currency_position == 'Left') ? '<div id="totalprice"><b>'.$setting_options->currency.' <span></span> </b></div>' : '<div id="totalprice"><b> <span></span> '.$setting_options->currency.'</b></div>').'
						                </div>
						            </div>';
						?>
				    </fieldset>
				 
				    <h3>Overview</h3>
				    <fieldset>
				        <legend><h3>Overview</h3></legend>
				 		<?php
				 			echo '
				 				<div>
				                <div>
				                    <br><div class="labelInfo">'.__('Voucher Value', 'gift-voucher' ).'</div>
				                    '.(($setting_options->currency_position == 'Left') ? '<div class="voucherAmountInfo">'.$setting_options->currency.' <b></b> </div>' : '<div class="voucherAmountInfo"> <b></b> '.$setting_options->currency.'</div>').'
				                </div>
				                <div class="half">
				                    <br><div class="labelInfo">'.__('Your Name', 'gift-voucher' ).'</div>
				                    <div><b class="voucherYourNameInfo"></b></div>
				                </div>
				                <div class="half voucherrecipientname">
				                    <br><div class="labelInfo">'.__('Recipient Name', 'gift-voucher' ).'</div>
				                    <div><b class="voucherReceiverInfo"></b></div>
				                </div>
				                <div class="full">
				                    <br><div class="labelInfo">'.__('Personal Message', 'gift-voucher' ).'</div>
				                    <div><b class="voucherMessageInfo"></b></div>
				                </div>
				                <div class="clearfix"></div>
				                <hr>
				                <div class="full">
				                    <br><div class="labelInfo">'.__('Shipping', 'gift-voucher' ).'</div>
				                    <div><b class="voucherShippingInfo">Shipping vai Email</b></div>
				                </div>
				                <div class="full shippingasemail">
				                    <br><div class="labelInfo">'.__('What email address should we send it to?', 'gift-voucher' ).'</div>
				                    <div><b class="voucherShippingEmailInfo"></b></div>
				                </div>
				                <div class="full">
				                    <br><div class="labelInfo">'.__('Your email address (for the receipt)', 'gift-voucher' ).'</div>
				                    <div><b class="voucherEmailInfo"></b></div>
				                </div>
				                <div class="shipping_as_post_fields" style="display:none;">
					                <div class="half shippingaspost">
					                    <br><div class="labelInfo">'.__('First Name', 'gift-voucher' ).'</div>
					                    <div><b class="voucherFirstNameInfo"></b></div>
					                </div>
					                <div class="half shippingaspost">
					                    <br><div class="labelInfo">'.__('Last Name', 'gift-voucher' ).'</div>
					                    <div><b class="voucherLastNameInfo"></b></div>
					                </div>
					                <div class="full shippingaspost">
					                    <br><div class="labelInfo">'.__('Address', 'gift-voucher' ).'</div>
					                    <div><b class="voucherAddressInfo"></b></div>
					                </div>
					                <div class="full shippingaspost">
					                    <br><div class="labelInfo">'.__('Postcode', 'gift-voucher' ).'</div>
					                    <div><b class="voucherPincodeInfo"></b></div>
					                </div>
					                <div class="full shippingaspost">
					                    <br><div class="labelInfo">'.__('Shipping method', 'gift-voucher' ).'</div>
					                    <div><b class="voucherShippingMethodInfo"></b></div>
					                </div>
				                </div
				                <hr>
				          
				                <br>
				                	<center><button type="button" id="voucherPaymentButton" class="voucherPaymentButton"  name="finalPayment">
				                		'.__('Confirm Order', 'gift-voucher' ).' 
				                	</button></center>
				            </div> '; 
				        ?>
				        <!-- <input id="acceptTerms-2" name="acceptTerms" type="checkbox" class="required"> <label for="acceptTerms-2">I agree with the Terms and Conditions.</label> -->
				    </fieldset>
				</form>
			</div>
		</div>
	</div>
</div>
