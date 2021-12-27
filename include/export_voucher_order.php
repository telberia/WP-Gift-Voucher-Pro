<?php
if(is_admin()) 
{
    global $wpdb;
    
    // Export in csv
    
    $filename = "voucher_orders.csv";
    header('Content-type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    ob_clean();

    $file = fopen('php://output', 'w');

    fputcsv($file, array('Id','Order Type','Template Id','Template Name','Itemcat Id','ItemCat Name','Item Id','Item Name','Buying For','From Name','To Name','Amount','Message','First Name','Last Name','Email','Address','Postcode','Payment Method','Shipping Type','Shipping Email','Shipping Method','Expiry Date','Voucher Code','Voucher Pdf Link','Order Date','Status','Payment Status'));

    // fputcsv($file, array('Id','Order Type','Template Id','Itemcat Id','Item Id','Buying For','From Name','To Name','Amount','Message','First Name','Last Name','Email','Address','Postcode','Payment Method','Shipping Type','Shipping Email','Shipping Method','Expiry Date','Voucher Code','Voucher Pdf Link','Order Date','Status','Payment Status'));

    $voucher_orders = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}giftvouchers_list");
    
    // $voucher_orders = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}giftvouchers_list Limit 2");

    foreach ($voucher_orders as $voucher_order) {
        $id = (isset($voucher_order->id)) ? $voucher_order->id : ''; 
        $order_type = (isset($voucher_order->order_type)) ? $voucher_order->order_type : '';
        $template_id = (isset($voucher_order->template_id)) ? $voucher_order->template_id : ''; 
        
        $template_name_arr = $wpdb->get_row("SELECT title FROM {$wpdb->prefix}giftvouchers_template WHERE id='$template_id' ", ARRAY_A);
        
        $template_name = (isset($template_name_arr['title'])) ? $template_name_arr['title'] : '';

        $itemcat_id = (isset($voucher_order->itemcat_id)) ? $voucher_order->itemcat_id : ''; 
        
        $itemcat_name_arr = get_term_by( 'id', $itemcat_id,'wpgv_voucher_category',ARRAY_A);

        $itemcat_name = $itemcat_name_arr['name'];

        $item_id = (isset($voucher_order->item_id)) ? $voucher_order->item_id : ''; 

        $item_name = get_the_title($item_id); 

        $buying_for = (isset($voucher_order->buying_for)) ? $voucher_order->buying_for : ''; 
        $from_name = (isset($voucher_order->from_name)) ? $voucher_order->from_name : ''; 
        $to_name = (isset($voucher_order->to_name)) ? $voucher_order->to_name : ''; 
        
        $setting_options = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}giftvouchers_setting WHERE id = 1" );

        $amount_with_currency = $setting_options->currency." ".$voucher_order->amount;
        $amount = (isset($voucher_order->amount)) ? $voucher_order->amount : '';

        $message = (isset($voucher_order->message)) ? $voucher_order->message : ''; 
        $firstname = (isset($voucher_order->firstname)) ? $voucher_order->firstname : ''; 
        $lastname = (isset($voucher_order->lastname)) ? $voucher_order->lastname : ''; 
        $email = (isset($voucher_order->email)) ? $voucher_order->email : ''; 
        $city = (isset($voucher_order->city)) ? $voucher_order->city : ''; 
        $address = (isset($voucher_order->address)) ? $voucher_order->address : ''; 
        $postcode = (isset($voucher_order->postcode)) ? $voucher_order->postcode : ''; 
        $pay_method = (isset($voucher_order->pay_method)) ? $voucher_order->pay_method : ''; 
        $shipping_type = (isset($voucher_order->shipping_type)) ? $voucher_order->shipping_type : ''; 
        $shipping_email = (isset($voucher_order->shipping_email)) ? $voucher_order->shipping_email : ''; 
        $shipping_method = (isset($voucher_order->shipping_method)) ? $voucher_order->shipping_method : '';

        if($shipping_method == "undefined")
        {
            $shipping_method = '';                
        }

        $expiry = (isset($voucher_order->expiry)) ? $voucher_order->expiry : ''; 
        $couponcode = (isset($voucher_order->couponcode)) ? $voucher_order->couponcode : ''; 
        $voucherpdf_link = (isset($voucher_order->voucherpdf_link)) ? $voucher_order->voucherpdf_link : ''; 

        $uploads = wp_upload_dir();
        $upload_path = $uploads['baseurl'];

        $full_path_voucherpdf_link = $upload_path."/voucherpdfuploads/".$voucherpdf_link.".pdf";

        $voucheradd_time = (isset($voucher_order->voucheradd_time)) ? $voucher_order->voucheradd_time : ''; 
        $status = (isset($voucher_order->status)) ? $voucher_order->status : ''; 
        $payment_status = (isset($voucher_order->status)) ? $voucher_order->payment_status : ''; 
        
        fputcsv($file, array($id,$order_type,$template_id,$template_name,$itemcat_id,$itemcat_name,$item_id,$item_name,$buying_for,$from_name,$to_name,$amount_with_currency,$message,$firstname,$lastname,$email,$address,$postcode,$pay_method,$shipping_type,$shipping_email,$shipping_method,$expiry,$couponcode,$full_path_voucherpdf_link,$voucheradd_time,$status,$payment_status));

        // fputcsv($file, array($id,$order_type,$template_id,$itemcat_id,$item_id,$buying_for,$from_name,$to_name,$amount,$message,$firstname,$lastname,$email,$address,$postcode,$pay_method,$shipping_type,$shipping_email,$shipping_method,$expiry,$couponcode,$voucherpdf_link,$voucheradd_time,$status,$payment_status));

    }
    exit();
}