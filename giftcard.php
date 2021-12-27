<?php
if( !defined( 'ABSPATH' ) ) exit;
function wpgv_voucher_template_shortcode(){
    $html = '';
    $find = array( 'http://', 'https://' );
    $replace = '';
    $siteURL = str_replace( $find, $replace, get_site_url() );  
    $setting_options = get_data_settings_voucher();  
    $wpgv_add_extra_charges = get_option('wpgv_add_extra_charges_voucher') ? get_option('wpgv_add_extra_charges_voucher') : 0;
    $number_slider = get_option('wpgv_number_giftcard_slider') ? get_option('wpgv_number_giftcard_slider') : 3;
    $wpgv_custom_css = get_option('wpgv_custom_css') ? stripslashes(trim(get_option('wpgv_custom_css'))) : '';    
    $type_mode = get_value_mode_gift_card_function();
    if ($type_mode == 'landscape_giftcard') {
        $mode_template_giftcard = $setting_options->landscape_mode_templates;
    } else{
        $mode_template_giftcard = $setting_options->portrait_mode_templates;
    }
    $coupon_code_length = get_option('wpgv_coupon_code_length') ? get_option('wpgv_coupon_code_length') : 12;

    $max_price_value = !empty($setting_options->voucher_max_value) ? $setting_options->voucher_max_value : 10000; 
    $min_price_value = !empty($setting_options->voucher_min_value) ? $setting_options->voucher_min_value : 1; 

    $voucher_brcolor = get_option('wpgv_voucher_border_color') ? get_option('wpgv_voucher_border_color') : '1371ff';
    $voucher_bgcolor = $setting_options->voucher_bgcolor;
    $voucher_color = $setting_options->voucher_color; 
    $custom_loader = $setting_options->custom_loader ? $setting_options->custom_loader : WPGIFT__PLUGIN_URL.'/assets/img/loader.gif';   
    wp_enqueue_style('wpgv-voucher-style');
    wp_enqueue_style('wpgv-slick-css');
    wp_enqueue_style('wpgv-fontawesome-css');
    wp_enqueue_style('wpgv-voucher-template-fonts-css');
    wp_enqueue_style('wpgv-voucher-template-style-css');
    wp_enqueue_script('wpgv-bootstrap-datetimepicker-js');
    wp_enqueue_script('wpgv-konva-min-js');
    wp_enqueue_script('wpgv-jspdf-js');
    wp_enqueue_script('wpgv-jquery-validate');
    wp_enqueue_script('wpgv-jquery-steps');
    wp_enqueue_script('wpgv-stripe-js');
    wp_enqueue_script('wpgv-paypal-js');
    wp_enqueue_script('wpgv-slick-script');
    wp_enqueue_script('wpgv-voucher-template-script');
    $html .='<div id="giftvoucher-template" class="wrapper-template-gift-voucher ">
        <div class="giftvoucher-template-step-main">
            <div class="giftvoucher-template-steps">
                <button id="wpgv_click_draw_auto" class="button" style="display:none">Drawing</button>
                <div class="giftvoucher-step active">
                    <div class="step-group enable_click" data-step="1" id="select-temp">
                        <div class="step-number">1</div>
                        <div class="step-label">'.__('Select Template', 'gift-voucher' ).'</div>
                    </div>
                </div>
                <div class="giftvoucher-step">
                    <div class="step-group disable_click" data-step="2" id="select-per">
                        <div class="step-number">2</div>
                        <div class="step-label">'.__('Setup your gift card', 'gift-voucher' ).'</div>
                    </div>
                </div>
                <div class="giftvoucher-step">
                    <div class="step-group disable_click" data-step="3" id="select-payment">
                        <div class="step-number">3</div>
                        <div class="step-label">'.__('Payment', 'gift-voucher' ).'</div>
                    </div>
                </div>
                <div class="giftvoucher-step">
                    <div class="step-group disable_click" data-step="4" id="select-overview">
                        <div class="step-number">4</div>
                        <div class="step-label">'.__('Overview', 'gift-voucher' ).'</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="step-progress" id="scrollintoview">
            <div class="progress">
                <div class="progress-bar" style="width:25%"></div>
            </div>
        </div>
        <div class="wrap-giftvoucher-template-content">
            <div id="voucher-template-name-step">
                <span class="number-step">1</span>
                <h3 class="choose-show-title">'.__('Select Template', 'gift-voucher' ).'</h3>
            </div>';
            $html .= '<div class="wrap-format-category-voucher">'.format_categories_function().'</div>'; // call back function fomart caegory
            // content step
            $html .='<div class="giftvoucher-template-content">
                <div id="slider-giftvoucher" class="voucher-content-step">
                    <div id="slider-giftvoucher-template">'.get_data_template_voucher($type_mode,$mode_template_giftcard, 0).'</div>
                </div>
                <div id="setup-voucher-template" class="voucher-content-step">
                    <input type="hidden" name="coupon_code_length" id="coupon_code_length" value="'.$coupon_code_length.'">
                    <input hidden id="voucher-id" value="" />
                    <input hidden id="voucher-extra-charges" value="'.$wpgv_add_extra_charges.'" />
                    <input hidden id="voucher-couponcode" value="" />
                    <div class="wrap-setup-voucher-template">
                        <div id="voucher-template-choose-gift" class="wrap-main-voucher-template">';
                            $html .='<div class="voucher-template-infomation">
                                <div class="wrapper-infomation-voucher-template" id="content-setup-voucher-template">'.set_up_gift_voucher().'</div>
                                <div class="wrapper-infomation-voucher-template" id="setup-shopping-payment-wrap">'.shipping_gift_voucher().'</div> 
                                <div class="wrapper-infomation-voucher-template" id="order-voucher-details-overview">'.show_overview_voucher_template().'</div>
                            </div>
                        </div>
                        <div id="select-template-voucher" class="wrap-main-voucher-template">
                        <div id="template_giftcard_container_1"></div>
                        <div id="template_giftcard_container_2" style="display:none"></div>
                        </div>
                    </div>
                </div>
                <div id="voucher-continue-step">
                    <div class="next-prev-button prev-button">
                        <a href="javascript:;" class="voucher-prev-step" data-prev-step="1"><span><i class="fa fa-angle-left" aria-hidden="true"></i></span>'.__('Prev step', 'gift-voucher' ).'</a>
                    </div>
                    <div class="next-prev-button next-button">
                        <input type="hidden" value="" id="dataVoucher"/>
                        <input type="hidden" value="'.$number_slider.'" id="number_giftcard_sl"/>';
                        if (!empty($setting_options->preview_button)) {
                            $html .='<a href="javascript:;" class="voucher-preview-pdf" id="voucher-preview-pdf">'.__('PDF preview', 'gift-voucher' ).'<span><i class="fa fa-angle-down" aria-hidden="true"></i></span></a>';
                        }
                        $html .='<a href="javascript:;" id="payment-voucher-template">'.__('Pay Now', 'gift-voucher' ).'<span><i class="fa fa-angle-right" aria-hidden="true"></i></span></a>
                        <a href="javascript:;" class="voucher-next-step" data-next-step="3">'.__('Next step', 'gift-voucher' ).'<span><i class="fa fa-angle-right" aria-hidden="true"></i></span></a>
                    </div>
                </div>              
            </div>
        </div>';
    $html .= '</div>';
    $html .= '<style type="text/css">
        .giftvoucher-template-steps .giftvoucher-step.active{ background: #'.$voucher_brcolor.'!important;}
        #slider-giftvoucher-template .item-voucher-template, .giftvoucher-template-steps .giftvoucher-step.passed .step-number {
            background: #'.$voucher_brcolor.'!important;
            border-color: #'.$voucher_brcolor.'!important;
        }
        .giftvoucher-template-steps .giftvoucher-step.passed, #voucher-template-name-step .number-step, .format-category-voucher-template ul li.active, #giftvoucher-template .list-category-voucher .category-nav-item.active .category-voucher-item, #slider-giftvoucher-template .layout-button, #giftvoucher-template .progress .progress-bar, #voucher-template-name-step .number-step, #giftvoucher-template #voucher-continue-step .next-button a.voucher-next-step, #giftvoucher-template #voucher-continue-step .next-prev-button a:hover, .buying-options div.active, #giftvoucher-template .choose-shipping-template .shipping-type.active, #giftvoucher-template .choose-shipping-template .shipping-type:hover, #payment-voucher-template{
            background: #'.$voucher_brcolor.'!important;
        }
        #giftvoucher-template .voucher-template-input .input-info-voucher, #giftvoucher-template #wpgv_payment_gateway #payment_gateway {border-color: #'.$voucher_brcolor.'!important;}
        .giftvoucher-template-steps .giftvoucher-step.active .step-number {
            color: #'.$voucher_brcolor.'!important;
        }
        .voucher-content-step.loading:after, #setup-voucher-template.loading:after {
            content: url('.$custom_loader.') !important;
        }
    </style>';
    $html .= '<style>'.stripslashes($wpgv_custom_css).'</style>';
    return $html;
}
add_shortcode( 'wpgv_giftcard', 'wpgv_voucher_template_shortcode' );

// function get_option_settings
function get_data_settings_voucher(){
    global $wp, $wpdb;
    $setting_table  = $wpdb->prefix . 'giftvouchers_setting';
    $setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1");
    return $setting_options;
}

// function get template voucher portail
function get_data_template_voucher($type, $template_voucher, $category_voucher){ 
    if(!empty($category_voucher)){
        $voucher_arr = array(
            'post_type' => 'voucher_template',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'category_voucher_template',
                    'field' => 'id',
                    'terms' => array($category_voucher)
                )
            ),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'       => 'wpgv_customize_template_status',
                    'value'     => 'active',
                    'compare'   => '=',
                ),
                array(
                    'key'       => 'wpgv_customize_template_template-style',
                    'value'     => join(', ', array($template_voucher)),
                    'compare'   => 'IN',
                ),
                
            )
        );
    }else{
        $voucher_arr = array(
            'post_type' => 'voucher_template',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'       => 'wpgv_customize_template_status',
                    'value'     => 'active',
                    'compare'   => '=',
                ),
                array(
                    'key'       => 'wpgv_customize_template_template-style',
                    'value'     => join(', ', array($template_voucher)),
                    'compare'   => 'IN',
                ),
                
            )
        );
    }
    $template_voucher_args = new WP_Query($voucher_arr);
    $voucher_counter = 1;
    $total_voucher = $template_voucher_args->found_posts;
    $s_counter = 1;
    $html = '';
    if($template_voucher_args->have_posts()){
        $html.= '<div class="slider-voucher-template">';
        while( $template_voucher_args->have_posts() ) : $template_voucher_args->the_post();
            $post_id = get_the_ID();
            $select_status_template = get_post_meta($post_id,'wpgv_customize_template_select_template',true);
            // echo "<pre>";
            // var_dump($select_status_template);
            if($select_status_template == ""){
                $selected_voucher_template = get_post_meta($post_id,'wpgv_customize_template_template-style',true);
                $frame_no = preg_replace('/[^0-9]/', '', $selected_voucher_template);
                $selected_voucher_thumbnail = get_post_meta($post_id,'wpgv_customize_template_thumbnail',true);
                $url_img = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/png/'.$selected_voucher_template;
                $html.= '<div class="item-voucher-template">
                    <img src="'.$url_img.'" alt="'.get_the_title().'">';
                    $html.= '<div class="layout-overlay" data-template="'.$type.'" data-src="'.$selected_voucher_template.'" data-post_id="'.$post_id.'">
                        <div class="layout-overlay-row">
                            <span class="layout-button">
                            '.__('Select This', 'gift-voucher' ).'
                            </span>
                        </div>
                    </div>
                </div>'; 
            }else{
                if($select_status_template == "default"){
                    $selected_voucher_template = get_post_meta($post_id,'wpgv_customize_template_template-style',true);
                    $frame_no = preg_replace('/[^0-9]/', '', $selected_voucher_template);
                    $selected_voucher_thumbnail = get_post_meta($post_id,'wpgv_customize_template_thumbnail',true);
                    $url_img = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/png/'.$selected_voucher_template;
                    $html.= '<div class="item-voucher-template">
                        <img src="'.$url_img.'" alt="'.get_the_title().'">';
                        $html.= '<div class="layout-overlay" data-template="'.$type.'" data-src="'.$selected_voucher_template.'" data-post_id="'.$post_id.'">
                            <div class="layout-overlay-row">
                                <span class="layout-button">
                                '.__('Select This', 'gift-voucher' ).'
                                </span>
                            </div>
                        </div>
                    </div>'; 
                }else if($select_status_template == "custom"){
                    $get_bg_temp = get_post_meta($post_id,'wpgv_customize_template_bg_result',true);
                    $link_bg_temp = wp_get_attachment_image_src( $get_bg_temp, 'large' );
                    // var_dump($get_bg_temp);
                    $html.= '<div class="item-voucher-template">
                        <img src="' . $link_bg_temp[0] . '" alt="'.get_the_title().'">';
                        $html.= '<div class="layout-overlay" data-template="'.$type.'" data-src="'. $link_bg_temp[0] .'" data-post_id="'.$post_id.'">
                            <div class="layout-overlay-row">
                                <span class="layout-button">
                                '.__('Select This', 'gift-voucher' ).'
                                </span>
                            </div>
                        </div>
                    </div>'; 
                }
            }
            
            
        endwhile; wp_reset_postdata(); 
        $html.= '</div>';
    }else{
       $html.='<div class="wpgv_no_voucher_found"><span>'.__('No Gift Voucher Found!', 'gift-voucher' ).'</span></div>'; 
    }
    return $html;
}
// function ajax template voucher slider
function getTemplateVoucherSlider(){
    $setting_options = get_data_settings_voucher();// get data settings option
    $portail_mode_templates = $setting_options->portrait_mode_templates; 
    $landscape_mode_templates = $setting_options->landscape_mode_templates; 
    $dataType = !empty($_POST['dataType']) ? $_POST['dataType'] : 'portrait';
    $dataCategory = !empty($_POST['dataCategory']) ? $_POST['dataCategory'] : '0';    
    if($dataType == 'landscape'){
        $data = get_data_template_voucher($dataType, $landscape_mode_templates, $dataCategory);
    }else{
        $data = get_data_template_voucher($dataType, $portail_mode_templates, $dataCategory);
    }
    echo $data;
    wp_die();
}
add_action('wp_ajax_voucher_slider_template', 'getTemplateVoucherSlider');
add_action('wp_ajax_nopriv_voucher_slider_template', 'getTemplateVoucherSlider');
// function select template voucher
function getSelectTemplateVoucher(){
    $setting_options = get_data_settings_voucher();
    $giftto = __('Gift To', 'gift-voucher');
    $giftfrom = __('Gift From', 'gift-voucher');
    //$date_of = __('Date of Expiry', 'gift-voucher');
    $date_of = __('Date', 'gift-voucher');
    $counpon = __('Coupon', 'gift-voucher');
    $voucher_id = !empty($_POST['voucher_id']) ? $_POST['voucher_id'] : 0;
    $web = !empty($setting_options->pdf_footer_url) ? $setting_options->pdf_footer_url : get_site_url();
    $email = !empty($setting_options->pdf_footer_email) ? $setting_options->pdf_footer_email : get_option('admin_email');;
    $company_name = !empty($setting_options->company_name) ? $setting_options->company_name : get_bloginfo( 'name' );;
    // check expiry
    $wpgv_hide_expiry = get_option('wpgv_hide_expiry') ? get_option('wpgv_hide_expiry') : 'yes'; 
    $wpgv_expiry_date_format = get_option('wpgv_expiry_date_format') ? get_option('wpgv_expiry_date_format') : 'd.m.Y';
    $voucher_expiry_value = !empty(get_post_meta( $voucher_id, 'wpgv_customize_template_voucher_expiry_value', true)) ? get_post_meta( $voucher_id, 'wpgv_customize_template_voucher_expiry_value', true) : $setting_options->voucher_expiry; // format day and number 
    if($wpgv_hide_expiry == 'no') {
        $expiryDate = __('No Expiry', 'gift-voucher' );
    } else {
        $expiryDate = ($setting_options->voucher_expiry_type == 'days') ? date($wpgv_expiry_date_format,strtotime('+'.$voucher_expiry_value.' days',time())) . PHP_EOL : $voucher_expiry_value;
    }
    $wpgv_leftside_notice = (get_option('wpgv_leftside_notice') != '') ? get_option('wpgv_leftside_notice') : __('Cash payment is not possible. The terms and conditions apply.', 'gift-voucher' );
    $logo = get_option("wpgv_company_logo");
    //WPGIFT__PLUGIN_URL
    $select_template = get_post_meta( $voucher_id, 'wpgv_customize_template_template-style', true );
    $title_template = str_replace(".png",".svg",$select_template);

    //$images_template = !empty(get_post_meta( $voucher_id, 'wpgv_customize_template_image', true )) ? get_post_meta( $voucher_id, 'wpgv_customize_template_image', true ) : WPGIFT__PLUGIN_URL.'/assets/img/template-images/'.$title_template; 
    // $check_images_template = WPGIFT__PLUGIN_URL.'/assets/img/template-images/'.$title_template;
    // if (empty($check_images_template)) {
    curl_file_server( $select_template );      
    // }
    $name_template = str_replace(".png",".json",$select_template);
    $get_status_temp = get_post_meta( $voucher_id, 'wpgv_customize_template_select_template', true);
    $get_json_temp = get_post_meta( $voucher_id, 'wpgv_customize_template_json_template', true);
    
    $images_template = "";
    $json = "";
    if(!empty($get_status_temp)){
        if($get_status_temp == "default"){
            $images_template = WPGIFT__PLUGIN_URL.'/assets/img/template-images-v2/'.$title_template; 
            $get_url_json = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/'.$name_template;
            $json = file_get_contents($get_url_json);
        }else if($get_status_temp == "custom"){
            $get_bg_template = get_post_meta( $voucher_id, 'wpgv_customize_template_id_bg_template', true);
            if(is_numeric($get_bg_template)){
                $get_url_bg = wp_get_attachment_image_src( $get_bg_template, 'large' );
                $images_template = $get_url_bg[0]; 
            }else{
                $images_template = $get_bg_template; 
            }
            $json = $get_json_temp;
        }
    }else{
        $images_template = WPGIFT__PLUGIN_URL.'/assets/img/template-images-v2/'.$title_template; 
        $get_url_json = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/json/'.$name_template;
        $json = file_get_contents($get_url_json);
    }  
    $result = array(
        'url' => $images_template,
        'currency' => $setting_options->currency,
        'giftto' => $giftto,
        'giftfrom' => $giftfrom,
        'date_of' => $date_of,
        'company_logo' => $logo,
        'company_name' => $company_name,
        'email' => $email,
        'web' => $web,
        'leftside_notice' => $wpgv_leftside_notice,
        'expiryDate' => $expiryDate,
        'counpon' => $counpon,
        'json' => $json,
    );
    echo json_encode($result);
    wp_die();
}
add_action('wp_ajax_ajax_select_voucher_template', 'getSelectTemplateVoucher');
add_action('wp_ajax_nopriv_ajax_select_voucher_template', 'getSelectTemplateVoucher');
// function set up gift voucher
function set_up_gift_voucher(){
    $setting_options = get_data_settings_voucher();
    
    $wpgv_buying_for = get_option('wpgv_buying_for') ? get_option('wpgv_buying_for') : 'both';
    $html = '';
    if ($wpgv_buying_for == 'both' || $wpgv_buying_for == 'someone_else' ) {
    $html .= '<div class="voucher-buying-for-option">'; 
            if ($wpgv_buying_for == 'both') {
                $html .= '<h4 class="title-header-h4">'.__('Buying For','gift-voucher').'</h4>
                <div class="voucher-option buying-options">            
                    <div class="someone_else active option-select" data-value="someone_else">
                        <img src="'.WPGIFT__PLUGIN_URL.'/assets/img/giftbox.png">
                        <span>'.__('Someone Else', 'gift-voucher' ).'</span>
                    </div>
                    <div class="yourself option-select" data-value="yourself">
                        <img src="'.WPGIFT__PLUGIN_URL.'/assets/img/users.png">
                        <span>'.__('Yourself', 'gift-voucher' ).'</span>
                    </div>
                </div>';
            }
            $html .= '<input type="hidden" name="buying_for" id="buying_for" value="someone_else">    
        </div>    
        <div class="voucher-template-input" id="voucher-to">
            <label>'.__('Gift To','gift-voucher').'</label>
            <input maxlength="30" value="" id="voucher_gift_to" name="voucher_gift_to" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>
        <div class="voucher-template-input" id="voucher-from">
            <label>'.__('Gift From','gift-voucher').'</label>
            <input maxlength="30" value="" id="voucher_gift_from" name="voucher_gift_from" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>';
    }else{
        $html .= '<div class="voucher-buying-for-option">
            <input type="hidden" name="buying_for" id="buying_for" value="yourself">    
        </div> 
        <div class="voucher-template-input" id="voucher-from">
            <label>'.__('Your name','gift-voucher').'</label>
            <input maxlength="30" value="" id="voucher_gift_from" name="voucher_gift_from" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>';
    }
    $html .= '<div class="voucher-template-input price-voucher">
        <label>'.__('Gift Value','gift-voucher').'</label>
        <div class="price-template-voucher">
            <span class="currencySymbol"> '.$setting_options->currency.' </span>
            <input type="number" name="voucher_price_value" id="voucher_price_value" class="input-info-voucher" placeholder="'.sprintf("%s: %s", __('Min value', 'gift-voucher'), $setting_options->voucher_min_value).'" value="" min-value="'.$setting_options->voucher_min_value.'" max-value="'.$setting_options->voucher_max_value.'" required>
        </div>
        <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
    </div>
    <div class="voucher-template-input">
        <label>'.__('Description (Max: 250 Characters)','gift-voucher').'</label>
        <textarea maxlength="250" value="" id="voucher_description" placeholder="'.__('Description (Max: 250 Characters)','gift-voucher').'" name="voucher_description" class="input-info-voucher"></textarea>
        <div class="maxchar"></div>
    </div>';
    return $html;
}
//function shipping voucher
function shipping_gift_voucher(){
    $setting_options = get_data_settings_voucher();
    $wpgv_buying_for = get_option('wpgv_buying_for') ? get_option('wpgv_buying_for') : 'both';
    $hiddenEmail = $wpgv_buying_for == 'yourself' ? 'hiddenEmail' : '';
    $html = '';
    if (!empty($setting_options->post_shipping)) {
        $html .='<div class="header-title">
            <h4 class="title-header-h4">'.__('Shipping','gift-voucher').'</h4>
        </div>
        <div class="choose-shipping-template">
            <a class="shipping-type active" data-type="shipping_as_email">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>'.__('Email','gift-voucher').'</span>
            </a>
            <a class="shipping-type" data-type="shipping_as_post">
                <i class="fa fa-truck" aria-hidden="true"></i>
                <span>'.__('Post','gift-voucher').'</span>
            </a>
        </div>';
    }
    $html .='<div class="form-shopping-payment">
        <div class="wrap-email-shiping-voucher">
            <div class="voucher-template-input '.$hiddenEmail.'" id="recipient_email">
                <label>'.__('Send the voucher to recipient email here','gift-voucher').'</label>
                <input value="" id="voucher_recipient_email" name="voucher_recipient_email" type="text" class="input-info-voucher">
                <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
            </div>
            <div class="voucher-template-input">
                <label>'.__('Your email address (for the receipt)','gift-voucher').'</label>
                <input value="" id="voucher_your_email" name="voucher_your_email" type="text" class="input-info-voucher">
                <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
            </div>
        </div>';
        if (!empty($setting_options->post_shipping)) {
            $html .='<div class="wrap-shipping-info-voucher">'.show_shipping_method_voucher().'</div>'; 
        }
        $html .='<div class="choose-payment-method">'.show_payment_option_voucher().'</div> 
        <div class="order-voucher-details">'.show_order_detail_voucher().'</div>                                  
    </div>';
    return $html;
}
// function get payment
function show_payment_option_voucher(){
    $setting_options = get_data_settings_voucher();
    $wpgv_multisafepay = get_option('wpgv_multisafepay') ? get_option('wpgv_multisafepay') : 0;
    $wpgv_paypal_alternative_text = get_option('wpgv_paypal_alternative_text') ? get_option('wpgv_paypal_alternative_text') : __('PayPal', 'gift-voucher');
    $wpgv_stripe_alternative_text = get_option('wpgv_stripe_alternative_text') ? get_option('wpgv_stripe_alternative_text') : __('Stripe', 'gift-voucher');
    $wpgv_multisafepay_alternative_text = get_option('wpgv_multisafepay_alternative_text') ? get_option('wpgv_multisafepay_alternative_text') : __('MultiSafepay', 'gift-voucher');
    $wpgv_termstext = get_option('wpgv_termstext') ? get_option('wpgv_termstext') : __('I here by accept the <a href="https://wordpress.org/about/privacy/" target="_blank">terms and conditions</a>, the revocation of the privacy policy and confirm that all information is correct.','gift-voucher');
    $paymenyGateway = '';
    if($setting_options->paypal || $setting_options->sofort || $setting_options->stripe || $setting_options->per_invoice || $wpgv_multisafepay){
        $paymenyGateway .= '<div class="" id="wpgv_payment_gateway">';
        $paymenyGateway .= '<label>'.__('Payment Method', 'gift-voucher').'</label>';
        $paymenyGateway .= '<select name="payment_gateway" id="payment_gateway" class="form-field">';
        $paymenyGateway .= $setting_options->paypal ? '<option value="Paypal">'.$wpgv_paypal_alternative_text.'</option>' : '';
        $paymenyGateway .= $setting_options->stripe ? '<option value="Stripe">'.$wpgv_stripe_alternative_text.'</option>' : '';
        $paymenyGateway .= $wpgv_multisafepay ? '<option value="MultiSafepay">'.$wpgv_multisafepay_alternative_text.'</option>' : '';
        $paymenyGateway .= $setting_options->sofort ? '<option value="Sofort">'.__('Sofort', 'gift-voucher').'</option>' : '';
        $paymenyGateway .= $setting_options->per_invoice ? '<option value="Per Invoice">'.__('Per Invoice', 'gift-voucher').'</option>' : '';
        $paymenyGateway .= '</select>';        
        $paymenyGateway .= '</div>';        
    }
    return $paymenyGateway;
}
// show shipping method
function show_shipping_method_voucher(){
    $setting_options = get_data_settings_voucher();
    $shipping_methods = explode(',', $setting_options->shipping_method);    
    $shipping_methods_string = '';
    $shipping_methods_string .= '<div class="shipping-name">
        <div class="voucher-template-input">
            <input value="" id="voucher_shipping_first" placeholder="'.__('First name','gift-voucher').'" name="voucher_shipping_first" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>
        <div class="voucher-template-input">
            <input value="" id="voucher_shipping_last" placeholder="'.__('Last name','gift-voucher').'" name="voucher_shipping_last" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>
    </div>
    <div class="shipping-address">
        <div class="voucher-template-input">
            <input value="" id="voucher_shipping_address" placeholder="'.__('Address','gift-voucher').'" name="voucher_shipping_address" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>
    </div>';
    $shipping_methods_string .= '<div class="shipping-postcode">
        <div class="voucher-template-input">
            <input value="" id="voucher_shipping_postcode" placeholder="'.__('Postcode','gift-voucher').'" name="voucher_shipping_postcode" type="text" class="input-info-voucher">
            <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        </div>
    </div>    
    <div class="shipping-method">';
    foreach ($shipping_methods as $key => $method) {
        if($method != ''){
            $shipping_method = explode(':', $method);
            $shipping_methods_string .= '<label data-value="'.trim(stripslashes($shipping_method[0])).'"><input type="radio" name="shipping_method" value="'.trim(stripslashes($shipping_method[1])).'" class="radio-field"> '.trim(stripslashes($shipping_method[1])).'</label>';
        }
    }
    $shipping_methods_string .= '</div>';
    return $shipping_methods_string;
}
// show order detail
function show_order_detail_voucher(){
    $setting_options = get_data_settings_voucher();
    $wpgv_additional_charges_text = get_option('wpgv_additional_charges_text_voucher') ? get_option('wpgv_additional_charges_text_voucher') : __('Additional Website Charges','gift-voucher');
    $wpgv_add_extra_charges = get_option('wpgv_add_extra_charges_voucher') ? get_option('wpgv_add_extra_charges_voucher') : 0;
    $wpgv_termstext = get_option('wpgv_termstext') ? get_option('wpgv_termstext') : __('I here by accept the <a href="https://wordpress.org/about/privacy/" target="_blank">terms and conditions</a>, the revocation of the privacy policy and confirm that all information is correct.','gift-voucher');
    $html = '';
    $html .= '<div class="order-detail-voucher-template">';
        $html .= '<div class="order-info">';
            $html .= '<h6 class="title-order">'.__('Your order','gift-voucher').'</h6>';
        $html .= '</div>';
        $html .= '<div class="order-info">';
            $html .= '<div class="order-info-content name-voucher">';
                $html .= '<h5 class="title-order">'.__('Gift voucher','gift-voucher').'</h5>';
            $html .= '</div>';
            $html .= '<div class="price-voucher '.get_position_currency_giftcard().'">';            
                $html .= '<span class="currency">'.$setting_options->currency.'</span>';            
                $html .= '<span class="currency-price-value"></span>';            
            $html .= '</div>';
            $html .= '<div class="order-info-name">'.__('Your name:','gift-voucher').'<span class="order-your-name"></span></h5></div>';
        $html .= '</div>';
        $html .= '<div class="order-info">';
            $html .= '<h5 class="title-order">'.$wpgv_additional_charges_text.'</h5>';
            $html .= '<div class="price-voucher-extra-charges '.get_position_currency_giftcard().'">';  
                $html .= '<span class="currency">'.$setting_options->currency.'</span>';            
                $html .= '<span class="currency-price-extra_charges">'.$wpgv_add_extra_charges.'</span>';           
            $html .= '</div>';
        $html .= '</div>'; 
        if (!empty($setting_options->post_shipping)) { 
            $html .= '<div class="order-info order-info-shipping">';
                $html .= '<h5 class="title-order">'.__('Shipping','gift-voucher').'</h5>';
                $html .= '<div class="price-voucher-shipping '.get_position_currency_giftcard().'">'; 
                    $html .= '<span class="currency">'.$setting_options->currency.'</span>';            
                    $html .= '<span class="currency-price-shipping"></span>';                     
                $html .= '</div>';
            $html .= '</div>';  
        }
        $html .= '<div class="order-info order-info-total">';
            $html .= '<h5 class="title-order">'.__('Total','gift-voucher').'</h5>';
            $html .= '<div class="price-voucher-total '.get_position_currency_giftcard().'">';                      
                $html .= '<span class="currency">'.$setting_options->currency.'</span>';                      
                $html .= '<span class="price-total"></span>';                      
            $html .= '</div>';
        $html .= '</div>';            
    $html .= '</div>';
    $html .= '<div class="acceptVoucherTerms">
        <label><input type="checkbox" class="required cccc" name="acceptVoucherTerms"> '.stripslashes($wpgv_termstext).'</label>
        <span class="error-input">'.__('This field is required.','gift-voucher').'</span>
        <div class="voucherNote">'.$setting_options->voucher_terms_note.'</div>
    </div>';
    return $html;
}
// Overview voucher
function show_overview_voucher_template(){
    $setting_options = get_data_settings_voucher();   
    $html = '';
    $html .= '<div class="overview_voucher_template">';
        $html .= '<div class="order-voucher order-voucher-price ">';
            $html .= '<span>'.__('Voucher value','gift-voucher').'</span>';
            $html .= '<p class="value-price-voucher '.get_position_currency_giftcard().'">'.$setting_options->currency.'<span class="price"></span></p>';
        $html .= '</div>';
        $html .= '<div class="order-voucher order-voucher-recipient-email">';
            $html .= '<span>'.__('Send the voucher to recipient email here','gift-voucher').'</span>';
            $html .= '<p class="value-recipient-email"><span class="recipient-email"></span></p>';
        $html .= '</div>';
        $html .= '<div class="order-voucher order-voucher-email">';
            $html .= '<span>'.__('Your email address (for the receipt)','gift-voucher').'</span>';
            $html .= '<p class="value-you-email"><span class="email"></span></p>';
        $html .= '</div>';        
        $html .= '<div class="order-voucher order-voucher-full-name">';
            $html .= '<span>'.__('Full Name','gift-voucher').'</span>';
            $html .= '<p class="value-full-name"><span class="full-name"></span></p>';
        $html .= '</div>';
        $html .= '<div class="order-voucher order-voucher-address">';
            $html .= '<span>'.__('Address','gift-voucher').'</span>';
            $html .= '<p class="value-address-voucher"><span class="address-voucher"></span></p>';
        $html .= '</div>';
        $html .= '<div class="order-voucher order-voucher-postcode">';
            $html .= '<span>'.__('Postcode','gift-voucher').'</span>';
            $html .= '<p class="value-postcode-voucher"><span class="postcode-voucher"></span></p>';
        $html .= '</div>';
        $html .= '<div class="order-voucher order-voucher-shipping">';
            $html .= '<span>'.__('Shipping','gift-voucher').'</span>';
            $html .= '<p class="value-shipping-voucher"><span class="shipping-voucher"></span></p>';
        $html .= '</div>';
        $html .= '<div class="order-voucher order-voucher-payment-method">';
            $html .= '<span>'.__('Paymet Method','gift-voucher').'</span>';
            $html .= '<p class="value-payment-method-voucher"><span class="payment-method-voucher"></span></p>';
        $html .= '</div>';
    $html .= '</div>';    
    return $html;
}
//upload images svg
function custom_mtypes( $m ){
    $m['svg'] = 'image/svg+xml';
    $m['svgz'] = 'image/svg+xml';
    $m['csv'] = 'text/csv'; //upload file csv
    $m['xml'] = 'text/xml';//upload file xml
    return $m;
}
add_filter( 'upload_mimes', 'custom_mtypes' );
//get file svg sever
function curl_file_server( $file_name ) {
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    if($ext == 'png') {
        $name_svg = str_replace(".png",".svg",$file_name);
        // $file_template= WPGIFT__PLUGIN_DIR.'/assets/img/template-images/'.$name_svg;
        // if(!file_exists($file_template)) {
        //     $url = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/svg/'.$name_svg;
        //     $ch = curl_init($url); 
        //     $dir = WPGIFT__PLUGIN_DIR.'/assets/img/template-images/';
        //     $file_name = basename($url); 
        //     $save_file_loc = $dir . $file_name; 
        //     $fp = fopen($save_file_loc, 'wb'); 
        //     curl_setopt($ch, CURLOPT_FILE, $fp); 
        //     curl_setopt($ch, CURLOPT_HEADER, 0); 
        //     curl_exec($ch); 
        //     curl_close($ch); 
        //     fclose($fp); 
        // } 
        $file_template_v2= WPGIFT__PLUGIN_DIR.'/assets/img/template-images-v2/'.$name_svg;
        if(!file_exists($file_template_v2)) {
            $url = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/svg/'.$name_svg;
            $ch = curl_init($url); 
            $dir = WPGIFT__PLUGIN_DIR.'/assets/img/template-images-v2/';
            $file_name = basename($url); 
            $save_file_loc = $dir . $file_name; 
            $fp = fopen($save_file_loc, 'wb'); 
            curl_setopt($ch, CURLOPT_FILE, $fp); 
            curl_setopt($ch, CURLOPT_HEADER, 0); 
            curl_exec($ch); 
            curl_close($ch); 
            fclose($fp); 
        } 
    }
}    
//format template mode giftcard
function get_value_mode_gift_card_function(){
    $mode_giftcard = (get_option('wpgv_template_mode_giftcard') != '') ? get_option('wpgv_template_mode_giftcard') : 'both_giftcard';
    return $mode_giftcard;
}
//function format_categories_function 
function format_categories_function(){
    $type = get_value_mode_gift_card_function();
    $html = '';
    $html .= '<div class="format-category-voucher-template">                
            <h6 class="title-h6-voucher">'.__('Format','gift-voucher').'</h6>
            <ul class="format-category-voucher">';
                if ($type == 'portrait_giftcard') {
                    $html .= '<li data-type="portrait" class="layout-type active">
                        <a href="javascript:;" class="portrait" title="'.esc_attr('portrait','gift-voucher').'"></a>
                    </li>';                    
                }elseif ($type == 'landscape_giftcard') {
                    $html .= '<li data-type="landscape" class="layout-type active">
                        <a href="javascript:;" class="landscape" title="'.esc_attr('landscape','gift-voucher').'"></a>
                    </li>';
                }else{
                    $html .= '<li data-type="portrait" class="layout-type active">
                        <a href="javascript:;" class="portrait" title="'.esc_attr('portrait','gift-voucher').'"></a>
                    </li>
                    <li data-type="landscape" class="layout-type template_mode_active">
                        <a href="javascript:;" class="landscape" title="'.esc_attr('landscape','gift-voucher').'"></a>
                    </li>';
                }
            $html .= '</ul>
        </div>
        <div class="voucher-category-selection-wrap">
            <div class="voucher-category-main">
                <h6 class="title-h6-voucher">'.__('Category','gift-voucher').'</h6>
                <ul class="list-category-voucher">
                    <li class="category-nav-item active">
                        <a href="#all" class="category-voucher-item" data-category-id="0">'.__('All','gift-voucher').'</a>
                    </li>';
                    $category_voucher = get_terms( array(
                        'taxonomy' => 'category_voucher_template',
                        'hide_empty' => false,
                    ) );
                    if (!empty($category_voucher)) {
                        foreach ($category_voucher as $key => $category) {
                            $html .='<li class="category-nav-item"><a class="category-voucher-item" data-category-id='.$category->term_id.'>'.$category->name.'</a></li>';
                        }
                    }
                $html .= '</ul>
            </div>
        </div>';
    return $html;
}
//function check price left/right
function get_position_currency_giftcard(){
    $setting_options = get_data_settings_voucher();
    $class = "";
    if ($setting_options->currency_position == 'Left') {
        $class = 'currency_left';
    }else{
        $class = 'currency_right';
    }
    return $class;
}
function wpgv_curl_file_server(){
	$file_name = (isset($_POST['file_name'])) ? esc_attr($_POST['file_name']) : '';
    curl_file_server($file_name);
	die(); 
}
add_action('wp_ajax_wpgv_curl_file_server', 'wpgv_curl_file_server');
add_action('wp_ajax_nopriv_wpgv_curl_file_server', 'wpgv_curl_file_server');

// function remove_jquery_migrate_notice() {
//     $m= $GLOBALS['wp_scripts']->registered['jquery-migrate'];
//     $m->extra['before'][]='temp_jm_logconsole = window.console.log; window.console.log=null;';
//     $m->extra['after'][]='window.console.log=temp_jm_logconsole;';
// }
// add_action( 'init', 'remove_jquery_migrate_notice', 5 );
