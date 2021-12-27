<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

//Add Voucher Shortcode
function wpgv_voucher_shortcode() 
{
    global $wp, $wpdb;
    $html = '';
    $find = array( 'http://', 'https://' );
    $replace = '';
    $siteURL = str_replace( $find, $replace, get_site_url() );
    $voucher_table  = $wpdb->prefix . 'giftvouchers_list';
    $setting_table  = $wpdb->prefix . 'giftvouchers_setting';
    $template_table = $wpdb->prefix . 'giftvouchers_template';
    $invoice_setting_table = $wpdb->prefix . 'giftvouchers_invoice_settings';
    require_once( WPGIFT__PLUGIN_DIR .'/include/PayPalAuth.php');
    wp_enqueue_style('wpgv-voucher-style');
    // wp_enqueue_style('wpgv-bootstrap-css');
    wp_enqueue_style('wpgv-bootstrap-datetimepicker-css');
    // wp_enqueue_script('wpgv-bootstrap-js');
    wp_enqueue_script('wpgv-bootstrap-datetimepicker-js');
    wp_enqueue_script('wpgv-jquery-validate');
    wp_enqueue_script('wpgv-jquery-steps');
    wp_enqueue_script('wpgv-stripe-js');
    wp_enqueue_script('wpgv-paypal-js');
    wp_enqueue_script('wpgv-voucher-script');
    $wpgv_termstext = get_option('wpgv_termstext') ? get_option('wpgv_termstext') : 'I hereby accept the terms and conditions, the revocation of the privacy policy and confirm that all information is correct.';

    $setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
    $wpgv_hide_first_step = (get_option('wpgv_hide_first_step')) ? get_option('wpgv_hide_first_step') : 0;
    if($wpgv_hide_first_step == 1){
        $template_options = $wpdb->get_results( "SELECT * FROM $template_table WHERE active = 1 ORDER BY id DESC LIMIT 1");
    }
    else{
        $template_options = $wpdb->get_results( "SELECT * FROM $template_table WHERE active = 1" );
    }
    $invoice_options = $wpdb->get_row( "SELECT * FROM $invoice_setting_table WHERE id = 1" );

    $nonce = wp_create_nonce( 'voucher_form_verify' );
    $wpgv_custom_css = get_option('wpgv_custom_css') ? stripslashes(trim(get_option('wpgv_custom_css'))) : '';
    
    $wpgv_multisafepay = get_option('wpgv_multisafepay') ? get_option('wpgv_multisafepay') : 0;
    $wpgv_paypal_alternative_text = get_option('wpgv_paypal_alternative_text') ? get_option('wpgv_paypal_alternative_text') : 'PayPal';
    $wpgv_stripe_alternative_text = get_option('wpgv_stripe_alternative_text') ? get_option('wpgv_stripe_alternative_text') : 'Stripe';
    $wpgv_multisafepay_alternative_text = get_option('wpgv_multisafepay_alternative_text') ? get_option('wpgv_multisafepay_alternative_text') : 'MultiSafepay';
    
    $wpgv_paypal_alternative_text = get_option('wpgv_paypal_alternative_text') ? get_option('wpgv_paypal_alternative_text') : 'PayPal';
    $wpgv_stripe_alternative_text = get_option('wpgv_stripe_alternative_text') ? get_option('wpgv_stripe_alternative_text') : 'Stripe';
        
    $wpgv_buying_for = get_option('wpgv_buying_for') ? get_option('wpgv_buying_for') : 'both';

    $wpgv_additional_charges_text = get_option('wpgv_additional_charges_text_voucher') ? get_option('wpgv_additional_charges_text_voucher') : 'Additional Website Charges';
    $wpgv_add_extra_charges = get_option('wpgv_add_extra_charges_voucher') ? get_option('wpgv_add_extra_charges_voucher') : 0;

    $wpgv_leftside_notice = (get_option('wpgv_leftside_notice') != '') ? get_option('wpgv_leftside_notice') : __('Cash payment is not possible. The terms and conditions apply.', 'gift-voucher' );
    if($wpgv_buying_for == 'both') {
        $buying_for_html = '<div class="buying-for flex-field">
                <label>'.__('Buying For', 'gift-voucher' ).'</label>
                <div class="buying-options">
                    <div class="someone_else selected" data-value="someone_else">
                        <img src="'.WPGIFT__PLUGIN_URL.'/assets/img/giftbox.png">
                        <span>'.__('Someone Else', 'gift-voucher' ).'</span>
                    </div>
                    <div class="yourself" data-value="yourself">
                        <img src="'.WPGIFT__PLUGIN_URL.'/assets/img/users.png">
                        <span>'.__('Yourself', 'gift-voucher' ).'</span>
                    </div>
                </div>
                <input type="hidden" name="buying_for" id="buying_for" value="someone_else">
            </div>
            <div class="form-group">
                <label for="voucherForName">'.__('Your Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherForName" id="voucherForName" class="required">
            </div>
            <div class="form-group fromname">
                <label for="voucherFromName">'.__('Recipient Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherFromName" id="voucherFromName" class="required">
            </div>';
    } else {
        if($wpgv_buying_for == 'someone_else') {
            $buying_for_html = '<input type="hidden" name="buying_for" id="buying_for" value="someone_else">
            <div class="form-group">
                <label for="voucherForName">'.__('Your Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherForName" id="voucherForName" class="required">
            </div>
            <div class="form-group fromname">
                <label for="voucherFromName">'.__('Recipient Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherFromName" id="voucherFromName" class="required">
            </div>';
        } else {
            $buying_for_html = '<input type="hidden" name="buying_for" id="buying_for" value="yourself">
            <div class="form-group">
                <label for="voucherForName">'.__('Your Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherForName" id="voucherForName" class="required">
            </div>';
        }
    }

    $wpgv_hide_price = get_option('wpgv_hide_price_voucher') ? get_option('wpgv_hide_price_voucher') : 0;

    $voucher_value_html = (!$wpgv_hide_price) ? '<div class="voucherValueForm">
                        <label>'.__('Voucher Value', 'gift-voucher' ).'</label>
                        <span class="currencySymbol"> '.$setting_options->currency.' </span>
                        <input type="text" name="voucherValueCard" class="voucherValueCard" readonly>
                    </div>' : '';

    $wpgv_allow_future_date = get_option('wpgv_allow_future_date') ? get_option('wpgv_allow_future_date') : 0;

    $wpgv_allow_future_date_html = ($wpgv_allow_future_date) ? '<div class="form-group">
                <label for="voucherSendType">'.__('When do you want to send a gift voucher?', 'gift-voucher' ).'</label>
                <select name="voucherSendType" id="voucherSendType" class="required">
                    <option value="send_instantly">'.__('Send instantly', 'gift-voucher' ).'</option>
                    <option value="send_future_date">'.__('Send on a future date', 'gift-voucher' ).'</option>
                </select>
            </div>
            <div class="form-group voucherSendDatePikerRow">
                <label for="voucherSendDatePiker">'.__('Select date and time', 'gift-voucher' ).'</label>
                <div class="input-group date voucherSendDatePiker" data-date="" data-date-format="dd MM yyyy - HH:ii p" data-link-field="voucherSendDate">
                    <input class="form-control" size="16" type="text" value="" readonly>
                    <span class="input-group-addon"><span class="glyphicon-th date-add-icon" title="'.__('Add date and time', 'gift-voucher' ).'"></span></span>
                    <span class="input-group-addon"><span class="glyphicon-remove date-remove-icon" title="'.__('Remove date and time', 'gift-voucher' ).'"></span></span>
                </div>
                <input type="hidden" id="voucherSendDate" value="" /><br/>
            </div>' : '<input type="hidden" id="voucherSendType" value="send_instantly">';

    $voucher_brcolor = get_option('wpgv_voucher_border_color') ? get_option('wpgv_voucher_border_color') : '81c6a9';
    $voucher_bgcolor = $setting_options->voucher_bgcolor;
    $voucher_color = $setting_options->voucher_color;

    $minVoucherValue = $setting_options->voucher_min_value ? $setting_options->voucher_min_value : 1;
    $minVoucherValueMsg = $setting_options->voucher_min_value ? sprintf(__('(Min Voucher Value %s)', 'gift-voucher'), wpgv_price_format($setting_options->voucher_min_value)) : '';
    $maxVoucherValue = $setting_options->voucher_max_value ? $setting_options->voucher_max_value : 10000;
    $custom_loader = $setting_options->custom_loader ? $setting_options->custom_loader : WPGIFT__PLUGIN_URL.'/assets/img/loader.gif';

    $shipping_methods = explode(',', $setting_options->shipping_method);
    $shipping_methods_string = '';
    foreach ($shipping_methods as $method) {
        if($method != ''){
            $shipping_method = explode(':', $method);
            $shipping_methods_string .= '<label data-value="'.trim($shipping_method[0]).'"><input type="radio" name="shipping_method" value="'.trim(stripslashes($shipping_method[1])).'" class="radio-field"> '.trim(stripslashes($shipping_method[1])).'</label>';
        }
    }

    $html .= '<style type="text/css">
        #voucher-multistep-form .secondRightDiv .cardDiv{
            background-color: #'.$voucher_bgcolor.'!important;
        }
        #voucher-multistep-form.wizard>.steps .done a,
        #voucher-multistep-form.wizard>.steps .done a:hover,
        #voucher-multistep-form.wizard>.steps .done a:active,
        #voucher-multistep-form.wizard>.actions a,
        #voucher-multistep-form.wizard>.actions a:hover,
        #voucher-multistep-form.wizard>.actions a:active,
        #voucher-multistep-form .voucherPreviewButton button,
        #voucher-multistep-form #voucherPaymentButton,
        #voucher-multistep-form .sin-template input[type="radio"]:checked:before,
        .buying-options div.selected, .shipping-options div.selected {
            background-color: #'.$voucher_brcolor.'!important;
        }
        #voucher-multistep-form .content .voucherform .form-group input[type="text"],
        #voucher-multistep-form .content .form-group input[type="email"],
        #voucher-multistep-form .content .form-group input[type="tel"],
        #voucher-multistep-form .content .form-group input[type="number"],
        #voucher-multistep-form .content .form-group select,
        #voucher-multistep-form .content .form-group textarea,
        #voucher-multistep-form .content .sin-template label.selectImage {
            border-color: #'.$voucher_brcolor.'!important;
        }
        #voucher-multistep-form .paymentUserInfo .full,
        #voucher-multistep-form .paymentUserInfo .half,
        #voucher-multistep-form .secondRightDiv .voucherBottomDiv h2,
        #voucher-multistep-form .voucherBottomDiv .termsCard,
        #voucher-multistep-form .voucherBottomDiv .voucherSiteInfo a {
            color: #'.$voucher_color.'!important;
        }
        #voucher-multistep-form.wizard>.content>.body .voucherBottomDiv label{
            color:  #'.$voucher_color.'!important;
        }
        #voucher-multistep-form.wizard>.content>.body.loading.current:after {
            content: url('.$custom_loader.') !important;
        }
    </style>';

    $chooseStyle = '';
    if ($setting_options->is_style_choose_enable) {
        $voucher_styles = json_decode($setting_options->voucher_style);
        $chooseStyle = '<label for="chooseStyle">'.__('Choose Voucher Style', 'gift-voucher' ).' <sup>*</sup></label><select name="chooseStyle" id="chooseStyle" class="required">';
        foreach ($voucher_styles as $key => $value) {
            $chooseStyle .= '<option value="'.$value.'">'.__('Style', 'gift-voucher').' '.($value+1).'</option>';
        }
        $chooseStyle .= '</select>';
    }

    $paymenyGateway = __('Payment Method');
    if($setting_options->paypal || $setting_options->sofort || $setting_options->stripe || $setting_options->per_invoice || $wpgv_multisafepay){
        $paymenyGateway = '<select name="voucherPayment" id="voucherPayment" class="required">';
        $paymenyGateway .= $setting_options->paypal ? '<option value="Paypal">'.$wpgv_paypal_alternative_text.'</option>' : '';
        $paymenyGateway .= $setting_options->sofort ? '<option value="Sofort">'.__('Sofort', 'gift-voucher').'</option>' : '';
        $paymenyGateway .= $setting_options->stripe ? '<option value="Stripe">'.$wpgv_stripe_alternative_text.'</option>' : '';
        $paymenyGateway .= $wpgv_multisafepay ? '<option value="MultiSafepay">'.$wpgv_multisafepay_alternative_text.'</option>' : '';
        $paymenyGateway .= $setting_options->per_invoice ? '<option value="Per Invoice">'.__('Per Invoice', 'gift-voucher').'</option>' : '';
        $paymenyGateway .= '</select>';
    }
    
    $paymentCount = $setting_options->paypal + $setting_options->sofort + $setting_options->stripe + $wpgv_multisafepay + $setting_options->per_invoice;

    $wpgv_hide_expiry = get_option('wpgv_hide_expiry') ? get_option('wpgv_hide_expiry') : 'yes';
    $wpgv_expiry_date_format = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';

    if($wpgv_hide_expiry == 'no') {
        $expiryCard = __('No Expiry', 'gift-voucher' );
    } else {
        $expiryCard = ($setting_options->voucher_expiry_type == 'days') ? date($wpgv_expiry_date_format,strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;
    }
    $coupon_code_length = get_option('wpgv_coupon_code_length') ? get_option('wpgv_coupon_code_length') : 12;
    $html .= '<form name="voucherform" id="voucher-multistep-form" action="'.home_url( $wp->request ).'" enctype="multipart/form-data">
        <input type="hidden" name="coupon_code_length" id="coupon_code_length" value="'.$coupon_code_length.'">
        <input type="hidden" name="voucher_form_verify" value="'.$nonce.'">
        <input type="hidden" name="wpgv_total_price" id="total_price">
        <input type="hidden" name="wpgv_website_commission_price" id="website_commission_price" data-price="'.$wpgv_add_extra_charges.'">';

    if($wpgv_hide_first_step == 1){
        foreach ($template_options as $key => $options) {
            $template_id = $options->id;
            $images = $options->image_style ? json_decode($options->image_style) : ['','',''];
            $image_attributes = wp_get_attachment_image_src( $images[0], 'voucher-medium' );
            $image1 = ($image_attributes) ? $image_attributes[0] : get_option('wpgv_demoimageurl_voucher');
            $image2_attributes = wp_get_attachment_image_src( $images[1], 'voucher-medium' );
            $image2 = ($image2_attributes) ? $image2_attributes[0] : get_option('wpgv_demoimageurl_voucher');
            $image3_attributes = wp_get_attachment_image_src( $images[2], 'voucher-medium' );
            $image3 = ($image3_attributes) ? $image3_attributes[0] : get_option('wpgv_demoimageurl_voucher');
            $title = $options->title;
        }
        $html .= '<input type="hidden" name="template_id" id="template_id" value="'.$template_id.'">';
        $html .= '<input type="hidden" name="hide_first_step" id="hide_first_step" value="1">';
    }
    else{
        $html .= '<h3>'.__('Select Templates', 'gift-voucher' ).'</h3>
        <fieldset>
            <legend>'.__('Select Templates', 'gift-voucher' ).'</legend><div class="voucher-row">';
            foreach ($template_options as $key => $options) {
                $images = $options->image_style ? json_decode($options->image_style) : ['','',''];
                $image_attributes = wp_get_attachment_image_src( $images[0], 'voucher-thumb' );
                $image = ($image_attributes) ? $image_attributes[0] : get_option('wpgv_demoimageurl_voucher');
                $html .= '<div class="vouchercol'.$setting_options->template_col.'"><div class="sin-template"><label for="template_id'.$options->id.'"><img src="'.$image.'" width="'.$image_attributes[1].'"/><span>'.$options->title.'</span></label><input type="radio" name="template_id" value="'.$options->id.'" id="template_id'.$options->id.'" class="required"></div></div>';
            }
            $html .= '</div></fieldset>';
            $html .= '<input type="hidden" name="hide_first_step" id="hide_first_step" value="0">';
            $image1 = $image2 = $image3 = get_option('wpgv_demoimageurl_voucher');
            $title = __('Gift Voucher', 'gift-voucher' );
    }

    $voucherstyle1 = '<div class="sideview secondRight secondRightDiv voucherstyle1">
        <div class="cardDiv">
            <div class="cardImgTop">
                <img class="uk-thumbnail" src="'.$image1.'">
            </div>
            <div class="voucherBottomDiv">
                <h2>'.$title.'</h2>
                <div class="uk-form-row">
                    <div class="nameFormLeft">
                        <label>'.__('Your Name', 'gift-voucher' ).'</label>
                        <input type="text" name="forNameCard" class="forNameCard" readonly>
                    </div>
                    <div class="nameFormRight">
                        <label>'.__('Recipient Name', 'gift-voucher' ).'</label>
                        <input type="text" name="fromNameCard" class="fromNameCard" readonly>
                    </div>
                    '.$voucher_value_html.'
                    <div class="messageForm">
                        <label>'.__('Personal Message', 'gift-voucher' ).'</label>
                        <textarea name="personalMessageCard" class="personalMessageCard" readonly></textarea>
                    </div>
                    <div class="expiryFormLeft">
                        <label>'.__('Date of Expiry', 'gift-voucher' ).'</label>
                        <input type="text" name="expiryCard" class="expiryCard" value="'.$expiryCard.'" readonly>
                    </div>
                    <div class="codeFormRight">
                        <label>'.__('Coupon Code', 'gift-voucher' ).'</label>
                        <input type="text" name="codeCard" class="codeCard" readonly>
                    </div>
                    <div class="clearfix"></div>
                    <div class="voucherSiteInfo"><a href="'.$setting_options->pdf_footer_url .'">'.$setting_options->pdf_footer_url.'</a> | <a href="mailto:'.$setting_options->pdf_footer_email.'">'.$setting_options->pdf_footer_email.'</a></div>
                    <div class="termsCard">* '.$wpgv_leftside_notice.'</div>
                </div></div></div>
        </div>';

    $voucherstyle2 = '<div class="sideview secondRight secondRightDiv voucherstyle2">
        <div class="cardDiv">
            <div class="voucherBottomDiv">
                <div class="cardImgTop">
                    <img class="uk-thumbnail" src="'.$image2.'">
                </div>
                <div class="sidedetails">
                    <h2>'.$title.'</h2>
                    <div class="nameFormLeft">
                        <label>'.__('Your Name', 'gift-voucher' ).'</label>
                        <input type="text" name="forNameCard" class="forNameCard" readonly>
                    </div>
                    <div class="nameFormRight">
                        <label>'.__('Recipient Name', 'gift-voucher' ).'</label>
                        <input type="text" name="fromNameCard" class="fromNameCard" readonly>
                    </div>
                    '.$voucher_value_html.'
                </div>
                <div class="uk-form-row">
                    <div class="messageForm">
                        <label>'.__('Personal Message', 'gift-voucher' ).'</label>
                        <textarea name="personalMessageCard" class="personalMessageCard" readonly></textarea>
                    </div>
                    <div class="expiryFormLeft">
                        <label>'.__('Date of Expiry', 'gift-voucher' ).'</label>
                        <input type="text" name="expiryCard" class="expiryCard" value="'.$expiryCard.'" readonly>
                    </div>
                    <div class="codeFormRight">
                        <label>'.__('Coupon Code', 'gift-voucher' ).'</label>
                        <input type="text" name="codeCard" class="codeCard" readonly>
                    </div>
                    <div class="clearfix"></div>
                    <div class="voucherSiteInfo"><a href="'.$setting_options->pdf_footer_url .'">'.$setting_options->pdf_footer_url.'</a> | <a href="mailto:'.$setting_options->pdf_footer_email.'">'.$setting_options->pdf_footer_email.'</a></div>
                    <div class="termsCard">* '.$wpgv_leftside_notice.'</div>
                </div></div></div>
        </div>';

    $voucherstyle3 = '<div class="sideview secondRight secondRightDiv voucherstyle3">
        <div class="cardDiv">
            <div class="voucherBottomDiv">
                <h2>'.$title.'</h2>
                <div class="cardImgTop">
                    <img class="uk-thumbnail" src="'.$image3.'">
                </div>
                <div class="sidedetails">
                    <div class="nameFormLeft">
                        <label>'.__('Your Name', 'gift-voucher' ).'</label>
                        <input type="text" name="forNameCard" class="forNameCard" readonly>
                    </div>
                    <div class="nameFormRight">
                        <label>'.__('Recipient Name', 'gift-voucher' ).'</label>
                        <input type="text" name="fromNameCard" class="fromNameCard" readonly>
                    </div>
                    '.$voucher_value_html.'
                </div>
                <div class="uk-form-row">
                    <div class="messageForm">
                        <label>'.__('Personal Message', 'gift-voucher' ).'</label>
                        <textarea name="personalMessageCard" class="personalMessageCard" readonly></textarea>
                    </div>
                    <div class="expiryFormLeft">
                        <label>'.__('Date of Expiry', 'gift-voucher' ).'</label>
                        <input type="text" name="expiryCard" class="expiryCard" value="'.$expiryCard.'" readonly>
                    </div>
                    <div class="codeFormRight">
                        <label>'.__('Coupon Code', 'gift-voucher' ).'</label>
                        <input type="text" name="codeCard" class="codeCard" readonly>
                    </div>
                    <div class="clearfix"></div>
                    <div class="voucherSiteInfo"><a href="'.$setting_options->pdf_footer_url .'">'.$setting_options->pdf_footer_url.'</a> | <a href="mailto:'.$setting_options->pdf_footer_email.'">'.$setting_options->pdf_footer_email.'</a></div>
                    <div class="termsCard">* '.$wpgv_leftside_notice.'</div>
                </div></div></div>
        </div>';

    $voucherstyle = '';
    if ($setting_options->is_style_choose_enable) { 
        $voucher_styles = json_decode($setting_options->voucher_style);
        foreach ($voucher_styles as $key => $value) {
            $voucherstyle .= ${'voucherstyle'.($value+1)};
        }
    } else {
        switch ($setting_options->voucher_style) {
            case 0:
                $voucherstyle = $voucherstyle1;
                break;
            case 1:
                $voucherstyle = $voucherstyle2;
                break;
            case 2:
                $voucherstyle = $voucherstyle3;
                break;
            default:
                $voucherstyle = $voucherstyle1;
                break;
        }
    }

    $html .= '<h3>'.__('Personalize', 'gift-voucher' ).'</h3>
    <fieldset>
        <legend>'.__('Personalize', 'gift-voucher' ).'</legend><div class="voucher-row">
        <div class="voucherform secondLeft">
            <div class="form-group">
                '.$chooseStyle.'
            </div>
            '.$buying_for_html.'
            <div class="form-group">
                <label for="voucherAmount">'.__('Voucher Value', 'gift-voucher' ).' '.$minVoucherValueMsg.'<sup>*</sup></label>
                <span class="currencySymbol"> '.$setting_options->currency.' </span>
                <input type="number" step="0.01" name="voucherAmount" id="voucherAmount" class="required" min="'.$minVoucherValue.'" max="'.$maxVoucherValue.'">
            </div>
            <div class="form-group">
                <label for="voucherMessage">'.__('Personal Message (Optional)', 'gift-voucher' ).' ('.__('Max: 250 Characters', 'gift-voucher' ).')</label>
                <textarea name="voucherMessage" id="voucherMessage" maxlength="250"></textarea>
                <div class="maxchar"></div>
            </div>
        </div>
        '.$voucherstyle.'
        </div>
    </fieldset>
 
    <h3>'.__('Payment', 'gift-voucher' ).'</h3>
    <fieldset>
        <legend>'.__('Payment', 'gift-voucher' ).'</legend><div class="voucher-row">';

    if($setting_options->post_shipping) {
        $html .= '<div class="voucherform secondLeft">
            <div class="shipping flex-field">
                <label><b>'.__('Shipping', 'gift-voucher' ).'</b></label>
                <div class="shipping-options">
                    <div class="shipping_as_email selected" data-value="shipping_as_email">
                        <img src="'.WPGIFT__PLUGIN_URL.'/assets/img/envelope.png">
                        <span>'.__('Email', 'gift-voucher' ).'</span>
                    </div>
                    <div class="shipping_as_post" data-value="shipping_as_post">
                        <img src="'.WPGIFT__PLUGIN_URL.'/assets/img/delivery-truck.png">
                        <span>'.__('Post', 'gift-voucher' ).'</span>
                    </div>
                </div>
                <input type="hidden" name="shipping" id="shipping" value="shipping_as_email">
            </div>
            <div class="form-group" id="wpgv-shipping_email">
                <label for="shipping_email">'.__('Send the voucher to recipient email here', 'gift-voucher' ).'</label>
                <input type="email" name="shipping_email" id="shipping_email" class="form-field required">
            </div>
            <div class="form-group">
                <label for="voucherEmail">'.__('Your email address (for the receipt)', 'gift-voucher' ).'</label>
                <input type="email" name="voucherEmail" id="voucherEmail" class="form-field required">
            </div>
            '.$wpgv_allow_future_date_html.'
            <div class="form-group wpgv-post-data">
                <label for="voucherFirstName">'.__('First Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherFirstName" id="voucherFirstName" class="required">
            </div>
            <div class="form-group wpgv-post-data">
                <label for="voucherLastName">'.__('Last Name', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherLastName" id="voucherLastName" class="required">
            </div>
            <div class="form-group wpgv-post-data">
                <label for="voucherAddress">'.__('Address', 'gift-voucher' ).' <sup>*</sup></label>
                <input type="text" name="voucherAddress" id="voucherAddress" class="required">
            </div>
            <div class="form-group wpgv-post-data">
                <label for="voucherPincode">'.__('Postcode', 'gift-voucher' ).'</label>
                <input type="text" name="voucherPincode" id="voucherPincode">
            </div>
            <div class="form-group wpgv-post-data" id="wpgv-shipping_method">
                <label id="shipping_method">'.__('Shipping method', 'gift-voucher' ).'</label>
                '.$shipping_methods_string.'
            </div>';
        } else {
        $html .= '<div class="voucherform secondLeft">
            <input type="hidden" name="shipping" id="shipping" value="shipping_as_email">
            <div class="form-group" id="wpgv-shipping_email">
                <label for="shipping_email">'.__('Send the voucher to recipient email here', 'gift-voucher' ).'</label>
                <input type="email" name="shipping_email" id="shipping_email" class="form-field required">
            </div>
            <div class="form-group">
                <label for="voucherEmail">'.__('Your email address (for the receipt)', 'gift-voucher' ).'</label>
                <input type="email" name="voucherEmail" id="voucherEmail" class="form-field required">
            </div>
            '.$wpgv_allow_future_date_html.'';
        }
        
        $html .= '<div class="form-group paymentcount'.$paymentCount.'">
                    <label for="voucherPayment">'.__('Payment Method', 'gift-voucher' ).' <sup>*</sup></label>'.$paymenyGateway.'
                </div>';

        if($invoice_options->is_invoice_active == 1){
            $html .= '<div class="form-group">
                    <label for="sellerCompanyName">'.__('Your Company Name (for Invoice)', 'gift-voucher' ).'</label>
                    <input type="text" name="sellerCompanyName" id="sellerCompanyName" class="form-field required">
                </div>

                <div class="form-group">
                    <label for="sellerAddressLine1">'.__('Address Line 1', 'gift-voucher' ).'</label>
                    <input type="text" name="sellerAddressLine1" id="sellerAddressLine1" class="form-field">
                </div>
                <div class="form-group">
                    <label for="sellerAddressLine2">'.__('Address Line 2', 'gift-voucher' ).'</label>
                    <input type="text" name="sellerAddressLine2" id="sellerAddressLine2" class="form-field">
                </div>
                <div class="form-group">
                    <label for="sellerAddressLine3">'.__('Address Line 3', 'gift-voucher' ).'</label>
                    <input type="text" name="sellerAddressLine3" id="sellerAddressLine3" class="form-field">
                </div>
                <div class="form-group">
                    <label for="sellerAddressLine4">'.__('Address Line 4', 'gift-voucher' ).'</label>
                    <input type="text" name="sellerAddressLine4" id="sellerAddressLine4" class="form-field">
                </div>                
                ';
        }

        $html .= '<div class="order_details_preview">
                <h3>'.__('Your Order', 'gift-voucher').'</h3>
                <div class="wpgv_preview_box">
                    <div>
                        <h4 class="wpgv-itemtitle">'.(($wpgv_hide_first_step == 1) ? $title : '-').'</h4>
                        <span>'.__('Your Name', 'gift-voucher').': <i id="autoyourname"></i></span>
                    </div>
                    '.(($setting_options->currency_position == 'Left') ? '<div id="itemprice">'.$setting_options->currency.' <span></span> </div>' : '<div id="itemprice"> <span></span> '.$setting_options->currency.'</div>').'
                </div>
                <div class="wpgv_shipping_box">
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
            </div>
        </div>
        '.$voucherstyle.'
        </div>
    </fieldset>
 
    <h3>'.__('Overview', 'gift-voucher' ).'</h3>
    <fieldset>
        <legend>'.__('Overview', 'gift-voucher' ).'</legend><div class="voucher-row">
        <div class="voucherform secondLeft">
            <div class="paymentUserInfo">
                <div class="full">
                    <div class="labelInfo">'.__('Voucher Value', 'gift-voucher' ).'</div>
                    '.(($setting_options->currency_position == 'Left') ? '<div class="voucherAmountInfo">'.$setting_options->currency.' <span></span> </div>' : '<div class="voucherAmountInfo"> <span></span> '.$setting_options->currency.'</div>').'
                </div>
                <div class="half">
                    <div class="labelInfo">'.__('Your Name', 'gift-voucher' ).'</div>
                    <div class="voucherYourNameInfo"></div>
                </div>
                <div class="half">
                    <div class="labelInfo">'.__('Recipient Name', 'gift-voucher' ).'</div>
                    <div class="voucherReceiverInfo"></div>
                </div>
                <div class="full">
                    <div class="labelInfo">'.__('Personal Message', 'gift-voucher' ).'</div>
                    <div class="voucherMessageInfo"></div>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="full">
                    <div class="labelInfo">'.__('Shipping', 'gift-voucher' ).'</div>
                    <div class="voucherShippingInfo">'.__('Shipping via Email', 'gift-voucher').'</div>
                </div>
                <div class="full shippingasemail">
                    <div class="labelInfo">'.__('Send the voucher to recipient email here', 'gift-voucher' ).'</div>
                    <div class="voucherShippingEmailInfo"></div>
                </div>
                <div class="full">
                    <div class="labelInfo">'.__('Your email address (for the receipt)', 'gift-voucher' ).'</div>
                    <div class="voucherEmailInfo"></div>
                </div>
                '.(($wpgv_allow_future_date) ? '<div class="full">
                    <div class="labelInfo">'.__('When do you want to send a gift voucher?', 'gift-voucher' ).'</div>
                    <div class="voucherSendInfo"> '.__('Send instantly', 'gift-voucher' ).' </div>
                </div>' : '').'
                <div class="half shippingaspost">
                    <div class="labelInfo">'.__('First Name', 'gift-voucher' ).'</div>
                    <div class="voucherFirstNameInfo"></div>
                </div>
                <div class="half shippingaspost">
                    <div class="labelInfo">'.__('Last Name', 'gift-voucher' ).'</div>
                    <div class="voucherLastNameInfo"></div>
                </div>
                <div class="full shippingaspost">
                    <div class="labelInfo">'.__('Address', 'gift-voucher' ).'</div>
                    <div class="voucherAddressInfo"></div>
                </div>
                <div class="full shippingaspost">
                    <div class="labelInfo">'.__('Postcode', 'gift-voucher' ).'</div>
                    <div class="voucherPincodeInfo"></div>
                </div>
                <div class="full shippingaspost">
                    <div class="labelInfo">'.__('Shipping method', 'gift-voucher' ).'</div>
                    <div class="voucherShippingMethodInfo"></div>
                </div>
                <div class="full paymentcount'.$paymentCount.'">
                    <div class="labelInfo">'.__('Payment Method', 'gift-voucher' ).'</div>
                    <div class="voucherPaymentInfo"></div>
                </div>
                <hr>
                <div class="acceptVoucherTerms">
                    <label class="aaaa"><input type="checkbox" class="required" name="acceptVoucherTerms"> '.stripslashes($wpgv_termstext).'</label>
                </div>
                <div class="voucherNote">'.$setting_options->voucher_terms_note.'</div>
                <button type="button" id="voucherPaymentButton" class="" name="finalPayment">'.__('Pay Now', 'gift-voucher' ).' - '.( ($setting_options->currency_position == 'Left') ? $setting_options->currency.' <span></span> ' : ' <span></span> '.$setting_options->currency).'</button>
            </div>
        </div>
        '.$voucherstyle.'';
            if($setting_options->preview_button) {
              $html .= '<div class="voucherPreviewButton"><button type="button" data-fullurl="" data-src="'.get_site_url() .'/voucher-pdf-preview" target="_blank">'.__('Show Preview as PDF', 'gift-voucher' ).'</button></div>';
            }
        $html .= '</div>
    </fieldset>
    </form>';

    $html .= '<style>'.stripslashes($wpgv_custom_css).'</style>';

    return $html;
}

function wpgv__doajax_front_template() {
    global $wpdb;
    $template_table = $wpdb->prefix . 'giftvouchers_template';
    $template_id = $_REQUEST['template_id'];
    $template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template_id" );
    
    $wpgv_hide_first_step = (get_option('wpgv_hide_first_step')) ? get_option('wpgv_hide_first_step') : 0;    
    if($wpgv_hide_first_step == 0){
        $images = $template_options->image_style ? json_decode($template_options->image_style) : ['','',''];
        $image_styles = array();
        foreach ($images as $key => $value) {
            $image_attributes = wp_get_attachment_image_src( $value, 'voucher-medium' );
            $image_styles[] = ($image_attributes) ? $image_attributes[0] : get_option('wpgv_demoimageurl_voucher');
        }

        echo wp_send_json(array('images' => $image_styles, 'title' => $template_options->title));
    }
    
    wp_die();
}

add_shortcode( 'wpgv_giftvoucher', 'wpgv_voucher_shortcode' );
add_action('wp_ajax_nopriv_wpgv_doajax_front_template', 'wpgv__doajax_front_template');
add_action('wp_ajax_wpgv_doajax_front_template', 'wpgv__doajax_front_template');
