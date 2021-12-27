<?php

$invoice = new WPGV_PDF('P','pt',array(595,900));
$invoice->SetAutoPageBreak(0);
$invoice->AddPage();
if(get_locale() == "el"){
	$invoice->AddFont('Arial_Greek', '', 'Arial_Greek.php');
	$invoice->SetFont('Arial_Greek', '', 14);
}
$invoice->SetTextColor(0,0,0);

$invoice->SetXY(0, 0);
$invoice->SetFillColor($invoice_color[0], $invoice_color[1], $invoice_color[2]);
$invoice->Cell(60,900,'',0,1,'L',1);

$invoice->SetXY(65, 0);
$invoice->SetFillColor($invoice_color[0], $invoice_color[1], $invoice_color[2]);
$invoice->Cell(05,900,'',0,1,'L',1);


if(get_locale() != "el"){
	$invoice->SetFont('Arial','');
}
$invoice->SetTextColor($invoice_color[0], $invoice_color[1], $invoice_color[2]);
$invoice->SetFontSize(12);

if($invoice_options->company_logo != ''){
	$invoice->Image($invoice_options->company_logo, 80, 30, 80, 60);
}
else{
	$invoice->SetXY(82, 80);
	$invoice->Cell(0,0,$invoice_options->company_name,0,1,'L',0);
}

$invoice->SetXY(82, 105);
$invoice->Cell(0,0,wpgv_em(__('Invoice - #'.$lastid, 'gift-voucher')),0,1,'L',0);

/* Admin Details */

$invoice->SetXY(420, 35);
$invoice->SetTextColor(0,0,0);
$invoice->writeHTML('<b>'.$invoice_options->company_name.'</b>');

$invoice->SetXY(420, 60);
$invoice->Cell(0,0,$invoice_options->address_line1,0,1,'L',0);

$invoice->SetXY(420, 75);
$invoice->Cell(0,0,$invoice_options->address_line2,0,1,'L',0);

$invoice->SetXY(420, 90);
$invoice->Cell(0,0,$invoice_options->address_line3,0,1,'L',0);

$invoice->SetXY(420, 105);
$invoice->Cell(0,0,$invoice_options->address_line4,0,1,'L',0);

/* Seller Details */

$invoice->SetXY(82, 220);
$invoice->SetTextColor(0,0,0);
$invoice->writeHTML('<b>'.$seller_company_name.'</b>');

$invoice->SetXY(82, 245);
$invoice->Cell(0,0,wpgv_em($seller_addr_line1),0,1,'L',0);

$invoice->SetXY(82, 260);
$invoice->Cell(0,0,wpgv_em($seller_addr_line2),0,1,'L',0);

$invoice->SetXY(82, 275);
$invoice->Cell(0,0,wpgv_em($seller_addr_line3),0,1,'L',0);

$invoice->SetXY(82, 290);
$invoice->Cell(0,0,wpgv_em($seller_addr_line4),0,1,'L',0);


/* Voucher Details */

$invoice->SetXY(380, 225);
$invoice->writeHTML('<b>'.__('Your Name', 'gift-voucher').':</b>');

$invoice->SetXY(497, 225);
$invoice->Cell(0,0,' '.wpgv_em($for),0,1,1);

if($buyingfor != 'yourself') {
	$invoice->SetXY(380, 240);
	$invoice->writeHTML('<b>'.__('Recipient Name', 'gift-voucher').':</b>');

	$invoice->SetXY(500, 240);
	$invoice->Cell(0,0,wpgv_em(__($from, 'gift-voucher')),0,1,'L',0);
}

$invoice->SetXY(380, 255);
$invoice->writeHTML('<b>'.__('Expiry Date', 'gift-voucher').':</b>');

$invoice->SetXY(500, 258);
$invoice->Cell(0,0,wpgv_em($expiry),0,1,1);

$invoice->SetXY(380, 270);
$invoice->writeHTML('<b>'.__('Personal Message', 'gift-voucher').':</b>');

$invoice->SetXY(380, 290);
$invoice->writeHTML(wpgv_em($message));

$invoice->SetXY(82, 365);
$invoice->SetFillColor($invoice_color[0], $invoice_color[1], $invoice_color[2]);
$invoice->Cell(480,25,'',0,1,'L',1);

$invoice->SetXY(90, 365);
$invoice->Cell(480,25,__('Voucher Name', 'gift-voucher'),0,1,1);

if($formtype == 'item') {
	//Title
	$invoice->SetXY(80, 390);
	$invoice->MultiCell(100, 25, wpgv_em(get_the_title($itemid)), 0, 'C');
} else {
	//Voucher
	$invoice->SetXY(80, 390);
	$invoice->MultiCell(100,25, wpgv_em($template_options->title),0,'C');
}

$invoice->SetXY(510, 365);
$invoice->Cell(480,25,__('Price', 'gift-voucher'),0,1,1);

$invoice_price = ($special_price) ? wpgv_price_format($special_price) : $currency;
$invoice->SetXY(510, 390);
$invoice->MultiCell(900,25, wpgv_em($invoice_price),0,1,0);

$invoice->SetXY(83, 415);
$invoice->SetFillColor(0, 0, 0);
$invoice->MultiCell(480,0.5, '' ,0,1,'L',0);

$invoice->SetXY(440, 420);
$invoice->MultiCell(900,25, __('Sub Total:', 'gift-voucher'),0,1,0);

$invoice->SetXY(510, 420);
$invoice->MultiCell(900,25, wpgv_em($invoice_price),0,1,0);

if($wpgv_add_extra_charges > 0){
	$invoice->SetXY(440, 435);
	$invoice->MultiCell(900,25, __('Charges: +', 'gift-voucher'),0,1,0);

	$invoice->SetXY(510, 435);
	$invoice->MultiCell(300,25, wpgv_price_format($wpgv_add_extra_charges),0,1,0);
}

if($shipping_charges > 0){
	$invoice->SetXY(440, 450);
	$invoice->MultiCell(900,25, __('Shipping: +', 'gift-voucher'),0,1,0);

	$invoice->SetXY(510, 450);
	$invoice->MultiCell(300,25, wpgv_price_format($shipping_charges),0,1,0);
}

$invoice->SetXY(440, 480);
$invoice->SetFillColor(0, 0, 0);
$invoice->MultiCell(120,1, '' ,0,1,'L',0);

/* Total */
$invoice->SetXY(440, 485);
$invoice->MultiCell(900,25, __('Total:', 'gift-voucher'),0,1,0);

$invoice->SetXY(510, 485);
$invoice->MultiCell(300,25, wpgv_price_format($value) ,0,1,0);

/* Footer */

$invoice->SetXY(80,885);
$invoice->SetTextColor(0,0,0);
$invoice->SetFontSize(9);
$invoice->Cell(0,0,'* '.wpgv_em(__($invoice_options->bottom_line, 'gift-voucher')),90);