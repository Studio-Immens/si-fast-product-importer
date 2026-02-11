<?php
/**
 * Flash_Products
 *
 * @package   Flash_Products
 * @author    Mauro Arnone <mauro.arnone.ma@gmail.com>
 * @copyright StudioImmens
 * @license   GPL v.3
 * @link      studioimmens.com
 */




 /**
 * Fired during plugin activation.
 *
 * This function create flash products meta table, called 'flash_products_meta' in the database.
 *
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_create_meta_table( $version = SIFProd_VERSION ){
	update_option( 'flash_products_meta_table', SIFProd_VERSION );
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . "flash_products_meta";  //get the database table prefix to create my new table

    $sql = "CREATE TABLE $table_name (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      meta_key varchar(255),
      meta_value text,
      assoc_id varchar(255),
      assoc_tb varchar(255),
      PRIMARY KEY  (id),
      KEY meta_key (meta_key),
      KEY assoc_id (assoc_id),
      KEY assoc_tb (assoc_tb)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    dbDelta( $sql );

  $meta_table = get_option( 'flash_products_meta_table' );
  if ( $meta_table != $version ) {
    update_option( 'flash_products_meta_table', $version );
  }
}
/**
 * This function retrieve flash products meta_value or entire row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_get_meta( $meta_key, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $meta_key = sanitize_key( $meta_key );

  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $table WHERE meta_key = %s", $meta_key ) );
  } elseif ( $type == 'all' ) {
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE meta_key = %s ORDER BY id", $meta_key ) );
  } else {
    $result = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM $table WHERE meta_key = %s", $meta_key ), $type );
  }
  return $result;
}
/**
 * This function retrieve flash products meta_value or entire results from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_get_meta_by_assoc_id( $assoc_id, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $assoc_id = sanitize_text_field( $assoc_id );

  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $table WHERE assoc_id = %s", $assoc_id ) );
  } else {
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE assoc_id = %s", $assoc_id ), $type );
  }
  return $result;
}
/**
 * This function retrieve flash products meta_value or entire results from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_get_meta_by_assoc_tb( $assoc_tb, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $assoc_tb = sanitize_text_field( $assoc_tb );

  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $table WHERE assoc_tb = %s", $assoc_tb ) );
  } else {
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE assoc_tb = %s", $assoc_tb ), $type );
  }
  return $result;
}
/**
 * This function retrieve flash products meta_value or entire row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_get_meta_by_id( $id, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $id = absint( $id );

  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $table WHERE id = %d", $id ) );
  } else {
    $result = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM $table WHERE id = %d", $id ), $type );
  }
  return $result;
}
/**
 * This function insert meta row in the table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_insert_meta( $meta_key, $meta_value, $assoc_id = null, $assoc_tb = null ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $result = $wpdb->insert( $table, array( 
      'meta_key'   => sanitize_key( $meta_key ), 
      'meta_value' => sanitize_textarea_field( $meta_value ), 
      'assoc_id'   => sanitize_text_field( $assoc_id ),
      'assoc_tb'   => sanitize_text_field( $assoc_tb ) 
  ) );
  return $result;
}
/**
 * This function update meta row in the table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_update_meta( $meta_key, $meta_value, $assoc_id = null, $assoc_tb = null ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $meta_key = sanitize_key( $meta_key );
  $meta = FP_get_meta( $meta_key );

  $data = array( 
      'meta_value' => sanitize_textarea_field( $meta_value ), 
      'assoc_id'   => sanitize_text_field( $assoc_id ),
      'assoc_tb'   => sanitize_text_field( $assoc_tb ) 
  );

  if ( $meta !== null ) {
    $result = $wpdb->update( $table, $data, array( 'meta_key' => $meta_key ) );
  } else {
    $data['meta_key'] = $meta_key;
    $result = $wpdb->insert( $table, $data );
  }
  return $result;
}
/**
 * This function delete meta row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_delete_meta( $meta_key ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $result = $wpdb->delete( $table, array( 'meta_key' => sanitize_key( $meta_key ) ) );
  return $result;
}
/**
 * This function delete meta row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_delete_meta_by_id( $id ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $result = $wpdb->delete( $table, array( 'id' => absint( $id ) ) );
  return $result;
}
/**
 * This is a debug function
 * @since      1.0.0
 * @package    Flash_Products
 * @author     StudioImmens <info@studioimmens.com>
 */
function FP_debug( $var ){ ?>
	<pre> <?php var_dump($var); ?> </pre> <?php
}

function FP_general_setting( $setting = array() ){

  $name = ( isset($setting['name']) ) ? sanitize_key($setting['name']) : '';
  $title = ( isset($setting['title']) ) ? sanitize_text_field($setting['title']) : '';
  $default = ( isset($setting['default']) ) ? $setting['default'] : null;
  $data_default = ( FP_get_meta($name) ) ? FP_get_meta($name) : $default;
  $options = ( isset($setting['options']) ) ? $setting['options'] : array();
  $type = ( isset($setting['type']) ) ? sanitize_key($setting['type']) : 'text';
  $class = ( isset($setting['class']) ) ? sanitize_html_class($setting['class']) : '';
  $text = ( isset($setting['text']) ) ? sanitize_text_field($setting['text']) : '';
  $info = ( isset($setting['info']) ) ? sanitize_text_field($setting['info']) : '';
  $other = ( isset($setting['other']) ) ? $setting['other'] : '';
  ?>

  <div class="FOsettingEl <?php echo esc_attr($class);?>" title="<?php echo esc_attr($info).' ______ '.esc_html__('nome dell\'impostazione nel database: ( ', 'si-flash-products').esc_attr($name).' )';?>">
      <?php if($title != ''){ ?>
          <strong class="FOtextSettings" style="flex-basis:100%"><?php echo esc_html($title);?></strong>
      <?php }?>
      <p class="FOtextSettings"><?php echo esc_html($text);?></p>
      <?php if($type == 'textarea'){ ?>
         <textarea name="setting[<?php echo esc_attr($name);?>]" <?php echo $other; //phpcs:ignore ?>><?php echo esc_textarea($data_default);?></textarea>
      <?php } elseif ($type != 'select') { ?>
          <input type="<?php echo esc_attr($type); ?>" name="setting[<?php echo esc_attr($name); ?>]" value="<?php echo esc_attr($data_default);?>" <?php echo $other; //phpcs:ignore ?>>
      <?php } else{ ?>
          <select name="setting[<?php echo esc_attr($name); ?>]" <?php echo $other; //phpcs:ignore ?>>
              <option selected disabled hidden><?php echo esc_html($data_default); ?></option>
              <?php if ( count($options) ) { ?>
                  <?php foreach ($options as $option) { ?>
                      <option value="<?php echo esc_attr($option);?>" <?php selected($data_default, $option); ?>><?php echo esc_html($option);?></option>
                  <?php } ?>
              <?php } ?>
          </select>
      <?php } ?>
      <?php if ( $default !== null ) { ?>
          <span class="dashicons dashicons-image-rotate pointer" data-default="<?php echo esc_attr($default); ?>"></span>
      <?php } ?>
  </div>

  <?php
}

function FP_save_settings( $args, $assoc_id = '', $debug = false ){
  if ( isset($_POST["update"]) && current_user_can( 'manage_options' ) ) {
      if ( !wp_verify_nonce( $_POST['sett_nonce'], 'si-flash-prod-sett' ) ) {
          return;
      }
      if ( isset( $_POST[$args] ) ) { 
          foreach ($_POST[$args] as $key => $value) {
              if ( isset( $_POST[$args][$key] ) ) {
              // FP_debug($key);
                  FP_update_meta( $key, $value, $assoc_id ); 
              }
          }
      } //$_SERVER['SERVER_NAME']
      if ($debug) {
          FP_debug($_POST);
      }
      $url = 'Location: '.$_SERVER['REQUEST_URI'];
      header( $url );
  }
}

/**
 * AJAX Handler for product search (Proxy to external API)
 */
function FP_ajax_search_products() {
    check_ajax_referer( 'fp_import_nonce', 'nonce' );

    $categories = isset( $_GET['categories'] ) ? sanitize_text_field( $_GET['categories'] ) : '';
    $languages = isset( $_GET['languages'] ) ? sanitize_text_field( $_GET['languages'] ) : '';
    $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
    $limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 10;
    $offset = isset( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0;
    $s = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

    $cache_key = 'fp_search_' . md5( $categories . $languages . $orderby . $limit . $offset . $s );
    $results = get_transient( $cache_key );

    if ( false === $results ) {
        $url = add_query_arg( array(
            'categories' => $categories,
            'languages'  => $languages,
            'orderby'    => $orderby,
            'limit'      => $limit,
            'offset'     => $offset,
            's'          => $s,
        ), 'https://flashproducts.studioimmens.com/wp-json/flash_products/v1/products' );

        $response = wp_remote_get( $url );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $results = json_decode( wp_remote_retrieve_body( $response ), true );
        set_transient( $cache_key, $results, HOUR_IN_SECONDS ); // Cache search results for 1 hour
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_fp_search_products', 'FP_ajax_search_products' );

/**
 * AJAX Handler for product import
 */
function FP_ajax_import_product() {
    check_ajax_referer( 'fp_import_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'si-flash-products' ) ) );
    }

    $product_data = isset( $_POST['product'] ) ? $_POST['product'] : array();

    if ( empty( $product_data ) ) {
        wp_send_json_error( array( 'message' => __( 'No product data received', 'si-flash-products' ) ) );
    }

    // Sanitize product data
    $sanitized_data = array(
        'post_title'    => sanitize_text_field( $product_data['post_title'] ),
        'post_content'  => wp_kses_post( $product_data['post_content'] ),
        'post_excerpt'  => wp_kses_post( $product_data['post_excerpt'] ),
        'fp_categories' => sanitize_text_field( $product_data['fp_categories'] ),
        'fp_tag'        => sanitize_text_field( $product_data['fp_tag'] ),
        'fp_img'        => esc_url_raw( $product_data['fp_img'] ),
    );

    $result = FP_create_woo_product( $sanitized_data );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    }

    wp_send_json_success( array( 'message' => __( 'Product imported successfully!', 'si-flash-products' ), 'product_id' => $result ) );
}
add_action( 'wp_ajax_fp_import_product', 'FP_ajax_import_product' );

/**
 * AJAX Handler for AI Generation via Gemini
 */
function FP_ajax_ai_generate_product() {
    check_ajax_referer( 'fp_import_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'si-flash-products' ) ) );
    }

    $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $context = isset( $_POST['context'] ) ? sanitize_textarea_field( $_POST['context'] ) : '';

    if ( empty( $name ) ) {
        wp_send_json_error( array( 'message' => __( 'Il nome del prodotto è obbligatorio', 'si-flash-products' ) ) );
    }

    $api_key = FP_get_meta('FP_gemini_api_key');
    $model = FP_get_meta('FP_ai_model') ?: 'gemini-2.0-flash';

    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'API Key mancante nelle impostazioni', 'si-flash-products' ) ) );
    }

    $prompt = "Genera i dettagli di un prodotto WooCommerce basandoti su queste informazioni:
    Nome: $name
    Contesto aggiuntivo: $context

    Restituisci ESCLUSIVAMENTE un oggetto JSON con questi campi:
    - post_title: un titolo accattivante
    - post_excerpt: una descrizione breve (max 150 caratteri)
    - post_content: una descrizione completa e formattata in HTML (usa tag <p>, <ul>, <li>, <strong>)
    - fp_categories: 2-3 categorie separate da virgola
    - fp_tag: 3-5 tag separati da virgola

    Il JSON deve essere valido e non contenere altri testi.";

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

    $body = array(
        'contents' => array(
            array(
                'parts' => array(
                    array( 'text' => $prompt )
                )
            )
        ),
        'generationConfig' => array(
            'response_mime_type' => 'application/json',
        )
    );

    $response = wp_remote_post( $url, array(
        'body'    => json_encode( $body ),
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 30,
    ) );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    $res_body = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( isset( $res_body['candidates'][0]['content']['parts'][0]['text'] ) ) {
        $ai_text = $res_body['candidates'][0]['content']['parts'][0]['text'];
        $product_data = json_decode( $ai_text, true );
        
        if ( $product_data ) {
            wp_send_json_success( $product_data );
        }
    }

    wp_send_json_error( array( 'message' => __( 'Errore nella generazione del contenuto AI. Verifica la tua API Key e riprova.', 'si-flash-products' ) ) );
}
add_action( 'wp_ajax_fp_ai_generate_product', 'FP_ajax_ai_generate_product' );

/**
 * Create WooCommerce product from remote data
 */
function FP_create_woo_product( $data ) {
    if ( ! class_exists( 'WC_Product_Simple' ) ) {
        return new WP_Error( 'wc_missing', __( 'WooCommerce not active', 'si-flash-products' ) );
    }

    // Check if product already exists by remote ID or Title
    $existing_id = post_exists( $data['post_title'] );
    if ( $existing_id && get_post_type( $existing_id ) === 'product' ) {
        return new WP_Error( 'already_exists', __( 'Product already exists', 'si-flash-products' ) );
    }

    $product = new WC_Product_Simple();
    $product->set_name( $data['post_title'] );
    $product->set_description( $data['post_content'] );
    $product->set_short_description( $data['post_excerpt'] );
    
    // Get default status from settings
    $default_status = FP_get_meta('FP_default_product_status');
    $product->set_status( $default_status ? $default_status : 'publish' );
    
    // Set categories
    if ( ! empty( $data['fp_categories'] ) ) {
        $cat_ids = array();
        $cats = explode( ',', $data['fp_categories'] );
        foreach ( $cats as $cat_name ) {
            if ( empty( $cat_name ) ) continue;
            $term = get_term_by( 'name', trim( $cat_name ), 'product_cat' );
            if ( ! $term ) {
                $term = wp_insert_term( trim( $cat_name ), 'product_cat' );
            }
            if ( ! is_wp_error( $term ) ) {
                $cat_ids[] = is_array( $term ) ? $term['term_id'] : $term->term_id;
            }
        }
        $product->set_category_ids( $cat_ids );
    }

    // Set Tags
    if ( ! empty( $data['fp_tag'] ) ) {
        $tag_names = explode( ',', $data['fp_tag'] );
        $product->set_tag_ids( array_filter( array_map( 'trim', $tag_names ) ) );
    }

    // Handle Image
    if ( ! empty( $data['fp_img'] ) ) {
        $image_id = FP_sideload_image( $data['fp_img'], $data['post_title'] );
        if ( ! is_wp_error( $image_id ) ) {
            $product->set_image_id( $image_id );
        }
    }

    $product_id = $product->save();

    return $product_id;
}

/**
 * Sideload image from URL to Media Library
 */
function FP_sideload_image( $url, $title ) {
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $url = esc_url_raw( $url );
    $filename = basename( $url );

    // Check if image already exists in media library by filename
    global $wpdb;
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ) );

    if ( $attachment_id ) {
        return $attachment_id;
    }

    $desc = $title;
    $file_array = array();

    // Download file to temp location
    $tmp = download_url( $url );

    if ( is_wp_error( $tmp ) ) {
        error_log( 'Flash Products Error: Failed to download image from ' . $url . ' - ' . $tmp->get_error_message() );
        return $tmp;
    }

    $file_array['name'] = $filename;
    $file_array['tmp_name'] = $tmp;

    // Do the job
    $id = media_handle_sideload( $file_array, 0, $desc );

    // If error, unlink
    if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
        error_log( 'Flash Products Error: Failed to sideload image ' . $filename . ' - ' . $id->get_error_message() );
    }

    return $id;
}




