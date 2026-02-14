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

// Generator for local DB - Generates 1000 common products
function FP_ensure_local_db() {
    $json_path = SIFProd_PLUGIN_PATH . 'includes/local_products.json';
    
    // Generate if file doesn't exist, if count is wrong, if images are old placeholders, or if explicit regeneration is requested
    $existing_products = file_exists( $json_path ) ? json_decode( file_get_contents( $json_path ), true ) : [];
    $needs_regen = ! file_exists( $json_path ) || count($existing_products) < 2000 || (isset($existing_products[0]['fp_img']) && strpos($existing_products[0]['fp_img'], 'picsum.photos') !== false);
    
    if ( $needs_regen || isset( $_GET['generate_local_db'] ) ) {
        if ( isset( $_GET['generate_local_db'] ) && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $categories_data = [
            'Elettronica' => ['Smartphone', 'Laptop', 'Cuffie Bluetooth', 'Smartwatch', 'Tablet', 'Fotocamera', 'Monitor 4K', 'Tastiera Meccanica', 'Power Bank', 'Speaker Wireless'],
            'Casa' => ['Lampada LED', 'Sedia Ergonomica', 'Tavolo in Legno', 'Quadro Moderno', 'Vaso in Ceramica', 'Tappeto Soft', 'Specchio', 'Divano 3 Posti', 'Set Posate', 'Macchina Caffè'],
            'Abbigliamento' => ['T-shirt Cotone', 'Jeans Slim Fit', 'Felpa con Cappuccio', 'Giacca Invernale', 'Scarpe Sportive', 'Cintura in Pelle', 'Cappello', 'Pantaloni Chino', 'Camicia Oxford'],
            'Bellezza' => ['Crema Idratante', 'Profumo Luxury', 'Shampoo Bio', 'Siero Viso', 'Maschera Argilla', 'Rossetto Matte', 'Smalto', 'Crema Solare', 'Balsamo'],
            'Sport' => ['Tappetino Yoga', 'Manubri 5kg', 'Palla da Basket', 'Corda per Saltare', 'Borraccia Termica', 'Zaino Trekking', 'Pesi Caviglie', 'Rullo Massaggi', 'Banda Elastica']
        ];

        $adjectives = ['Rivoluzionario', 'Professionale', 'Eco-sostenibile', 'Intelligente', 'Classico', 'Moderno', 'Ultra-resistente', 'Essenziale', 'Edizione Limitata', 'Compatto', 'Superiore', 'Elite', 'Definitivo'];
        
        $ingredients_list = ['Acqua Termale', 'Estratto di Aloe', 'Olio di Argan', 'Acido Ialuronico', 'Vitamina C', 'Burro di Shea', 'Proteine della Seta'];
        $allergens_list = ['Glutine', 'Lattosio', 'Frutta a guscio', 'Soia', 'Nichel Free'];
        
        // Unsplash mapping for relevant commercial-use images
        $img_keywords = [
            'Elettronica'   => 'tech,electronics,gadget',
            'Casa'          => 'interior,home,furniture',
            'Abbigliamento' => 'fashion,clothing,apparel',
            'Bellezza'      => 'beauty,cosmetics,skincare',
            'Sport'         => 'fitness,sport,gym'
        ];

        $products = [];
        for ($i = 1; $i <= 2000; $i++) {
            $cat_keys = array_keys($categories_data);
            $category = $cat_keys[array_rand($cat_keys)];
            $base_name = $categories_data[$category][array_rand($categories_data[$category])];
            $adj = $adjectives[array_rand($adjectives)];
            
            $title = "$base_name $adj " . ($i);
            $price = rand(15, 800) + (rand(0, 99) / 100);
            $sale_price = (rand(1, 10) > 7) ? ($price * 0.85) : ''; 
            
            // Unsplash Featured URL (More reliable)
            $kw = $img_keywords[$category] ?? 'product';
            $main_img = "https://source.unsplash.com/featured/800x800/?" . str_replace(' ', '', $base_name) . ",$kw&sig=$i";
            $gallery1 = "https://source.unsplash.com/featured/800x800/?" . str_replace(' ', '', $base_name) . ",detail&sig=" . ($i + 2000);
            $gallery2 = "https://source.unsplash.com/featured/800x800/?" . $kw . ",quality&sig=" . ($i + 4000);

            // Direct Response Style Description
            $desc = "<h3>Vuoi trasformare il tuo modo di vivere la categoria $category?</h3>";
            $desc .= "<p>Smetti di accontentarti di soluzioni mediocri. Il nuovo <strong>$title</strong> non è solo un prodotto, è la risposta definitiva che stavi cercando.</p>";
            $desc .= "<h4>Perché scegliere $title?</h4>";
            $desc .= "<ul>";
            $desc .= "<li><strong>Prestazioni Ineguagliabili:</strong> Progettato con tecnologia $adj per superare ogni aspettativa.</li>";
            $desc .= "<li><strong>Qualità Certificata:</strong> Ogni componente è stato testato per garantire una durata nel tempo senza precedenti.</li>";
            $desc .= "<li><strong>Design Esclusivo:</strong> Un'estetica che si adatta perfettamente al tuo stile di vita moderno.</li>";
            $desc .= "</ul>";
            $desc .= "<p><strong>Non perdere altro tempo.</strong> Le scorte sono limitate e la richiesta è altissima. Assicurati il tuo $title oggi stesso e sperimenta la differenza!</p>";

            // Conditional fields
            $ing = ($category === 'Bellezza' || rand(1, 10) > 8) ? $ingredients_list[array_rand($ingredients_list)] . ", " . $ingredients_list[array_rand($ingredients_list)] : '';
            $all = ($category === 'Bellezza' && rand(1, 10) > 7) ? $allergens_list[array_rand($allergens_list)] : '';
            $stick = (rand(1, 10) > 8) ? "NOVITÀ" : ((rand(1, 10) > 8) ? "BEST SELLER" : '');

            $products[] = [
                'post_title'      => $title,
                'post_content'    => $desc,
                'post_excerpt'    => "Il segreto per ottenere il massimo da $category. Scopri perché $title è la scelta numero uno degli esperti.",
                'fp_categories'   => $category,
                'fp_tag'          => strtolower($category) . ", $adj, offerta, esclusivo",
                'fp_img'          => $main_img,
                'fp_gallery'      => "$gallery1,$gallery2",
                'regular_price'   => number_format($price, 2, '.', ''),
                'sale_price'      => $sale_price ? number_format($sale_price, 2, '.', '') : '',
                'sku'             => "FP-" . strtoupper(substr($category, 0, 3)) . "-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'stock_status'    => 'instock',
                'stock_qty'       => rand(5, 150),
                'weight'          => (rand(5, 100) / 10),
                'length'          => rand(5, 80),
                'width'           => rand(5, 80),
                'height'          => rand(2, 40),
                'is_virtual'      => 'no',
                'is_downloadable' => 'no',
                'fp_ingredient'   => $ing,
                'fp_allerg'       => $all,
                'fp_sticker'      => $stick,
                'fp_temp'         => (rand(1, 10) > 9) ? "Conservare in luogo fresco" : '',
                'attributes'      => [
                    ['name' => 'Colore', 'value' => 'Nero | Bianco | Grigio | Blu', 'visible' => 1, 'variation' => 0],
                    ['name' => 'Materiale', 'value' => 'Premium | Eco-friendly', 'visible' => 1, 'variation' => 0]
                ],
                'custom_taxonomy' => [
                    'brand'      => 'FlashBrand',
                    'materiale'  => 'Materiale ' . $adj
                ]
            ];
        }

        file_put_contents($json_path, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        if ( isset( $_GET['generate_local_db'] ) ) {
            wp_die('2000 prodotti generati con successo! <a href="' . admin_url() . '">Torna indietro</a>');
        }
    }
}
add_action('admin_init', 'FP_ensure_local_db');





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
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

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

  <div class="FOsettingEl <?php echo esc_attr($class);?>" title="<?php echo esc_attr($info).' ______ '.esc_html__('Database setting name:', 'si-flash-products').' ( '.esc_attr($name).' )';?>">
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
              <?php if ( ! empty($data_default) ) : ?>
                  <option selected disabled hidden><?php echo esc_html($data_default); ?></option>
              <?php endif; ?>
              <?php if ( is_array($options) && count($options) ) { ?>
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
                  FP_update_meta( $key, $value, $assoc_id ); 
              }
          }
      }
      if ($debug) {
          FP_debug($_POST);
      }
      wp_safe_redirect( $_SERVER['REQUEST_URI'] );
      exit;
  }
}

/**
 * AJAX Handler for product search (Proxy to external API)
 */
/**
 * Log an event to the plugin's internal log system
 * 
 * @param string $message The message to log
 * @param string $context The context (e.g., 'Gemini API', 'Product Import')
 */
function FP_log_event( $message, $context = 'General' ) {
    $logs = get_option( 'fp_error_logs', array() );
    
    // Add new log entry at the beginning
    array_unshift( $logs, array(
        'timestamp' => current_time( 'mysql' ),
        'context'   => $context,
        'message'   => $message
    ) );
    
    // Keep only the last 50 logs
    $logs = array_slice( $logs, 0, 50 );
    
    update_option( 'fp_error_logs', $logs );
}

function fp_ajax_clear_logs() {
    check_ajax_referer( 'fp_import_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'si-flash-products' ) ) );
    }
    
    delete_option( 'fp_error_logs' );
    wp_send_json_success( array( 'message' => __( 'Logs cleared successfully!', 'si-flash-products' ) ) );
}
add_action( 'wp_ajax_fp_clear_logs', 'fp_ajax_clear_logs' );

function FP_ajax_search_products() {
    check_ajax_referer( 'fp_import_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'si-flash-products' ) ) );
    }

    $categories = isset( $_GET['categories'] ) ? sanitize_text_field( $_GET['categories'] ) : '';
    $languages = isset( $_GET['languages'] ) ? sanitize_text_field( $_GET['languages'] ) : '';
    $orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : '';
    $limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 10;
    $offset = isset( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0;
    $s = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
    $source = isset( $_GET['source'] ) ? sanitize_key( $_GET['source'] ) : 'all';

    $cache_key = 'fp_search_' . md5( $categories . $languages . $source . $orderby . $limit . $offset . $s );
    $results = get_transient( $cache_key );

    if ( false === $results ) {
        $all_results = array('result' => array(), 'total_results' => 0);
        
        // 1. Get Remote Results
        if ( $source === 'all' || $source === 'remote' ) {
            $remote_links = FP_get_meta('FP_remote_db_links');
            $urls = array('https://flashproducts.studioimmens.com/wp-json/flash_products/v1/products');
            
            if ( ! empty( $remote_links ) ) {
                $extra_links = explode( "\n", str_replace( "\r", "", $remote_links ) );
                foreach ( $extra_links as $link ) {
                    $link = trim($link);
                    if ( ! empty( $link ) && filter_var($link, FILTER_VALIDATE_URL) ) {
                        $urls[] = $link;
                    }
                }
            }

            foreach ( $urls as $base_url ) {
                $url = add_query_arg( array(
                    'categories' => $categories,
                    'languages'  => $languages,
                    'orderby'    => $orderby,
                    'limit'      => $limit,
                    'offset'     => $offset,
                    's'          => $s,
                ), $base_url );

                $response = wp_remote_get( $url, array('timeout' => 10) );

                if ( ! is_wp_error( $response ) ) {
                    $data = json_decode( wp_remote_retrieve_body( $response ), true );
                    if ( isset($data['result']) && is_array($data['result']) ) {
                        $all_results['result'] = array_merge($all_results['result'], $data['result']);
                        $all_results['total_results'] += intval($data['total_results'] ?? 0);
                    }
                }
            }
        }

        // 2. Get Local JSON Results
        if ( $source === 'all' || $source === 'local' ) {
            $local_db_path = SIFProd_PLUGIN_PATH . 'includes/local_products.json';
            if ( file_exists( $local_db_path ) ) {
                $local_data = json_decode( file_get_contents( $local_db_path ), true );
                if ( is_array($local_data) ) {
                    $filtered_local = array();
                    foreach ( $local_data as $product ) {
                        $match = true;
                        if ( ! empty($s) && stripos($product['post_title'], $s) === false ) {
                            $match = false;
                        }
                        if ( ! empty($categories) && stripos($product['fp_categories'], $categories) === false ) {
                            $match = false;
                        }
                        
                        if ( $match ) {
                            $product['source'] = 'local';
                            $filtered_local[] = $product;
                        }
                    }

                    $all_results['total_results'] += count($filtered_local);
                    
                    // Apply offset and limit to local results
                    $paged_local = array_slice($filtered_local, $offset, $limit);
                    $all_results['result'] = array_merge($all_results['result'], $paged_local);
                }
            }
        }

        $results = $all_results;
        set_transient( $cache_key, $results, HOUR_IN_SECONDS );
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
        'fp_gallery'    => implode(',', array_filter(array_map('esc_url_raw', explode(',', $product_data['fp_gallery'] ?? '')))),
        'regular_price' => wc_format_decimal( $product_data['regular_price'] ),
        'sale_price'    => wc_format_decimal( $product_data['sale_price'] ),
        'sku'           => sanitize_text_field( $product_data['sku'] ),
        'stock_status'  => sanitize_text_field( $product_data['stock_status'] ),
        'stock_qty'     => intval( $product_data['stock_qty'] ),
        'weight'        => sanitize_text_field( $product_data['weight'] ),
        'length'        => sanitize_text_field( $product_data['length'] ),
        'width'         => sanitize_text_field( $product_data['width'] ),
        'height'        => sanitize_text_field( $product_data['height'] ),
        'is_virtual'    => isset($product_data['is_virtual']) && $product_data['is_virtual'] === 'yes',
        'is_downloadable' => isset($product_data['is_downloadable']) && $product_data['is_downloadable'] === 'yes',
        'attributes'    => isset($product_data['attributes']) ? $product_data['attributes'] : array(),
        'custom_taxonomy' => isset($product_data['custom_taxonomy']) ? $product_data['custom_taxonomy'] : array(),
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
        wp_send_json_error( array( 'message' => __( 'Product name is required', 'si-flash-products' ) ) );
    }

    $api_key = FP_get_meta('FP_gemini_api_key');
    $model = FP_get_meta('FP_ai_model') ?: 'gemini-2.0-flash';
    $tone = FP_get_meta('FP_ai_tone') ?: 'Professionale e persuasivo';
    $sku_prefix = FP_get_meta('FP_sku_prefix') ?: 'PROD-';
    $default_stock = FP_get_meta('FP_default_stock') ?: '10';
    $temperature = floatval(FP_get_meta('FP_ai_creativity') ?: '0.7');

    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => __( 'API Key missing in settings', 'si-flash-products' ) ) );
    }

    $prompt = "Genera i dettagli di un prodotto WooCommerce basandoti su queste informazioni:
    Nome: $name
    Contesto aggiuntivo: $context
    Tono richiesto: $tone

    Restituisci ESCLUSIVAMENTE un oggetto JSON con questi campi:
    - post_title: un titolo accattivante
    - post_excerpt: una descrizione breve (max 150 caratteri)
    - post_content: una descrizione completa e formattata in HTML (usa tag <p>, <ul>, <li>, <strong>)
    - fp_categories: 2-3 categorie separate da virgola
    - fp_tag: 3-5 tag separati da virgola
    - regular_price: un prezzo realistico (solo numero)
    - sale_price: un prezzo scontato realistico o vuoto (solo numero)
    - sku: un codice SKU univoco che inizia con $sku_prefix
    - stock_status: 'instock'
    - stock_qty: un numero (default consigliato: $default_stock)
    - weight: peso realistico (solo numero)
    - length: lunghezza realistica (solo numero)
    - width: larghezza realistica (solo numero)
    - height: altezza realistica (solo numero)
    - fp_gallery: 2-3 URL di immagini realistiche correlate al prodotto, separate da virgola (usa URL placeholder di alta qualità se non ne hai di specifici)
    - attributes: un array di oggetti con 'name' (es. 'Color') e 'values' (es. 'Red | Blue | Green')

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
            'temperature' => $temperature,
        )
    );

    $response = wp_remote_post( $url, array(
        'body'    => json_encode( $body ),
        'headers' => array( 'Content-Type' => 'application/json' ),
        'timeout' => 30,
    ) );

    if ( is_wp_error( $response ) ) {
        $error_msg = $response->get_error_message();
        FP_log_event( $error_msg, 'Gemini API' );
        wp_send_json_error( array( 'message' => $error_msg ) );
    }

    $res_body = json_decode( wp_remote_retrieve_body( $response ), true );
    
    if ( isset( $res_body['error'] ) ) {
        $error_msg = isset( $res_body['error']['message'] ) ? $res_body['error']['message'] : __( 'Unknown API Error', 'si-flash-products' );
        FP_log_event( $error_msg, 'Gemini API' );
        wp_send_json_error( array( 'message' => $error_msg ) );
    }

    if ( isset( $res_body['candidates'][0]['content']['parts'][0]['text'] ) ) {
        $ai_text = $res_body['candidates'][0]['content']['parts'][0]['text'];
        $product_data = json_decode( $ai_text, true );
        
        if ( $product_data ) {
            // Clean attributes if they exist
            if ( isset( $product_data['attributes'] ) && is_array( $product_data['attributes'] ) ) {
                $sanitized_attrs = array();
                foreach ( $product_data['attributes'] as $attr ) {
                    if ( isset( $attr['name'] ) && isset( $attr['values'] ) ) {
                        $sanitized_attrs[] = array(
                            'name'   => sanitize_text_field( $attr['name'] ),
                            'values' => sanitize_text_field( $attr['values'] )
                        );
                    }
                }
                $product_data['attributes'] = $sanitized_attrs;
            }
            wp_send_json_success( $product_data );
        }
    }

    wp_send_json_error( array( 'message' => __( 'Error generating AI content. Check your API Key and try again.', 'si-flash-products' ) ) );
}
add_action( 'wp_ajax_fp_ai_generate_product', 'FP_ajax_ai_generate_product' );

/**
 * AJAX Handler for Taxonomy Search (Autocomplete)
 */
function FP_ajax_search_terms() {
    check_ajax_referer( 'fp_import_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'si-flash-products' ) ) );
    }

    $taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : 'product_cat';
    $search = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';

    $args = array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'name__like' => $search,
        'number'     => 10
    );

    $terms = get_terms( $args );

    if ( is_wp_error( $terms ) ) {
        wp_send_json_error( array( 'message' => $terms->get_error_message() ) );
    }

    $results = array();
    foreach ( $terms as $term ) {
        $results[] = array(
            'id'   => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug
        );
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_fp_search_terms', 'FP_ajax_search_terms' );

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
    
    // Product Type
    $product->set_virtual( $data['is_virtual'] );
    $product->set_downloadable( $data['is_downloadable'] );
    
    // WooCommerce Base Fields
    if ( ! empty( $data['regular_price'] ) ) $product->set_regular_price( $data['regular_price'] );
    if ( ! empty( $data['sale_price'] ) ) $product->set_sale_price( $data['sale_price'] );
    if ( ! empty( $data['sku'] ) ) $product->set_sku( $data['sku'] );
    
    if ( ! empty( $data['stock_status'] ) ) {
        $product->set_stock_status( $data['stock_status'] );
    }
    
    if ( isset( $data['stock_qty'] ) ) {
        $product->set_manage_stock( true );
        $product->set_stock_quantity( $data['stock_qty'] );
    }

    if ( ! empty( $data['weight'] ) ) $product->set_weight( $data['weight'] );
    if ( ! empty( $data['length'] ) ) $product->set_length( $data['length'] );
    if ( ! empty( $data['width'] ) ) $product->set_width( $data['width'] );
    if ( ! empty( $data['height'] ) ) $product->set_height( $data['height'] );
    
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

    // Handle Gallery
    if ( ! empty( $data['fp_gallery'] ) ) {
        $gallery_urls = explode( ',', $data['fp_gallery'] );
        $gallery_ids = array();
        foreach ( $gallery_urls as $g_url ) {
            if ( empty( $g_url ) ) continue;
            $g_id = FP_sideload_image( trim( $g_url ), $data['post_title'] . ' Gallery' );
            if ( ! is_wp_error( $g_id ) ) {
                $gallery_ids[] = $g_id;
            }
        }
        if ( ! empty( $gallery_ids ) ) {
            $product->set_gallery_image_ids( $gallery_ids );
        }
    }

    // Handle Attributes
    if ( ! empty( $data['attributes'] ) && is_array( $data['attributes'] ) ) {
        $attributes = array();
        foreach ( $data['attributes'] as $attr_data ) {
            if ( empty( $attr_data['name'] ) || empty( $attr_data['values'] ) ) continue;

            $attribute = new WC_Product_Attribute();
            $attribute->set_name( sanitize_text_field( $attr_data['name'] ) );
            $attribute->set_options( array_map( 'trim', explode( '|', $attr_data['values'] ) ) );
            $attribute->set_position( count( $attributes ) );
            $attribute->set_visible( true );
            $attribute->set_variation( false );
            $attributes[] = $attribute;
        }
        $product->set_attributes( $attributes );
    }

    // Handle Custom Taxonomies
    if ( ! empty( $data['custom_taxonomy'] ) && is_array( $data['custom_taxonomy'] ) ) {
        foreach ( $data['custom_taxonomy'] as $tax_name => $tax_value ) {
            if ( empty( $tax_name ) || empty( $tax_value ) ) continue;

            // Ensure taxonomy exists
            if ( taxonomy_exists( $tax_name ) ) {
                $terms = array();
                $values = explode( '|', $tax_value );
                foreach ( $values as $val ) {
                    $term = get_term_by( 'name', trim( $val ), $tax_name );
                    if ( ! $term ) {
                        $term = wp_insert_term( trim( $val ), $tax_name );
                    }
                    if ( ! is_wp_error( $term ) ) {
                        $terms[] = is_array( $term ) ? $term['term_id'] : $term->term_id;
                    }
                }
                if ( ! empty( $terms ) ) {
                    wp_set_object_terms( $product->get_id(), $terms, $tax_name );
                }
            }
        }
    }

    try {
        $product_id = $product->save();
        if ( ! $product_id ) {
            throw new Exception( __( 'Unknown error during product save', 'si-flash-products' ) );
        }
        return $product_id;
    } catch ( Exception $e ) {
        $error_msg = 'Failed to create product "' . $data['post_title'] . '": ' . $e->getMessage();
        FP_log_event( $error_msg, 'Product Creation' );
        return new WP_Error( 'save_error', $error_msg );
    }
}

/**
 * Sideload image from URL to Media Library
 */
function FP_sideload_image( $url, $title ) {
    // Se l'URL è già un ID (caso del media uploader di WP)
    if ( is_numeric( $url ) ) {
        return (int) $url;
    }

    // Se l'URL fa parte della nostra libreria media, proviamo a trovare l'ID
    $attachment_id = attachment_url_to_postid( $url );
    if ( $attachment_id ) {
        return $attachment_id;
    }

    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $url = esc_url_raw( $url );
    $filename = basename( $url );

    // 1. Check if we already sideloaded this exact URL
    global $wpdb;
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_fp_source_url' AND meta_value = %s",
        $url
    ) );

    if ( $attachment_id ) {
        return (int) $attachment_id;
    }

    // 2. Fallback: Check if image already exists in media library by filename
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ) );

    if ( $attachment_id ) {
        // Also tag it with the source URL for future lookups
        update_post_meta( $attachment_id, '_fp_source_url', $url );
        return (int) $attachment_id;
    }

    $desc = $title;
    $file_array = array();

    // Download file to temp location
    $tmp = download_url( $url );

    if ( is_wp_error( $tmp ) ) {
        FP_log_event( 'Failed to download image from ' . $url . ' - ' . $tmp->get_error_message(), 'Image Sideload' );
        return $tmp;
    }

    $file_array['name'] = $filename;
    $file_array['tmp_name'] = $tmp;

    // Do the job
    $id = media_handle_sideload( $file_array, 0, $desc );

    // If error, unlink
    if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
        FP_log_event( 'Failed to sideload image ' . $filename . ' - ' . $id->get_error_message(), 'Image Sideload' );
    } else {
        // Success! Store source URL for future reference
        update_post_meta( $id, '_fp_source_url', $url );
    }

    return $id;
}




