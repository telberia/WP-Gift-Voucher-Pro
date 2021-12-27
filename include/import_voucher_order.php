<?php

global $wpdb;

// Table name
$tablename = $wpdb->prefix."giftvouchers_list";

$get_last_id_arr = $wpdb->get_row("SELECT max(id) as id FROM $tablename", ARRAY_A);

$get_last_id = $get_last_id_arr['id'];

if(isset($_POST['voucher_oreder_import_btn']))
{
    // File extension
    $extension = pathinfo($_FILES['voucher_oreder_import_file']['name'], PATHINFO_EXTENSION);

    if(!empty($_FILES['voucher_oreder_import_file']['name']) && $extension == "csv" )
    {
        $csvFile = fopen($_FILES['voucher_oreder_import_file']['tmp_name'], 'r');

        fgetcsv($csvFile); // Skipping header row

        $totalInserted = 0;

        // Read file
        while(($csvData = fgetcsv($csvFile)) !== FALSE)
        {
            $get_last_id++;
            $csvData = array_map("utf8_encode", $csvData);
            
            $order_type = (isset($csvData[1])) ? $csvData[1] : '';
            $template = (isset($csvData[2])) ? $csvData[2] : ''; 
            $itemcat_id = (isset($csvData[3])) ? $csvData[3] : '';
            $item_id = (isset($csvData[4])) ? $csvData[4] : '';
            $buyingfor = (isset($csvData[5])) ? $csvData[5] : '';
            $for = (isset($csvData[6])) ? $csvData[6] : '';
            $from = (isset($csvData[7])) ? $csvData[7] : '';
            $value = (isset($csvData[8])) ? $csvData[8] : '';
            $message = (isset($csvData[9])) ? $csvData[9] : '';
            $firstname = (isset($csvData[10])) ? $csvData[10] : '';
            $lastname = (isset($csvData[11])) ? $csvData[11] : '';
            $email = (isset($csvData[12])) ? $csvData[12] : '';
            $address = (isset($csvData[13])) ? $csvData[13] : '';
            $pincode = (isset($csvData[14])) ? $csvData[14] : '';
            $paymentmethod = (isset($csvData[15])) ? $csvData[15] : '';
            $shipping = (isset($csvData[16])) ? $csvData[16] : '';
            $shipping_email = (isset($csvData[17])) ? $csvData[17] : '';
            $shipping_method = (isset($csvData[18])) ? $csvData[18] : '';
            $expiry = (isset($csvData[19])) ? $csvData[19] : '';
            $code = (isset($csvData[20])) ? $csvData[20] : '';
            $voucherpdf_link = (isset($csvData[21])) ? $csvData[21] : '';
            $voucheradd_time = (isset($csvData[22])) ? $csvData[22] : '';
            $status = (isset($csvData[23])) ? $csvData[23] : '';
            $payment_status = (isset($csvData[24])) ? $csvData[24] : '';



            $voucher_table  = $wpdb->prefix . 'giftvouchers_list';
            $setting_table  = $wpdb->prefix . 'giftvouchers_setting';
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

            if($wpgv_hide_expiry == 'no') {
                $expiry = __('No Expiry', 'gift-voucher' );
            } else {
                $expiry = ($setting_options->voucher_expiry_type == 'days') ? date($wpgv_expiry_date_format,strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;
            }

            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $curr_time = time();
            $upload_dir = $upload_dir . '/voucherpdfuploads/'.$curr_time.$code.'.pdf';
            $upload_url = $curr_time.$code;

            $formtype = 'voucher';
            $preview = false;

            if ($setting_options->is_style_choose_enable) {
                $voucher_style = 0;
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

            $wpdb->insert($voucher_table, array(
                'id' => $get_last_id,
                'order_type' => $order_type,
                'template_id' => $template,
                'itemcat_id' => $itemcat_id,
                'item_id' => $item_id,
                'buying_for' => $buyingfor,
                'from_name' => $for,
                'to_name' => $from,
                'amount' => $value,
                'message' => $message,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'address' => $address,
                'postcode' => $pincode,
                'pay_method' => $paymentmethod,
                'shipping_type' => $shipping,
                'shipping_email' => $shipping_email,
                'shipping_method' => $shipping_method,
                'expiry' => $expiry,
                'couponcode' => $code,
                'voucherpdf_link' => $upload_url,
                'voucheradd_time' => current_time( 'mysql' ),
                'status' => $status,
                'payment_status' => $payment_status
            ));

            if($wpdb->insert_id > 0)
            {
                $totalInserted++;
            }
        }

        if($totalInserted > 0)
        {
            $url = admin_url("admin.php")."?page=vouchers-lists&tab=import-order&msg=2&import=".$totalInserted;
            wp_redirect($url);
        }
        else
        {
            $url = admin_url("admin.php")."?page=vouchers-lists&tab=import-order&msg=3";
            wp_redirect($url);   
        }
    }
    else
    {
        $url = admin_url("admin.php")."?page=vouchers-lists&tab=import-order&msg=1";
        wp_redirect($url);
    }
}