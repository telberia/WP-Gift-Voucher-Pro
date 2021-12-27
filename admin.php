<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WPGiftVoucherAdminPages Class for add Admin Pages in Menu
*/
class WPGiftVoucherAdminPages
{
	// class instance
	static $instance;

	// Voucher WP_List_Table object
	public $vouchers_obj;

	public function __construct()
	{
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_assets' ) );
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Admin CSS and JS Files
	 */
	function admin_register_assets( $hook )
	{
        wp_enqueue_style( 'wp-color-picker' ); 
       	wp_enqueue_style( 'voucher-style', WPGIFT__PLUGIN_URL.'/assets/css/admin-style.css');
		wp_enqueue_style( 'font-style', WPGIFT__PLUGIN_URL.'/assets/css/voucher-template-fonts.css');
       	wp_enqueue_style( 'wpgb-admin-step-style', WPGIFT__PLUGIN_URL.'/assets/css/admin-step.css');
       	wp_enqueue_script('wpgv-admin-jquery-validate', WPGIFT__PLUGIN_URL  . '/assets/js/jquery.validate.min.js', array('jquery'), '1.17.0', true);
       	wp_enqueue_script('wpgv-admin-jquery-step', WPGIFT__PLUGIN_URL  . '/assets/js/jquery.steps.min.js', array('jquery'), '1.1.0', true);
       	wp_enqueue_script('voucher-script', WPGIFT__PLUGIN_URL  . '/assets/js/admin-script.js', array( 'wp-color-picker' ), '1.0.0', true);
		wp_enqueue_script('konva-js', WPGIFT__PLUGIN_URL  . '/assets/js/konva.min.js', array('jquery'), 'v8.0.4', true);

		wp_localize_script( 'voucher-script', 'WPGiftAjax', array('ajaxurl' => admin_url( 'admin-ajax.php' ),));
    }

    /**
	 * Voucher Menu page
	 */
	public function plugin_menu() 
	{
		add_menu_page('Gift Cards Pro', 'Gift Cards Pro', 'read', 'wpgv-gift-cards', '', 'dashicons-tickets-alt', 25);
		add_submenu_page('wpgv-gift-cards', 'Item Categories', 'Item Categories', 'edit_posts', 'edit-tags.php?taxonomy=wpgv_voucher_category&post_type=wpgv_voucher_product',false );		
		add_submenu_page('wpgv-gift-cards', 'Voucher Categories', 'Gift Cards Categories', 'edit_posts', 'edit-tags.php?taxonomy=category_voucher_template&post_type=voucher_template',false );
		$templatehook = add_submenu_page( 'wpgv-gift-cards', __('Voucher Templates', 'gift-voucher' ), __('Voucher Templates', 'gift-voucher' ), 'manage_options', 'voucher-templates', array( $this, 'voucher_template' ));
		add_submenu_page( NULL, __('Add New Template', 'gift-voucher' ), __('Add New Template', 'gift-voucher' ), 'manage_options', 'new-voucher-template', array( $this, 'new_voucher_template' ));

		add_submenu_page( NULL, __('View Voucher Details', 'gift-voucher' ), __('View Voucher Details', 'gift-voucher' ), 'manage_options', 'view-voucher-details', array( $this, 'view_voucher_details' ));

		add_submenu_page( 'wpgv-gift-cards', __('Settings', 'gift-voucher' ), __('Settings', 'gift-voucher' ), 'manage_options', 'voucher-setting', array( $this, 'voucher_settings' ));

		add_submenu_page( 'wpgv-gift-cards', __('Invoice Settings', 'gift-voucher' ), __('Invoice Settings', 'gift-voucher' ), 'manage_options', 'invoice-setting', array( $this, 'invoice_settings' ));
		if(wpgv_is_woocommerce_enable()){

			$producthook = add_submenu_page( 'wpgv-gift-cards', __('Voucher Products', 'gift-voucher' ), __('Voucher Products', 'gift-voucher' ), 'manage_options', 'voucher-products', array( $this, 'voucher_products' ));
			add_submenu_page( 'wpgv-gift-cards', __('Add Gift Voucher Product', 'gift-voucher' ), __('Add Gift Voucher Product', 'gift-voucher' ), 'manage_options', 'add-gift-voucher-product', array( $this, 'add_gift_voucher_product' ));
		}
		add_submenu_page( 'wpgv-gift-cards', __('Add Gift Voucher Order', 'gift-voucher' ), __('Add Gift Voucher Order', 'gift-voucher' ), 'manage_options', 'add-gift-voucher-order', array( $this, 'add_gift_voucher_order' ));
		$hook = add_submenu_page('wpgv-gift-cards', __('Gift Voucher Orders', 'gift-voucher' ), __('Gift Voucher Orders', 'gift-voucher' ), 'manage_options', 'vouchers-lists', array( $this, 'voucher_list' ) );
		add_submenu_page('wpgv-gift-cards', __('Check Voucher Balance', 'gift-voucher' ), __('Check Voucher Balance', 'gift-voucher' ), 'manage_options', 'check-vouchers-balance', array( $this, 'check_voucher_balance' ) );
		add_submenu_page('wpgv-gift-cards', __('Activate License', 'gift-voucher' ), __('Plugin License', 'gift-voucher' ), 'manage_options', 'wpgv_license_page', array( $this, 'wpgv_license_page' ) );
		add_submenu_page( NULL, __('Export Order', 'gift-voucher' ),__('Export Order', 'gift-voucher' ),'manage_options','export_voucher_order',array( $this, 'export_voucher_order' ) );
		add_submenu_page( NULL, __('Import Order', 'gift-voucher' ),__('Import Order', 'gift-voucher' ),'manage_options','import_voucher_order',array( $this, 'import_voucher_order' ) );
		
		add_action( "load-$hook", array( $this, 'screen_option_voucher' ) );
		add_action( "load-$templatehook", array( $this, 'screen_option_template' ) );
		if(wpgv_is_woocommerce_enable()){
			add_action( "load-$producthook", array( $this, 'screen_option_products' ) );
		}
		
	}

	/**
	 * Voucher List page
	 */
	public function voucher_list()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/voucher_list.php';
	}

	/**
	 * Voucher List page
	 */
	public function check_voucher_balance()
	{
		echo '<h3>'.__('Check Voucher Balance', 'gift-voucher').'</h3>';
		echo do_shortcode( ' [wpgv-check-voucher-balance] ' );
	}

	/**
	 * Method for view details of an voucher
	 */
	public function view_voucher_details() 
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/view_voucher_details.php';
	}

	/**
	 * Voucher settings page
	 */
	public function voucher_settings() 
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/voucher_settings.php';
	}

	/**
	 * Voucher voucherproducts page
	 */
	public function voucher_products() 
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/voucher_products.php';
	}

	/**
	 * Invoice settings pafe
	*/

	public function invoice_settings() 
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/invoice_settings.php';
	}

	/**
	 * Voucher Template page
	 */
	public function voucher_template()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/voucher_template.php';
	}

	/**
	 * Add Voucher Template page
	 */
	public function new_voucher_template()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/new_voucher_template.php';
	}

	/**
	 * Redeem Voucher page
	 */
	public function redeem_voucher()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/redeem_voucher.php';
	}

	/**
	 * Plugin License page
	 */	
	public function wpgv_license_page()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/wpgv_license_page.php';
	}
	
	/**
	 *  Voucher Export 
	 */	
	public function export_voucher_order()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/export_voucher_order.php';
	}

	/**
	 *  Voucher Export 
	 */	
	public function import_voucher_order()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/import_voucher_order.php';
	}

	/**
	 *  Add New Gift Voucher Order
	 */	
	public function add_gift_voucher_order()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/add_gift_voucher_order.php';
	}

	/**
	* Add Gift voucher type product
	*/
	public function add_gift_voucher_product()
	{
		require_once WPGIFT__PLUGIN_DIR . '/include/add_gift_voucher_product.php';
	}

	/**
	 * Method for export vouchers in xls
	 */
	function export_orders()
	{
    	if(is_admin()) {
        	global $wpdb;
            if(isset($_POST["tbl_name"])){
                $tablename = sanitize_text_field($_POST["tbl_name"]);
                $sql = "SHOW TABLES";
                $table_list  = $wpdb->get_results($sql,ARRAY_N);
                $IsValidTableName = 0;
                foreach($table_list as $table_name){
                    foreach ($table_name as $singlevalue){
                        if($singlevalue == $tablename){
                            $IsValidTableName = 1;
                        }
                    }
                }
                if($IsValidTableName==1){
					$filename = "export-orders";
                    $cols = $wpdb->get_results("DESCRIBE $tablename");
                    $cols = $wpdb->get_results("SELECT * FROM $tablename");
                    $sql = $wpdb->get_results("SELECT COUNT(*) FROM $tablename");
		            ob_clean();
                    header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
                    header("Content-Disposition: attachment; filename= ".$filename."-".date('d-m-y').".xls");  //File name extension was wrong
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false);
                    echo "<html>";
                    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
                    echo "<body>";
                    echo "<table>";
                    print("<tr>");
                    foreach ($cols as $col) {
                    	echo "<th  style='border: thin solid; background-color: #83b4d8;'>" . $col->Field . "</th>";
                    }
                    print("</tr>");
                    while($row = mysql_fetch_row($result)){
                        $output = '';
                        $output = "<tr>";
                        for($j=0; $j<mysql_num_fields($result); $j++){
                            if(!isset($row[$j]))
                                $output .= "<td>NULL\t</td>";
                            else
                                $output .= "<td style='border: thin solid;'>$row[$j]\t</td>";
                        }
                        $output .= "</tr>";
                        $output = preg_replace("/\r\n|\n\r|\n|\r/", ' ', $output);
                        print(trim($output));
                    }
                    echo "</table>";
                    echo "</body>";
                    echo "</html>";
                }
                else{
                    echo __('Invalid Request.', 'gift-voucher' );
                }
            }
	?>
		<form action="" method="POST" style="display: inline-block;padding: 0 10px;float:right;">
            <input type="hidden" name="tbl_name" value="<?php echo $wpdb->prefix . "giftvouchers_list"; ?>"/>
            <input class="button button-primary exportbtn" name="exportbtn" type="submit" name="table_display" value="<?php echo __('Export All Orders', 'gift-voucher' ) ?>"/>
		</form>
		<?php 
		} 
	}

	/**
	 * Screen options for voucher list
	 */
	public function screen_option_voucher()
	{
		$option = 'per_page';
		$args   = array(
			'label'   => __('Gift Vouchers', 'gift-voucher'),
			'default' => 20,
			'option'  => 'vouchers_per_page'
		);

		add_screen_option( $option, $args );

		$this->vouchers_obj = new WPGV_Voucher_List();
	}

	/**
	 * Screen options for voucher templates
	 */
	public function screen_option_template()
	{
		$option = 'per_page';
		$args = array(
			'label'   => __('Voucher Templates', 'gift-voucher'),
			'default' => 20,
			'option'  => 'templates_per_page'
		);

		add_screen_option( $option, $args );

		$this->vouchers_obj = new WPGV_Voucher_Template();
	}

	/**
	 * Screen options for voucher templates
	 */
	public function screen_option_products()
	{
		$option = 'per_page';
		$args = array(
			'label'   => __('Voucher Products', 'gift-voucher'),
			'default' => 20,
			'option'  => 'products_per_page'
		);

		add_screen_option( $option, $args );

		$this->vouchers_obj = new WPGV_Voucher_Product_List();
	}

	/** Singleton instance */
	public static function get_instance() 
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Method for get image url by id (Only for Template Page)
 */
function wpgv_get_image_url($ids)
{
	$images = $ids ? json_decode($ids) : [''];
	foreach ($images as $key => $value) {
		if($value):
			$image_attributes = wp_get_attachment_image_src( $value, 'voucher-thumb' );
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('.image_src<?php echo $key; ?>').attr('src', '<?php echo $image_attributes[0]; ?>').show();
					$('.remove_image<?php echo $key; ?>').show();
				});
			</script>
			<?php
		endif;
	}
}

add_action( 'admin_post_save_voucher_settings_option', 'process_voucher_settings_options' );

function wpgv_pinker_color() {
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
}
add_action('wp_enqueue_scripts', 'wpgv_pinker_color');


function wpgv_upload_image_voucher() {
    echo 'Hello World';
    // Some interesting server side stuff
}

add_action('wp_ajax_nopriv_wpgv_upload_image_voucher', 'wpgv_upload_image_voucher');
add_action('wp_ajax_wpgv_upload_image_voucher', 'wpgv_upload_image_voucher');