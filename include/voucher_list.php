<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

global $wpdb;

$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
$items = isset($_GET['items']) ? $_GET['items'] : '';
$wcproductorder = isset($_GET['wcproductorder']) ? $_GET['wcproductorder'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$voucher_code = isset($_GET['voucher_code']) ? $_GET['voucher_code'] : '';
?>
<div class="wrap voucher-page">
	<h1><?php echo __( 'Voucher Orders', 'gift-voucher' ) ?></h1><br>
	<div class="content">
    	<?php
    		$sql = "SELECT id, amount FROM {$wpdb->prefix}giftvouchers_list WHERE `status` = 'unused' AND `payment_status` = 'Paid' ORDER BY `id` DESC";
			$orders = $wpdb->get_results( $sql, ARRAY_A );
			$columns = array();
			$amount = 0;
    		foreach($orders as $row) {
        		$columns[] = $row['id'];
        		$amount += $row['amount'];
    		}
   		do_wpgv_check_voucher_status();
    		$num_fields = count($columns);
			if ( $num_fields > 0 ) { ?>
			<div class="total-unused">
     			<div class="count">
     				<span><?php echo $num_fields ?></span><?php echo __('Unused Gift Vouchers', 'gift-voucher'); ?>
     			</div>
     			<div class="amount">
     				<span><?php echo wpgv_price_format($amount) ?></span><?php echo __('Total Unused Voucher Amount', 'gift-voucher'); ?>
     			</div>
				<form action="<?php echo admin_url( 'admin.php' ); ?>">
					<input type="hidden" name="page" value="vouchers-lists">
					<?php if($wcproductorder): ?><input type="hidden" name="wcproductorder" value="1"><?php endif; ?>
					<?php if($items): ?><input type="hidden" name="items" value="1"><?php endif; ?>
					<input type="hidden" name="search" value="1">
        			<input type="text" name="voucher_code" autocomplete="off" placeholder="Search by Gift voucher code or email" value="<?php echo $voucher_code ?>" style="width: 300px;">
        			<input type="submit" class="button button-primary" value="Search">
    			</form>
     		</div>
			<?php } ?>
		<?php //$this->export_orders(); ?>
		<!-- <a href="<?php echo admin_url( 'edit.php' ); ?>?post_type=wpgv_voucher_product&page=import-orders" class="button button-primary" style="display: inline-block;padding: 0 10px;float:right;"><?php echo __( 'Import Vouchers', 'gift-voucher' ) ?></a> -->
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if(!$items && $tab != "import-order"): ?>nav-tab-active<?php endif; ?>" href="?page=vouchers-lists"><?php echo __( 'Purchased Voucher Codes', 'gift-voucher' ) ?></a>
			<a class="nav-tab <?php if($items && $tab != "import-order"): ?>nav-tab-active<?php endif; ?>" href="?page=vouchers-lists&items=1"><?php echo __( 'Purchased Items', 'gift-voucher' ) ?></a>
			<a class="nav-tab <?php if($wcproductorder && $tab != "import-order"): ?>nav-tab-active<?php endif; ?>" href="?page=vouchers-lists&wcproductorder=1"><?php echo __( 'Purchased Voucher Products', 'gift-voucher' ) ?></a>
			<a class="nav-tab <?php if($tab == "new-order"): ?>nav-tab-active<?php endif; ?>" href="?page=add-gift-voucher-order"><?php echo __( 'Add New Gift Voucher Order', 'gift-voucher' ) ?></a>
			<!-- <a class="nav-tab <?php if($tab == "import-order"): ?>nav-tab-active<?php endif; ?>" href="?page=vouchers-lists&tab=import-order"><?php echo __( 'Import Vouchers', 'gift-voucher' ) ?></a> -->
			<a class="nav-tab" href="?page=export_voucher_order"><?php echo __( 'Export Vouchers', 'gift-voucher' ) ?></a>
		</h2>
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<div <?php if($tab == "import-order") { ?> style="display:none;" <?php } ?>>
						<form method="post">
							<?php
								$this->vouchers_obj->prepare_items();
								$this->vouchers_obj->display(); 
							?>
						</form>
					</div>
					<div <?php if($tab == "import-order") { ?> style="display:block;" <?php } else { ?> style="display:none;" <?php } ?>>
						<?php
							if(isset($_GET["msg"]))
							{
								if($_GET["msg"] == "1")
								{
									echo "<br><b style='color:red;'>Please Select CSV File</b><br>";
								}
								else if($_GET["msg"] == "2")
								{

									echo "<br> <b style='color:#46b450;'>".$_GET["import"]." Order Imported Successfully </b><br>";
								}
								else if($_GET["msg"] == "3")
								{

									echo "<br><b style='color:red;'>Please Select Valid CSV File</b><br>";
								}
							}
						?>
						<br>
						<form method="post" action="<?php echo admin_url("admin.php"); ?>?page=import_voucher_order" enctype='multipart/form-data'>
							<input type="file" name="voucher_oreder_import_file"><br>
							<input type="submit" value="Import" name="voucher_oreder_import_btn" class="btn button-primary" id="voucher_oreder_import_btn">
		    			</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>