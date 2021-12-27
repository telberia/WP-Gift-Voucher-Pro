<?php

$receipt = new WPGV_PDF('P','pt',array(595,900));
$receipt->SetAutoPageBreak(0);
$receipt->AddPage();
if(get_locale() == "el"){
	$receipt->AddFont('Arial_Greek', '', 'Arial_Greek.php');
	$receipt->SetFont('Arial_Greek', '', 14);
}
$receipt->SetTextColor(0,0,0);

//Title
$receipt->SetXY(30, 50);
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B',16);
}
$receipt->SetFontSize(20);
$receipt->MultiCell(0, 0, wpgv_em(__('Customer Receipt', 'gift-voucher')), 0, 'C');

$receipt->SetFontSize(12);

//Company Name
if(get_locale() != "el"){
	if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
}
$receipt->SetXY(30, 100);
$receipt->Cell(0,0,wpgv_em(__('Company Name', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 100);
$receipt->Cell(0,0,' '.wpgv_em($setting_options->company_name),0,1,'L',0);

//Company Email
if(get_locale() != "el"){
	if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
}
$receipt->SetXY(30, 120);
$receipt->Cell(0,0,wpgv_em(__('Company Email', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 120);
$receipt->Cell(0,0,' '.wpgv_em($setting_options->pdf_footer_email),0,1,'L',0);

//Company Website
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 140);
$receipt->Cell(0,0,wpgv_em(__('Company Website', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 140);
$receipt->Cell(0,0,' '.wpgv_em($setting_options->pdf_footer_url),0,1,'L',0);

//Order Number
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 160);
$receipt->Cell(0,0,wpgv_em(__('Order Number', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 160);
$receipt->Cell(0,0,wpgv_em(__(' #'.$lastid, 'gift-voucher')),0,1,'L',0);

//Order Date
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 180);
$receipt->Cell(0,0,wpgv_em(__('Order Date', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 180);
$receipt->Cell(0,0,' '.date('d.m.Y'),0,1,'L',0);

//For
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 200);
$receipt->Cell(0,0,wpgv_em(__('Your Name', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 200);
$receipt->Cell(0,0,' '.wpgv_em($for),0,1,'L',0);

//From
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 220);
if($buyingfor != 'yourself') {
	$receipt->Cell(0,0,wpgv_em(__('Recipient Name', 'gift-voucher')),0,1,'L',0);
	$receipt->SetFont('Arial','');
	$receipt->SetXY(250, 220);
	$receipt->Cell(0,0,' '.wpgv_em($from),0,1,'L',0);
}

//Email
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 240);
$receipt->Cell(0,0,wpgv_em(__('Email', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 240);
$receipt->Cell(0,0,' '.wpgv_em($email),0,1,'L',0);

//Amount
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 260);
$receipt->Cell(0,0,wpgv_em(__('Amount', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 260);
$receipt->Cell(0,0,' '.iconv('utf-8', 'cp1252', wpgv_price_format($value)),0,1,'L',0);

//Coupon Code
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 280);
$receipt->Cell(0,0,wpgv_em(__('Coupon Code', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 280);
$receipt->Cell(0,0,' '.wpgv_em($code),0,1,'L',0);

//Coupon Expiry date
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 300);
$receipt->Cell(0,0,wpgv_em(__('Coupon Expiry date', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 300);
$receipt->Cell(0,0,' '.wpgv_em($expiry),0,1,'L',0);

//Payment Method
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 320);
$receipt->Cell(0,0,wpgv_em(__('Payment Method', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 320);
$receipt->Cell(0,0,' '.wpgv_em($paytext),0,1,'L',0);

//Payment Status
if(get_locale() != "el"){
	$receipt->SetFont('Arial','B');
}
$receipt->SetXY(30, 340);
$receipt->Cell(0,0,wpgv_em(__('Payment Status', 'gift-voucher')),0,1,'L',0);
$receipt->SetFont('Arial','');
$receipt->SetXY(250, 340);
$receipt->Cell(0,0,' '.wpgv_em(__('Paid', 'gift-voucher')),0,1,'L',0);