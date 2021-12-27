<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

if( ! class_exists( 'WPGV_Voucher_Taxonomy_Image' ) ) {

class WPGV_Voucher_Taxonomy_Image {
    
    public function __construct() {
     //
    }

    /**
     * Initialize the class and start calling our hooks and filters
     */
    public function init() {
     // Image actions
     add_action( 'wpgv_voucher_category_add_form_fields', array( $this, 'add_category_image' ), 10, 2 );
     add_action( 'created_wpgv_voucher_category', array( $this, 'save_category_image' ), 10, 2 );
     add_action( 'wpgv_voucher_category_edit_form_fields', array( $this, 'update_category_image' ), 10, 2 );
     add_action( 'edited_wpgv_voucher_category', array( $this, 'updated_category_image' ), 10, 2 );
     add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
     add_action( 'admin_footer', array( $this, 'add_script' ) );
   }

   public function load_media() {
     if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'wpgv_voucher_category' ) {
       return;
     }
     wp_enqueue_media();
   }
  
   /**
    * Add a form field in the new category page
    * @since 1.0.0
    */
  
   public function add_category_image( $taxonomy ) { ?>
     <div class="form-field term-group">
       <label for="wpgv-voucher-category-image-id"><?php _e( 'Featured Image', 'gift-voucher' ); ?></label>
       <input type="text" id="wpgv-voucher-category-image-id" name="wpgv-voucher-category-image-id" class="custom_media_url" value="">
       <div id="category-image-wrapper"></div>
       <p>
         <input type="button" class="button button-secondary wpgv_voucher_tax_media_button" id="wpgv_voucher_tax_media_button" name="wpgv_voucher_tax_media_button" value="<?php _e( 'Add Image', 'gift-voucher' ); ?>" />
         <input type="button" class="button button-secondary wpgv_voucher_tax_media_remove" id="wpgv_voucher_tax_media_remove" name="wpgv_voucher_tax_media_remove" value="<?php _e( 'Remove Image', 'gift-voucher' ); ?>" />
       </p>
     </div>
   <?php }

   /**
    * Save the form field
    * @since 1.0.0
    */
   public function save_category_image( $term_id, $tt_id ) {
     if( isset( $_POST['wpgv-voucher-category-image-id'] ) && '' !== $_POST['wpgv-voucher-category-image-id'] ){
       add_term_meta( $term_id, 'wpgv-voucher-category-image-id', absint( $_POST['wpgv-voucher-category-image-id'] ), true );
     }
    }

    /**
     * Edit the form field
     * @since 1.0.0
     */
    public function update_category_image( $term, $taxonomy ) { ?>
      <tr class="form-field term-group-wrap">
        <th scope="row">
          <label for="wpgv-voucher-category-image-id"><?php _e( 'Featured Image', 'gift-voucher' ); ?></label>
        </th>
        <td>
          <?php $image_id = get_term_meta( $term->term_id, 'wpgv-voucher-category-image-id', true ); ?>
          <input type="text" id="wpgv-voucher-category-image-id" name="wpgv-voucher-category-image-id" value="<?php echo esc_attr( $image_id ); ?>">
          <div id="category-image-wrapper">
            <?php if( $image_id ) { ?>
              <?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
            <?php } ?>
          </div>
          <p>
            <input type="button" class="button button-secondary wpgv_voucher_tax_media_button" id="wpgv_voucher_tax_media_button" name="wpgv_voucher_tax_media_button" value="<?php _e( 'Add Image', 'gift-voucher' ); ?>" />
            <input type="button" class="button button-secondary wpgv_voucher_tax_media_remove" id="wpgv_voucher_tax_media_remove" name="wpgv_voucher_tax_media_remove" value="<?php _e( 'Remove Image', 'gift-voucher' ); ?>" />
          </p>
        </td>
      </tr>
   <?php }

   /**
    * Update the form field value
    * @since 1.0.0
    */
   public function updated_category_image( $term_id, $tt_id ) {
     if( isset( $_POST['wpgv-voucher-category-image-id'] ) && '' !== $_POST['wpgv-voucher-category-image-id'] ){
       update_term_meta( $term_id, 'wpgv-voucher-category-image-id', absint( $_POST['wpgv-voucher-category-image-id'] ) );
     } else {
       update_term_meta( $term_id, 'wpgv-voucher-category-image-id', '' );
     }
   }
 
   /**
    * Enqueue styles and scripts
    * @since 1.0.0
    */
   public function add_script() {
     if( ! isset( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'wpgv_voucher_category' ) {
       return;
     } ?>
     <script> jQuery(document).ready( function($) {
       _wpMediaViewsL10n.insertIntoPost = '<?php _e( "Insert", "gift-voucher" ); ?>';
       function ct_media_upload(button_class) {
         var _custom_media = true, _orig_send_attachment = wp.media.editor.send.attachment;
         $('body').on('click', button_class, function(e) {
           var button_id = '#'+$(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = $(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if( _custom_media ) {
               $('#wpgv-voucher-category-image-id').val(attachment.id);
               $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
               $( '#category-image-wrapper .custom_media_image' ).attr( 'src',attachment.url ).css( 'display','block' );
             } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
           }
           wp.media.editor.open(button); return false;
         });
       }
       ct_media_upload('.wpgv_voucher_tax_media_button.button');
       $('body').on('click','.wpgv_voucher_tax_media_remove',function(){
         $('#wpgv-voucher-category-image-id').val('');
         $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
       });
       // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
       $(document).ajaxComplete(function(event, xhr, settings) {
         var queryStringArr = settings.data.split('&');
         if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
           var xml = xhr.responseXML;
           $response = $(xml).find('term_id').text();
           if($response!=""){
             // Clear the thumb image
             $('#category-image-wrapper').html('');
           }
          }
        });
      });
    </script>
   <?php }
  }
$WPGV_Voucher_Taxonomy_Image = new WPGV_Voucher_Taxonomy_Image();
$WPGV_Voucher_Taxonomy_Image->init(); }



// Add the voucher Meta Boxes
function wpgv_add_voucher_metaboxes() {
  add_meta_box('wpgv_voucher_amount', __('Item Details'), 'wpgv_voucher_amount', 'wpgv_voucher_product', 'normal', 'default');
}
add_action( 'add_meta_boxes', 'wpgv_add_voucher_metaboxes' );

function wpgv_add_edit_form_multipart_encoding() {
    echo ' enctype="multipart/form-data"';
}
add_action('post_edit_form_tag', 'wpgv_add_edit_form_multipart_encoding');

// The vouchers Metabox
function wpgv_voucher_amount() {
  global $post;

  if(function_exists( 'wp_enqueue_media' )){
    wp_enqueue_media();
  } 
  else
  {
    wp_enqueue_style('thickbox');
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
  }
  
  // Noncename needed to verify where the data originated
  echo '<input type="text" name="voucher_meta_noncename" id="voucher_meta_noncename" value="'.wp_create_nonce(plugin_basename(__FILE__)).'" />';

  // Get the location data if its already been entered
  $description = get_post_meta($post->ID, 'description', true);
  $price = get_post_meta($post->ID, 'price', true);
  $special_price = get_post_meta($post->ID, 'special_price', true);
  $style1_image = get_post_meta($post->ID, 'style1_image', true);
  $style2_image = get_post_meta($post->ID, 'style2_image', true);
  $style3_image = get_post_meta($post->ID, 'style3_image', true);
  // Echo out the field
  echo '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="description">'.__('Description').': (20 Words)</label></p><textarea name="description" id="description" class="widefat">' . $description  . '</textarea><div class="dt_hr dt_hr-bottom"></div>';
  echo '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="price">'.__('Item Price').':</label></p><input type="number" name="price" id="price" class="widefat" value="' . $price  . '" step=".01"><div class="dt_hr dt_hr-bottom"></div>';
  echo '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="special_price">'.__('Item Special Price').':</label></p><input type="number" name="special_price" id="special_price" class="widefat" value="' . $special_price  . '" step=".01"><div class="dt_hr dt_hr-bottom"></div>';

  for ($i=1; $i < 4; $i++) {
    if(${'style'.$i.'_image'}):
      $image_attributes = wp_get_attachment_image_src( ${'style'.$i.'_image'}, 'voucher-thumb' );
      ?>
      <script type="text/javascript">
        jQuery(document).ready(function($) {
          $('.image_src<?php echo $i; ?>').attr('src', '<?php echo $image_attributes[0]; ?>').show();
          $('.remove_image<?php echo $i; ?>').show();
        });
      </script>
      <?php
    endif;
  }

  $sizearr = array('', '1000px x 760px', '1000px x 1500px', '1000px x 750px');
  for ($i=1; $i < 4; $i++) {
    echo '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="style'.$i.'_image">Image - Style '.$i.' (Recommended: '.$sizearr[$i].'):</label></p>';
    ?>
    <img class="image_src<?php echo $i; ?>" src="" width="100" style="display: none;" />
    <input class="image_url<?php echo $i; ?>" type="text" name="style<?php echo $i; ?>_image" size="60" value="<?php echo ${'style'.$i.'_image'}; ?>">
    <button type="button" class="upload_image<?php echo $i; ?> button"><?php echo __('Upload Image', 'gift-voucher' ) ?></button>
    <button type="button" class="button button-primary remove_image<?php echo $i; ?>" style="display: none;"><?php echo __('Remove Image', 'gift-voucher') ?></button><br>
  <?php } ?>
  <script>
      jQuery(document).ready(function($) {
        <?php for ($i=1; $i < 4; $i++) { ?>
          $('.upload_image<?php echo $i; ?>').click(function(e) {
              e.preventDefault();

              var custom_uploader = wp.media({
                  title: 'Add Voucher Image',
                  button: {
                      text: 'Upload Image'
                  },
                  multiple: false  // Set this to true to allow multiple files to be selected
              })
              .on('select', function() {
                  var attachment = custom_uploader.state().get('selection').first().toJSON();
                  $('.image_src<?php echo $i; ?>').attr('src', attachment.url).show();
                  $('.image_url<?php echo $i; ?>').val(attachment.id);
                  $('.remove_image<?php echo $i; ?>').show();
              })
              .open();
          });
          $('.remove_image<?php echo $i; ?>').click(function () {
            $('.image_src<?php echo $i; ?>').attr('src','').hide();
            $('.image_url<?php echo $i; ?>').val('');
              $('.remove_image<?php echo $i; ?>').hide();
          });
          <?php } ?>
      });
  </script>
  <?php
}

// Save the Metabox Data

function wpt_save_voucher_meta($post_id, $post) {
  
  $voucher_meta_noncename = !empty($_POST['voucher_meta_noncename']) ? $_POST['voucher_meta_noncename'] : "";
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce(  $voucher_meta_noncename, plugin_basename(__FILE__) )) {
  return $post->ID;
  }

  // Is the user allowed to edit the post or page?
  if ( !current_user_can( 'edit_post', $post->ID ))
    return $post->ID;

  // OK, we're authenticated: we need to find and save the data
  // We'll put it into an array to make it easier to loop though.
  $events_meta['description'] = $_POST['description'];
  $events_meta['price'] = $_POST['price'];
  $events_meta['special_price'] = $_POST['special_price'];
  $events_meta['style1_image'] = $_POST['style1_image'];
  $events_meta['style2_image'] = $_POST['style2_image'];
  $events_meta['style3_image'] = $_POST['style3_image'];
  
  // Add values of $events_meta as custom fields
  foreach ($events_meta as $key => $value) { // Cycle through the $events_meta array!
    if( $post->post_type == 'revision' ) return; // Don't store custom data twice
    $value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
    if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
      update_post_meta($post->ID, $key, $value);
    } else { // If the custom field doesn't have a value
      add_post_meta($post->ID, $key, $value);
    }
    if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
  }

}

add_action('save_post', 'wpt_save_voucher_meta', 1, 2); // save the voucher meta fields

class Template_Voucher { 
    private $screens = array(
        'voucher_template',
    );
    private $fields = array(
        
      array(
        'id' => 'bg_result',
        'type' => 'bg_result',
      ),
      array(
          'id' => 'status',
          'label' => 'Status',
          'type' => 'select',
          'options' => array(
            'Active',
            'Inactive',
          ),
        ),
        array(
          'id' => 'voucher_expiry_value',
          'label' => 'Voucher Expiry Value',
          'type' => 'date_expiry',
        ),
        array(
          'id' => 'select_template',
          'label' => 'Select Template',
          'type' => 'select_template',
          'options' => array(
            'default',
            'custom',
          ),
        ),
        array(
          'id' => 'chosse_template',
          'label' => 'Custom Template',
          'type' => 'chosse_template',
          'options' => array(
            'lanscape',
            'portail',
          ),
        ),
        array(
          'id' => 'json_template',
          'type' => 'json_template',
        ),
        array(
          'id' => 'id_bg_template',
          'type' => 'id_bg_template',
        ),
        
        array(
          'id' => 'check_temp_custom',
          'type' => 'check_temp_custom',
        ),
        array(
          'id' => 'get_chosse_temp',
          'type' => 'get_chosse_temp',
        ),
        array(
          'id' => 'template-style',
          'label' => 'Template Style (Left click to open the popup)',
          'type' => 'radio',
          'options' => array(
              'template-voucher-lanscape-1.png',
              'template-voucher-lanscape-2.png',
              'template-voucher-lanscape-3.png',
              'template-voucher-lanscape-4.png',
              'template-voucher-lanscape-5.png',
              'template-voucher-lanscape-6.png',
              'template-voucher-lanscape-7.png',
              'template-voucher-lanscape-8.png',
              'template-voucher-lanscape-9.png',
              'template-voucher-lanscape-10.png',
              'template-voucher-lanscape-11.png',
              'template-voucher-lanscape-12.png',
              'template-voucher-lanscape-13.png',
              'template-voucher-lanscape-14.png',
              'template-voucher-lanscape-15.png',
              'template-voucher-lanscape-16.png',
              'template-voucher-lanscape-17.png',
              'template-voucher-lanscape-18.png',
              'template-voucher-lanscape-19.png',
              'template-voucher-lanscape-20.png',
              'template-voucher-lanscape-21.png',
              'template-voucher-lanscape-22.png',
              'template-voucher-lanscape-23.png',
              'template-voucher-lanscape-24.png',
              'template-voucher-lanscape-25.png',
              'template-voucher-lanscape-26.png',
              'template-voucher-lanscape-27.png',
              'template-voucher-lanscape-28.png',
              'template-voucher-lanscape-29.png',
              'template-voucher-lanscape-30.png',
              'template-voucher-lanscape-31.png',
              'template-voucher-lanscape-32.png',
              'template-voucher-lanscape-33.png',
              'template-voucher-lanscape-34.png',
              'template-voucher-lanscape-35.png',
              'template-voucher-lanscape-36.png',
              'template-voucher-lanscape-37.png',
              'template-voucher-lanscape-38.png',
              'template-voucher-lanscape-39.png',
              'template-voucher-lanscape-40.png',
              'template-voucher-lanscape-41.png',
              'template-voucher-lanscape-42.png',
              'template-voucher-lanscape-43.png',
              'template-voucher-lanscape-44.png',
              'template-voucher-lanscape-45.png',
              'template-voucher-lanscape-46.png',
              'template-voucher-lanscape-47.png',
              'template-voucher-lanscape-48.png',
              'template-voucher-lanscape-49.png',
              'template-voucher-lanscape-50.png',
              'template-voucher-lanscape-51.png',
              'template-voucher-lanscape-52.png',
              'template-voucher-lanscape-53.png',
              'template-voucher-portail-1.png',
              'template-voucher-portail-2.png',
              'template-voucher-portail-3.png',
              'template-voucher-portail-4.png',
              'template-voucher-portail-5.png',
              'template-voucher-portail-6.png',
              'template-voucher-portail-7.png',
              'template-voucher-portail-8.png',
              'template-voucher-portail-9.png',
              'template-voucher-portail-10.png',
              'template-voucher-portail-11.png',
              'template-voucher-portail-12.png',
              'template-voucher-portail-13.png',
              'template-voucher-portail-14.png',
              'template-voucher-portail-15.png',
              'template-voucher-portail-16.png',
              'template-voucher-portail-17.png',
              'template-voucher-portail-18.png',
              'template-voucher-portail-19.png',
              'template-voucher-portail-20.png',
              'template-voucher-portail-21.png',
              'template-voucher-portail-22.png',
              'template-voucher-portail-23.png',
              'template-voucher-portail-24.png',
              'template-voucher-portail-25.png',
              'template-voucher-portail-26.png',
              'template-voucher-portail-27.png',
              'template-voucher-portail-28.png',
              'template-voucher-portail-29.png',
              'template-voucher-portail-30.png',
              'template-voucher-portail-31.png',
              'template-voucher-portail-32.png',
              'template-voucher-portail-33.png',
              'template-voucher-portail-34.png',
              'template-voucher-portail-35.png',
              'template-voucher-portail-36.png',
              'template-voucher-portail-37.png',
              'template-voucher-portail-38.png',
              'template-voucher-portail-39.png',
              'template-voucher-portail-40.png',
              'template-voucher-portail-41.png',
              'template-voucher-portail-42.png',
              'template-voucher-portail-43.png',
          ),
        ),
        array(
          'id' => 'modal_edit_temp',
          'type' => 'modal_edit_temp',
        ),
    );

    public function __construct() {
        add_action( 'add_meta_boxes', array($this,'add_meta_boxes') );
        add_action( 'admin_footer', array( $this, 'admin_footer' ) );
        add_action( 'save_post', array( $this, 'save_post' ) );
    }

    // Add the voucher Meta Boxes
    public function add_meta_boxes() {
        foreach ( $this->screens as $screen ) {
            add_meta_box(
                'customize-template',
                __( 'Customize Template', 'gift-voucher' ),
                array( $this, 'add_meta_box_callback' ),
                $screen,
                'normal',
                'high'
            );
        }
    }

    /**
     * Generates the HTML for the meta box
     * 
     * @param object $post WordPress post object
     */
    public function add_meta_box_callback( $post ) {
        wp_nonce_field( 'wpgv_customize_template_data', 'wpgv_customize_template_nonce' );
        $this->generate_fields( $post );
        if(get_post_meta( $post->ID, 'wpgv_customize_template_template-style', true )) {
            //echo '<a href="#" class="button">See Sample Preview</a>';           
        }
    }

    /**
     * Hooks into WordPress' admin_footer function.
     * Adds scripts for media uploader.
     */
  public function admin_footer() {
      ?><script>
          // https://codestag.com/how-to-use-wordpress-3-5-media-uploader-in-theme-options/
          jQuery(document).ready(function($){
              if ( typeof wp.media !== 'undefined' ) {
                  var _custom_media = true,
                  _orig_send_attachment = wp.media.editor.send.attachment;
                  $('.rational-metabox-media').click(function(e) {
                      var send_attachment_bkp = wp.media.editor.send.attachment;
                      var button = $(this);
                      var id = button.attr('id').replace('_button', '');
                      _custom_media = true;
                          wp.media.editor.send.attachment = function(props, attachment){
                          if ( _custom_media ) {
                              $("#"+id).val(attachment.url);
                          } else {
                              return _orig_send_attachment.apply( this, [props, attachment] );
                          };
                      }
                      wp.media.editor.open(button);
                      return false;
                  });
                  $('.add_media').on('click', function(){
                      _custom_media = false;
                  });
              }  
          });
      </script>
      <?php }
            /**
             * Generates the field's HTML for the meta box.
             */
            public function generate_fields( $post ) {
                global $wpdb;
                $setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
                $options = $wpdb->get_row("SELECT * FROM $setting_table_name WHERE id = 1");
                $company_name = $options->company_name;
                $name_site = $options->pdf_footer_url;
                $name_email = $options->pdf_footer_email;
                $output = '';
                foreach ( $this->fields as $field ) {
                    $label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
                    $db_value = get_post_meta( $post->ID, 'wpgv_customize_template_' . $field['id'], true );
                    switch ( $field['type'] ) {
                        
                        case 'bg_result':
                          $get_select_template = get_post_meta( $post->ID, 'wpgv_customize_template_select_template', true );
                          $get_str_template_style = get_post_meta( $post->ID, 'wpgv_customize_template_template-style', true );
                          $get_str_chosse_tem = get_post_meta( $post->ID, 'wpgv_customize_template_chosse_template', true );
                          $image_result = wp_get_attachment_image_src( $db_value, 'large' );
                          $url_bg = $image_result[0];

                          $input = '';
                          $input .= sprintf(
                            "<input class='%s' id='%s' name='%s' data-id-result='%s' value='%s'  type='hidden' ><a id='wpgv_trigger_template' class='wpgv_trigger_template' data-tem-style='%s' data-chosse-tem='%s'>%s</a>",
                            $field['id'],
                            $field['id'],
                            $field['id'],
                            $db_value,
                            $db_value,
                            $get_str_template_style,
                            $get_str_chosse_tem,
                            $db_value == null ? ((!empty($get_str_template_style)) ? "<img id='wpgv_src_result' src='https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/png/" . $get_str_template_style . "'  />" : "") : (($db_value != "0") ? "<img id='wpgv_src_result' src='".$url_bg."' />" : "")
                            
                          );
                          break;
                        case 'select':
                          $input = '';
                          $input .= sprintf(
                            '<select id="%s" name="%s" class="regular-text">',
                            $field['id'],
                            $field['id']
                          );
                          foreach ($field['options'] as $key => $value) {
                            $field_value = !is_numeric($key) ? $key : $value;
                            $input .= sprintf(
                              '<option %s value="%s">%s</option>',
                              $db_value == $field_value ? 'selected' : '',
                              $field_value,
                              $value
                            );
                          }
                          $input .= '</select>';
                          break;
                        
                        case 'date_expiry':
                          $input = '';
                          $input .= sprintf(
                            '<input %s id="%s" name="%s" type="%s" value="%s" min="1">',
                            $field['type'] !== 'color' ? 'class="regular-text"' : '',
                            $field['id'],
                            $field['id'],
                            $field['type'] == 'date_expiry' ? 'number' : '',
                            $db_value == Null ? '60' : $db_value
                          );
                          break;
    
                        case 'chosse_template':

                          $input = '';
                          $input .= '<div id="wpgv_wrap_custom_template">';
                          $input .= '<div style="display:none;" id="wpgv_validation_upload_bg"><p id="wpgv_alert_red" style="color:red">(*) Please save template</p></div>';
                            $i = 0;
                            foreach ($field['options'] as $key => $value) {
                              $field_value = !is_numeric($key) ? $key : $value;
                              $select_temp = get_post_meta( $post->ID, 'wpgv_customize_template_select_template', true );
                              $get_bg_temp = get_post_meta( $post->ID, 'wpgv_customize_template_template-style', true );
                              $get_chosse_template = get_post_meta( $post->ID, 'wpgv_customize_template_chosse_template', true );
                              $check_select;
                              $checked;
                              if($select_temp == "default"){
                                  $check_select = 0;
                                  $checked;
                              }else if($select_temp == "custom"){
                                  $check_select = 1;
                                  $checked = "checked";
                              }
                              $input .= sprintf(
                                '<label class="chosse_template_label" ><input data-id="%s" data-checked="%s" %s id="%s" class="%s wpgv_select_style_temp" name="%s" type="radio" value="%s" data-bg-temp="%s"><img src="%s"></label>',
                                $field['id'] . '-' . $i,
                                $db_value === $field_value ? $check_select : '0',
                                $db_value === $field_value ? $checked : '',
                                $field['id'],
                                $field['id'],
                                $field['id'],
                                $field_value,
                                $field_value == 'portail' ? 'template-custom-portail.png' : 'template-custom-lanscape.png',
                                '../wp-content/plugins/gift-voucher-pro/assets/img/' . $value . '.png'
                              );
                              $i++;
                            };
                              
                            $input .= '</div>';
                          break;  
                        
                        case 'radio':
                          $input = '';
                          $input .= '<fieldset id="wpgv_load_temp">';
                          $input .= '<legend class="screen-reader-text">' . $field['label'] . '</legend>';
                          $i = 0;
                          $get_temp_custom = get_post_meta( $post->ID, 'wpgv_customize_template_check_temp_custom', true );
                          foreach ($field['options'] as $key => $value) {
                            $field_value = !is_numeric($key) ? $key : $value;
                            $ex_field_value = !is_numeric($key) ? $key : $value;
                            $name_chosse = explode("-",$ex_field_value);
                            
                            $input .= sprintf(
                              '<label><input data-checked="%s" class="%s wpgv_select_style_temp" %s id="%s" name="%s" type="radio" value="%s" data-chosse="%s" ><img class="voucher-content-step" src="%s"></label>%s',
                              $db_value === $field_value ? ($get_temp_custom == 1 ? "0" : "1") : '0',
                              $field['id'],
                              $db_value === $field_value ? ($get_temp_custom == 1 ? "" : "checked") : '',
                              $field['id'],
                              $field['id'],
                              $field_value,
                              $name_chosse[2],
                              'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/png/' . $value,
                              $i < count($field['options']) - 1 ? '' : ''
                            );
                            $i++;
                          }
                          $input .= '</fieldset>';
                          break;
                        case 'select_template':
                          $input = '';
                          $get_template_style = get_post_meta( $post->ID, 'wpgv_customize_template_template-style', true );
                          $input .= '<div style="width:100%">';
                          $input .= '<input class="get_template_style" id="get_template_style" name="get_template_style" data-template-style = "'.$get_template_style.'" value = "'.$get_template_style.'" type="hidden">';
                          $input .= '<input class="get_company_name" id="get_company_name" name="get_company_name" value = "'.$company_name.'" type="hidden">';
                          $input .= '<input class="get_name_site" id="get_name_site" name="get_name_site" value = "'.$name_site.'" type="hidden">';
                          $input .= '<input class="get_name_email" id="get_name_email" name="get_name_email" value = "'.$name_email.'" type="hidden">';
                          $input .= "<input class='get_option_url' id='get_option_url' name='get_option_url' value='".get_option('wpgv_company_logo')."' type='hidden' >";
                          $input .= "<input class='wpgv_admin_url' id='wpgv_admin_url' name='wpgv_admin_url' value='" . admin_url('admin-ajax.php') . "' type='hidden' >";
                          $input .= "<input class='wpgv_get_svg' id='wpgv_get_svg' name='wpgv_get_svg' value='" . plugins_url()."/gift-voucher-pro/assets/img/template-images-v2/" . "' type='hidden' >";
                          $input .= '</div>';
                          $i = 0;
                          foreach ($field['options'] as $key => $value) {
                            $field_value = !is_numeric($key) ? $key : $value;
                            

                            $input .= sprintf(
                              '<div id="wpgv_show_radio"><span><input data-select="%s"  %s id="%s" class="%s" name="%s" type="radio" value="%s"></span><label>%s </label></div>',
                              $db_value === "" ?  ( ($field_value === "default") ? "1": "0"  ) : ( ($db_value === $field_value) ? "1": "0"  ),
                              $db_value === $field_value ? 'checked' : '',
                              $field['id'],
                              $field['id'],
                              $field['id'],
                              $field_value,
                              $field_value
                            );
                            $i++;
                          };
                          break;
                        case 'json_template':
                          $input = '';
                          $input .= sprintf(
                            "<input class='get_json_db' id='get_json_db' name='get_json_db' value='%s' type='text' ><input class='%s' id='%s' name='%s' data-json-template='%s' value='%s' type='text' >",
                            $db_value,
                            $field['id'],
                            $field['id'],
                            $field['id'],
                            $db_value,
                            $db_value
                          );
                          break;
    
                        case 'id_bg_template':
                          $image_bg_template = "";
                          $image_attributes = get_post_meta( $post->ID, 'wpgv_customize_template_id_bg_template', true );
                          if(is_numeric($image_attributes) ){
                            $image_bg_template_array = wp_get_attachment_image_src( $db_value, 'large' );
                            $image_bg_template =$image_bg_template_array[0];
                          }else{
                            $image_bg_template = get_post_meta( $post->ID, 'wpgv_customize_template_id_bg_template', true );
                          }
                          
                          $input = '';
                          $input .= sprintf(
                            "<input class='%s' id='%s' name='%s' data-id-bg='%s' value='%s'  type='text' ><img id='wpgv_src_json' src='%s' />",
                            $field['id'],
                            $field['id'],
                            $field['id'],
                            $image_bg_template,
                            $image_bg_template,
                            $image_bg_template
                          );
                          break;
                        
                        case 'check_temp_custom':
                          $image_attributes = wp_get_attachment_image_src( $db_value, 'large' );
                          $input = '';
                          $input .= sprintf(
                            "<input class='%s' id='%s' name='%s' value='%s' data-status-temp='%s'  type='text' >",
                            $field['id'],
                            $field['id'],
                            $field['id'],
                            $db_value != null ? $db_value : '0',
                            $db_value
                          );
                          break;
                        case 'get_chosse_temp':
                            $input = '';
                            $input .= sprintf(
                              "<input class='%s' id='%s' name='%s' value='%s' data-chosse-temp='%s' type='text' >",
                              $field['id'],
                              $field['id'],
                              $field['id'],
                              get_post_meta( $post->ID, 'wpgv_customize_template_chosse_template', true ),
                              get_post_meta( $post->ID, 'wpgv_customize_template_chosse_template', true )
                            );
                            break;
                        case 'modal_edit_temp':
                          $input = '';
                          $input .= sprintf(
                            '<div class="modal_edit_template" id="modal_edit_template" style="display:none">
                                      <div class="modal_template_content">
                                        <div class="dialog_template">
                                            <div class="title_modal wpgv_wrap_icon">
                                              <span class="close_popup dashicons dashicons-no-alt"></span>
                                            </div>
                                            <div class="title_modal">
                                                <div class="box-icon-edit"><span class="dashicons dashicons-edit"></span></div>
                                                <h1>Edit template</h1>
                                            </div>
                                            <div class="wrap_template">
                                              
                                              <div class="wpgv_row">
                                                <div class="content_left_template wpgv_col">
                                                  <div id="container-form-chosse-template">
                                                    <div class="group_form_edit_temp">      
                                                    <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Color name voucher</label>
                                                        <input name="color_text_name_voucher_preview" type="text" id="color_text_name_voucher" value="" class="regular-text" aria-required="true">
                                                      </div>
                                                    <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Font all text</label>
                                                        <input name="color_font_all_text" type="number" id="color_font_all_text" value="" class="color_font_all_text wpvc_input" min="8" max="96" aria-required="true" placeholder="Font size all text">
                                                      </div>
                                                      <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Color all text</label>
                                                        <input name="color_text_voucher" type="text" id="color_text_voucher" value="" class="regular-text" aria-required="true" >
                                                      </div>
                                                      <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Gift Value</label>
                                                        <input name="color_text_voucher_price" type="text" id="color_text_voucher_price" value="" class="regular-text" aria-required="true">
                                                        <input name="color_font_voucher_price" type="number" id="color_font_voucher_price" value="" class="color_font_voucher_price wpvc_input" min="8" max="96" aria-required="true" placeholder="Font size value">
                                                        <input class="wpvc_input" name="edit_price_temp" id="edit_price_temp" type="number" value="" placeholder="Price"/>
                                                      </div> 
                                                      <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Gift to</label>
                                                        <input class="wpvc_input" name="edit_giftto_temp" id="edit_giftto_temp" type="input" value="" placeholder="Gift to"/>
                                                      </div>
                                                      <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Gift from</label>
                                                        <input class="wpvc_input" name="edit_giftform_temp" id="edit_giftform_temp" type="input" value="" placeholder="Gift from"/>
                                                      </div>
                                                      <div class="group_input_edit_temp">
                                                        <label class="wpvc_label">Description</label>  
                                                        <textarea class="wpvc_textarea" maxlength="250" value="" id="edit_desc_temp" placeholder="Max: 250 Characters" name="edit_desc_temp" class="edit_desc_temp" spellcheck="false"></textarea>
                                                        <div class="maxchar"></div>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>
                                                <div class="content_right_tamplete wpgv_col">
                                                    <div class="content_top_template ">
                                                        <a class="button wpgv_button" id="savepdf">Save as PDF</a>
                                                        <a class="upload_bg_template button wpgv_button" id="upload_bg_template" >Upload Background</a>
                                                        <a class="button wpgv_button" id="wpgv_tripger_all" >Save Template</a>
                                                        <a class="button wpgv_button" id="wpgv_set_default_json" >Set default</a>
                                                        <a class="button wpgv_button" id="wpgv_appendImage" style="display:none">Image</a>
                                                        <a class="button wpgv_button" id="wpgv_draw_stage" style="display:none">Drawing</a>
                                                        <a class="button wpgv_button" id="wpgv_reset_default" data-temp="" style="display:none">Reset Default</a>
                                                    </div>
                                                    <div id="container-template-chosse-template"></div>
                                                </div>
                                              </div>
                                              <a id="show-preview-gift-card" href=""></a>
                                            </div>
                                        </div>
                                      </div>
                                    </div>'
                          );
                          break;
                      default:
                        $input .= sprintf(
                          '<input %s id="%s" name="%s" type="%s" value="%s">',
                          $field['type'] !== 'color' ? 'class="regular-text"' : '',
                          $field['id'],
                          $field['id'],
                          $field['type'],
                          $db_value
                        );
                    }
                    $output .= $this->row_format( $label, $input );
                }
                echo '<table class="form-table wpgv-template-box"><tbody>' . $output . '</tbody></table>';
            }

            /**
             * Generates the HTML for table rows.
             */
            public function row_format( $label, $input ) {
              return sprintf(
                '<tr class="wpgv_line_tr"><th scope="row">%s</th><td>%s</td></tr>',
                $label,
                $input
              );
            }

            /**
             * Hooks into WordPress' save_post function
             */
            public function save_post($post_id) {
              if (!isset($_POST['wpgv_customize_template_nonce']))
                return $post_id;

              $nonce = $_POST['wpgv_customize_template_nonce'];
              if (!wp_verify_nonce($nonce, 'wpgv_customize_template_data'))
                return $post_id;
              if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
              return $post_id;

              if (isset($_POST['json_template'])) {
                update_post_meta($post_id, 'wpgv_customize_template_json_template', stripslashes($_POST['json_template']));
              }
              if (isset($_POST['id_bg_template'])) {
                update_post_meta($post_id, 'wpgv_customize_template_id_bg_template', $_POST['id_bg_template']);
              }
              if (isset($_POST['bg_result'])) {
                update_post_meta($post_id, 'wpgv_customize_template_bg_result', $_POST['bg_result']);
              }
              if (isset($_POST['check_temp_custom'])) {
                update_post_meta($post_id, 'wpgv_customize_template_check_temp_custom', $_POST['check_temp_custom']);
              }

              if (isset($_POST['get_chosse_temp'])) {
                
                if($_POST['get_chosse_temp'] == 0){
                  update_post_meta($post_id, 'wpgv_customize_template_template-style', 0);
                }
                // exit();
                if (isset($_POST['check_temp_custom']) || $_POST['check_temp_custom'] == "0") {
                  update_post_meta($post_id, 'wpgv_customize_template_chosse_template', $_POST['get_chosse_temp']);
                  if($_POST['chosse_template'] == "portail"){
                    update_post_meta($post_id, 'wpgv_customize_template_template-style', $_POST['get_template_style']);
                  }else if($_POST['chosse_template'] == "lanscape"){
                    update_post_meta($post_id, 'wpgv_customize_template_template-style', $_POST['get_template_style']);
                  }
                }
                if (isset($_POST['check_temp_custom']) || $_POST['check_temp_custom'] == "1") {
                  update_post_meta($post_id, 'wpgv_customize_template_chosse_template', $_POST['get_chosse_temp']);
                  
                  if($_POST['chosse_template'] == "portail"){
                    update_post_meta($post_id, 'wpgv_customize_template_template-style', $_POST['get_template_style']);
                  }else if($_POST['chosse_template'] == "lanscape"){
                    update_post_meta($post_id, 'wpgv_customize_template_template-style', $_POST['get_template_style']);
                  }
                }
                
              }
              if (isset($_POST['template-style'])) {
                
                update_post_meta($post_id, 'wpgv_customize_template_chosse_template', $_POST['get_chosse_temp']);
                if ($_POST['check_temp_custom'] == "0") {
                  
                  update_post_meta($post_id, 'wpgv_customize_template_template-style', $_POST['get_template_style']);
                }
                if ($_POST['check_temp_custom'] == "1") {
                  
                  update_post_meta($post_id, 'wpgv_customize_template_template-style', $_POST['template-style']);
                }
              }

              
              if (isset($_POST['status'])) {
                update_post_meta($post_id, 'wpgv_customize_template_status', $_POST['status']);
              }
              if (isset($_POST['voucher_expiry_value'])) {
                update_post_meta($post_id, 'wpgv_customize_template_voucher_expiry_value', $_POST['voucher_expiry_value']);
              }

              if (isset($_POST['check_temp_custom'])) {
                if ($_POST['check_temp_custom'] == "1") {
                  update_post_meta($post_id, 'wpgv_customize_template_select_template', 'custom');
                }else{
                  update_post_meta($post_id, 'wpgv_customize_template_select_template', $_POST['select_template']);
                } 
              }
              
              if (isset($_POST[$field['id']])) {
                switch ($field['type']) {
                  case 'email':
                    $_POST[$field['id']] = sanitize_email($_POST[$field['id']]);
                    break;
                  case 'text':
                    $_POST[$field['id']] = sanitize_text_field($_POST[$field['id']]);
                  case 'number':
                    $_POST[$field['id']] = (int)$_POST[$field['id']];
                    break;
                }
                $this->curl_file_server($_POST[$field['id']]);
                update_post_meta($post_id, 'wpgv_customize_template_' . $field['id'], $_POST[$field['id']]);
              } else if ($field['type'] === 'checkbox') {
                update_post_meta($post_id, 'wpgv_customize_template_' . $field['id'], '0');
              }
              

            }
            public function curl_file_server($file_name){
              $ext = pathinfo($file_name, PATHINFO_EXTENSION);
              if ($ext == 'png') {
                $name_svg = str_replace(".png", ".svg", $file_name);
                $file_template = WPGIFT__PLUGIN_DIR . '/assets/img/template-images-v2/' . $name_svg;
                if (!file_exists($file_template)) {
                  $url = 'https://gift-card-pro.s3.eu-central-1.amazonaws.com/templates/version-1.2/svg/' . $name_svg;
                  $ch = curl_init($url);
                  $dir = WPGIFT__PLUGIN_DIR . '/assets/img/template-images-v2/';
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
}
new Template_Voucher;