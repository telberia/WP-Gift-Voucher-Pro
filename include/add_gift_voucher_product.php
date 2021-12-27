<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
 * Include Scripts
 */

if ( !current_user_can( 'manage_options' ) )
{
	wp_die( 'You are not allowed to be on this page.' );
}
if( ! class_exists( 'WPGV_Voucher_Image' ) ) {

	class WPGV_Voucher_Image {

		public function init() {
			add_action( 'admin_footer', array( $this, 'add_script' ) );
		}

		public function load_media() {
		    wp_enqueue_media();
		}

		public function add_script(){
			?>
			<script type="text/javascript">
				jQuery(function($){
				/*
				 * Select/Upload image(s) event
				 */
					$('body').on('click', '.wpgv_upload_image_button', function(e){
						e.preventDefault();
				 
				    		var button = $(this),
				    		    custom_uploader = wp.media({
								title: 'Insert image',
								library : {
								// uncomment the next line if you want to attach image to the current post
								// uploadedTo : wp.media.view.settings.post.id, 
								type : 'image'
							},
							button: {
								text: 'Use this image' // button label text
							},
							multiple: false // for multiple image selection set to true
						}).on('select', function() { // it also has "open" and "close" events 
							var attachment = custom_uploader.state().get('selection').first().toJSON();
							$(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:95%;display:block;" width="80" height="80"/>').next().val(attachment.id).next().show();
						})
						.open();
					});
				 
					/*
					 * Remove image event
					 */
					$('body').on('click', '.wpgv_remove_image_button', function(){
						$(this).hide().prev().val('').prev().addClass('button').html('Upload image');
						return false;
					});
				});
			</script>
			<?php
		}

		public function wpgv_image_uploader_field( $name, $value = '') {
			$image = ' button">Upload image';
			$image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
			$display = 'none'; // display state ot the "Remove image" button
		 
			if( $image_attributes = wp_get_attachment_image_src( $value, $image_size ) ) {
				$image = '"><img src="' . $image_attributes[0] . '" style="display:block;" width="100" height="100" />';
				$display = 'inline-block';
			}
		 
			return '
			<div style="margin-top: 20px;margin-bottom: 20px;" >
				<a href="#" class="wpgv_upload_image_button' . $image . '</a>
				<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . esc_attr( $value ) . '"/>
				<a href="#" class="wpgv_remove_image_button" style="display:inline-block;display:' . $display . '">Remove image</a>
			</div>';
		}
	}
	$WPGV_Image = new WPGV_Voucher_Image();
	$WPGV_Image->init();
}

if(isset($_REQUEST['add_product'])){
	global $wpgv_gift_voucher;

	$gift_voucher_product = new wpgv_wc_product_gift_voucher();

	$wpgv_gift_voucher->set_current_currency_to_default();

	$title = sanitize_text_field($_REQUEST['title']);
	$description = sanitize_text_field($_REQUEST['description']);
	$exploded_price = explode(',', $_REQUEST['price']);
	$attach_id = sanitize_text_field($_REQUEST['Upload_Image']);

	$gift_voucher_product->set_props( array(
		'name' => $title	
	) );

	$gift_voucher_product->save();

	$post_id = $gift_voucher_product->get_id();

	$update_post = array(
		'ID' =>  $post_id,
		'post_content'  => $description,
		'post_status'   => 'draft',
		'post_type' => 'product'
	);

	wp_update_post( $update_post );

	set_post_thumbnail( $post_id, $attach_id );
	$term = get_term_by('name', 'Gift Voucher', 'product_cat');

	if($term->term_id == ''){ $category_id = wp_insert_term('Gift Voucher','product_cat',array('description'=> '','slug' => 'wpgv-gift-voucher')); }else{ $category_id = $term->term_id; }

	$serialize_array['wpgv-voucher-amount'] = $exploded_price;
	$serialized_data = serialize($serialize_array);

	wp_set_object_terms( $post_id, $category_id, 'product_cat');
	wp_set_object_terms( $post_id, 'gift_voucher', 'product_type' );

	$number_of_prices = count($exploded_price);

	for ($i = 0; $i < $number_of_prices; $i++) { 
		$gift_voucher_product->add_amount( $exploded_price[$i] );
	}

	$url = admin_url('admin.php?page=add-gift-voucher-product&msg=1');
	wp_safe_redirect( $url );
	exit;
}
if(isset($_GET['msg']) && $_GET['msg'] == 1)
{
	?>
	<div class="updated notice is-dismissible"><p><?php _e( 'Gift Voucher Product added as Draft', 'gift-voucher' ); ?></p></div>
	<?php
}
?>
<div class="wrap wpgiftv-settings">
	<h1><?php echo __( 'Add Gift Voucher Product', 'gift-voucher' ); ?></h1>
	<hr>
	<div class="wpgiftv-row">
		<div class="wpgiftv-col75">
			<div class="white-box add_gift_white_box">
				<form action="#" method="post" enctype="multipart/form-data">
					<h3><?php echo __( 'Add Gift Voucher Product', 'gift-voucher' ); ?></h3>
					<p class="post-attributes-label-wrapper">
						<label class="post-attributes-label" for="title"><?php echo __('Title'); ?>:</label>
					</p>
					<input required type="text" name="title" id="title" class="widefat" value="">

					<p class="post-attributes-label-wrapper">
						<label class="post-attributes-label" for="description"><?php echo __('Description'); ?>(20 Words):</label>
					</p>
					<textarea name="description" id="description" class="widefat"></textarea>
					<div class="dt_hr dt_hr-bottom"></div>

					<p class="post-attributes-label-wrapper">
						<label class="post-attributes-label" for="price"><?php echo __('Item Price (Separate multiple price with a comma.) '); ?>:</label>
					</p>
					<input required type="text" name="price" id="price" class="widefat" value="">
					<div class="dt_hr dt_hr-bottom"></div>

					<?php
						$WPGV_Image->load_media();
						echo $WPGV_Image->wpgv_image_uploader_field("Upload Image",'');
					?>

					<p class="post-attributes-label-wrapper">
						<input type="submit" name="add_product" value="Add Product" class="voucherPaymentButton">
					</p>
					
				</form>
			</div>
		</div>
	</div>
</div>