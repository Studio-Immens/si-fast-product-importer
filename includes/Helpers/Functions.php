<?php
/**
 * Global Helper Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get plugin setting
 */
if ( ! function_exists( 'sifp_get_setting' ) ) {
    function sifp_get_setting( $key, $default = '' ) {
        return get_option( $key, $default );
    }
}

/**
 * Update plugin setting
 */
if ( ! function_exists( 'sifp_update_setting' ) ) {
    function sifp_update_setting( $key, $value ) {
        return update_option( $key, $value );
    }
}

/**
 * Render general setting field
 */
if ( ! function_exists( 'sifp_general_setting' ) ) {
    function sifp_general_setting( $setting = array() ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $name    = isset( $setting['name'] ) ? sanitize_key( $setting['name'] ) : '';
        $title   = isset( $setting['title'] ) ? sanitize_text_field( $setting['title'] ) : '';
        $default = isset( $setting['default'] ) ? $setting['default'] : null;
        $value   = sifp_get_setting( $name, $default );
        $options = isset( $setting['options'] ) ? $setting['options'] : array();
        $type    = isset( $setting['type'] ) ? sanitize_key( $setting['type'] ) : 'text';
        $class   = isset( $setting['class'] ) ? sanitize_html_class( $setting['class'] ) : '';
        $text    = isset( $setting['text'] ) ? sanitize_text_field( $setting['text'] ) : '';
        $info    = isset( $setting['info'] ) ? sanitize_text_field( $setting['info'] ) : '';
        $other   = isset( $setting['other'] ) ? $setting['other'] : '';

        ?>
        <div class="sifp-setting-el <?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( $info ) . ' ( ' . esc_attr( $name ) . ' )'; ?>">
            <?php if ( $title ) : ?>
                <strong class="sifp-text-settings sifp-u-flex-100"><?php echo esc_html( $title ); ?></strong>
            <?php endif; ?>
            
            <p class="sifp-text-settings"><?php echo esc_html( $text ); ?></p>
            
            <?php if ( 'textarea' === $type ) : ?>
                <textarea name="setting[<?php echo esc_attr( $name ); ?>]" <?php echo wp_kses_post( $other ); ?>><?php echo esc_textarea( $value ); ?></textarea>
            <?php elseif ( 'select' === $type ) : ?>
                <select name="setting[<?php echo esc_attr( $name ); ?>]" <?php echo wp_kses_post( $other ); ?>>
                    <?php foreach ( $options as $option ) : ?>
                        <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>><?php echo esc_html( $option ); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <input type="<?php echo esc_attr( $type ); ?>" name="setting[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $value ); ?>" <?php echo wp_kses_post( $other ); ?>>
            <?php endif; ?>

            <?php if ( null !== $default ) : ?>
                <span class="dashicons dashicons-image-rotate pointer" data-default="<?php echo esc_attr( $default ); ?>"></span>
            <?php endif; ?>
        </div>
        <?php
    }
}

/**
 * Handle settings save from POST on admin_init
 */
if ( ! function_exists( 'sifp_handle_settings_save_init' ) ) {
    function sifp_handle_settings_save_init() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['update'] ) && isset( $_POST['setting'] ) ) {
            sifp_save_settings( 'setting' );
        }
    }
    add_action( 'admin_init', 'sifp_handle_settings_save_init' );
}

/**
 * Save settings from POST
 */
if ( ! function_exists( 'sifp_save_settings' ) ) {
    function sifp_save_settings( $args_name ) {
        if ( ! isset( $_POST['update'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! isset( $_POST['sett_nonce'] ) || ! wp_verify_nonce( $_POST['sett_nonce'], 'si-flash-prod-sett' ) ) {
            wp_die( esc_html__( 'Security check failed', 'si-flash-products' ) );
        }

        if ( isset( $_POST[ $args_name ] ) && is_array( $_POST[ $args_name ] ) ) {
            foreach ( $_POST[ $args_name ] as $key => $value ) {
                $sanitized_key = sanitize_key( $key );
                
                // Specific sanitization based on setting key
                switch ( $sanitized_key ) {
                    case 'sifp_openai_key':
                    case 'sifp_claude_key':
                    case 'sifp_openrouter_key':
                        $sanitized_value = \SIFlashProducts\Helpers\Encryption::encrypt( sanitize_text_field( $value ) );
                        break;
                    case 'sifp_remote_db_links':
                        $links = explode( "\n", str_replace( "\r", "", $value ) );
                        $sanitized_links = array();
                        foreach ( $links as $link ) {
                            $link = trim( $link );
                            if ( ! empty( $link ) ) {
                                $sanitized_links[] = esc_url_raw( $link );
                            }
                        }
                        $sanitized_value = implode( "\n", $sanitized_links );
                        break;
                    case 'sifp_default_stock':
                    case 'sifp_menu_order':
                        $sanitized_value = intval( $value );
                        break;
                    case 'sifp_ai_creativity':
                        $sanitized_value = floatval( $value );
                        break;
                    case 'sifp_active_ai_provider':
                    case 'sifp_default_product_status':
                    case 'sifp_default_import_status':
                        $sanitized_value = sanitize_key( $value );
                        break;
                    default:
                        $sanitized_value = sanitize_text_field( $value );
                        break;
                }
                
                update_option( $sanitized_key, $sanitized_value );
            }
            
            // Re-render the page with a success message
            $redirect_url = remove_query_arg( array( 'message', 'sifp_sync_db', 'sifp_regenerate_db', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
            wp_safe_redirect( add_query_arg( 'message', 'settings_saved', $redirect_url ) );
            exit;
        }
    }
}

/**
 * Generator for local DB - Generates 2000 common products
 */
if ( ! function_exists( 'sifp_ensure_local_db' ) ) {
    function sifp_ensure_local_db() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/si-flash-products';
        
        if ( ! file_exists( $plugin_upload_dir ) ) {
            wp_mkdir_p( $plugin_upload_dir );
        }

        $json_path = $plugin_upload_dir . '/local_products.json';
        
        // Generate only if explicit regeneration is requested via settings
        if ( isset( $_GET['sifp_regenerate_db'] ) ) {
            check_admin_referer( 'sifp_regenerate_db' );
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
        } else {
            // If it doesn't exist, we don't generate it automatically anymore to avoid performance issues
            return;
        }

        $lang_data = array(
            'it' => array(
                'categories' => array(
                    'Elettronica' => array('Smartphone', 'Laptop', 'Cuffie Bluetooth', 'Smartwatch', 'Tablet', 'Fotocamera', 'Monitor 4K', 'Tastiera Meccanica', 'Power Bank', 'Speaker Wireless'),
                    'Casa'        => array('Lampada LED', 'Sedia Ergonomica', 'Tavolo in Legno', 'Quadro Moderno', 'Vaso in Ceramica', 'Tappeto Soft', 'Specchio', 'Divano 3 Posti', 'Set Posate', 'Macchina Caffè'),
                    'Abbigliamento' => array('T-shirt Cotone', 'Jeans Slim Fit', 'Felpa con Cappuccio', 'Giacca Invernale', 'Scarpe Sportive', 'Cintura in Pelle', 'Cappello', 'Pantaloni Chino', 'Camicia Oxford'),
                    'Bellezza'    => array('Crema Idratante', 'Profumo Luxury', 'Shampoo Bio', 'Siero Viso', 'Maschera Argilla', 'Rossetto Matte', 'Smalto', 'Crema Solare', 'Balsamo'),
                    'Sport'       => array('Tappetino Yoga', 'Manubri 5kg', 'Palla da Basket', 'Corda per Saltare', 'Borraccia Termica', 'Zaino Trekking', 'Pesi Caviglie', 'Rullo Massaggi', 'Banda Elastica'),
                ),
                'adjectives'  => array('Rivoluzionario', 'Professionale', 'Eco-sostenibile', 'Intelligente', 'Classico', 'Moderno', 'Ultra-resistente', 'Essenziale', 'Edizione Limitata', 'Compatto', 'Superiore', 'Elite', 'Definitivo'),
                'ingredients' => array('Acqua Termale', 'Estratto di Aloe', 'Olio di Argan', 'Acido Ialuronico', 'Vitamina C', 'Burro di Shea', 'Proteine della Seta'),
                'allergens'   => array('Glutine', 'Lattosio', 'Frutta a guscio', 'Soia', 'Nichel Free'),
                'desc_title'  => "Vuoi trasformare il tuo modo di vivere %s?",
                'desc_intro'  => "Smetti di accontentarti di soluzioni mediocri. Il nuovo <strong>%s</strong> non è solo un prodotto, è la risposta definitiva che stavi cercando.",
                'desc_why'    => "Perché scegliere %s?",
                'benefit_1'   => 'Prestazioni Ineguagliabili',
                'benefit_1_txt' => "Progettato con tecnologia %s per superare ogni aspettativa.",
                'benefit_2'   => 'Qualità Certificata',
                'benefit_2_txt' => "Ogni componente è stato testato per garantire una durata nel tempo senza precedenti.",
                'benefit_3'   => 'Design Esclusivo',
                'benefit_3_txt' => "Un'estetica che si adatta perfettamente al tuo stile di vita moderno.",
                'excerpt'     => "Il segreto per ottenere il massimo da %s. Scopri perché %s è la scelta numero uno degli esperti.",
                'tags'        => array('offerta', 'esclusivo'),
            ),
            'en' => array(
                'categories' => array(
                    'Electronics' => array('Smartphone', 'Laptop', 'Bluetooth Headphones', 'Smartwatch', 'Tablet', 'Camera', '4K Monitor', 'Mechanical Keyboard', 'Power Bank', 'Wireless Speaker'),
                    'Home'        => array('LED Lamp', 'Ergonomic Chair', 'Wooden Table', 'Modern Painting', 'Ceramic Vase', 'Soft Rug', 'Mirror', '3-Seater Sofa', 'Cutlery Set', 'Coffee Machine'),
                    'Clothing'    => array('Cotton T-shirt', 'Slim Fit Jeans', 'Hoodie', 'Winter Jacket', 'Sports Shoes', 'Leather Belt', 'Hat', 'Chino Pants', 'Oxford Shirt'),
                    'Beauty'      => array('Moisturizing Cream', 'Luxury Perfume', 'Organic Shampoo', 'Face Serum', 'Clay Mask', 'Matte Lipstick', 'Nail Polish', 'Sunscreen', 'Hair Conditioner'),
                    'Sports'      => array('Yoga Mat', 'Dumbbells 5kg', 'Basketball', 'Jump Rope', 'Thermal Bottle', 'Trekking Backpack', 'Ankle Weights', 'Massage Roller', 'Resistance Band'),
                ),
                'adjectives'  => array('Revolutionary', 'Professional', 'Eco-friendly', 'Smart', 'Classic', 'Modern', 'Ultra-durable', 'Essential', 'Limited Edition', 'Compact', 'Superior', 'Elite', 'Ultimate'),
                'ingredients' => array('Thermal Water', 'Aloe Extract', 'Argan Oil', 'Hyaluronic Acid', 'Vitamin C', 'Shea Butter', 'Silk Proteins'),
                'allergens'   => array('Gluten', 'Lactose', 'Tree Nuts', 'Soy', 'Nickel Free'),
                'desc_title'  => "Want to transform how you experience %s?",
                'desc_intro'  => "Stop settling for mediocre solutions. The new <strong>%s</strong> is not just a product, it is the ultimate answer you were looking for.",
                'desc_why'    => "Why choose %s?",
                'benefit_1'   => 'Unmatched Performance',
                'benefit_1_txt' => "Designed with %s technology to exceed every expectation.",
                'benefit_2'   => 'Certified Quality',
                'benefit_2_txt' => "Every component has been tested to ensure unprecedented durability.",
                'benefit_3'   => 'Exclusive Design',
                'benefit_3_txt' => "An aesthetic that fits perfectly with your modern lifestyle.",
                'excerpt'     => "The secret to getting the most out of %s. Find out why %s is the number one choice of experts.",
                'tags'        => array('sale', 'exclusive'),
            ),
        );

        // Map English category names to the image pools (which use Italian keys)
        $cat_image_map = array(
            'Electronics' => 'Elettronica',
            'Home'        => 'Casa',
            'Clothing'    => 'Abbigliamento',
            'Beauty'      => 'Bellezza',
            'Sports'      => 'Sport',
        );

        $img_base    = 'https://images.unsplash.com/';
        $img_params  = '?auto=format&fit=crop&w=800&q=80';

        $category_images = array(
            'Elettronica' => array(
                'main' => array(
                    'photo-1468495244123-6c6c332eeece',
                    'photo-1505740420928-5e560c06d30e',
                    'photo-1523275335684-37898b6baf30',
                    'photo-1507003211169-0a1dd7228f2d',
                    'photo-1511707171634-5f897ff02aa9',
                ),
                'gallery' => array(
                    'photo-1544244015-0df4b3ffc6b0',
                    'photo-1561948955-570b270e7c36',
                    'photo-1531297484001-80022131f5a1',
                ),
            ),
            'Casa' => array(
                'main' => array(
                    'photo-1555041469-a586c61ea9bc',
                    'photo-1493663284031-b7e3aefcae8e',
                    'photo-1507003211169-0a1dd7228f2d',
                    'photo-1540574163026-643ea20ade25',
                    'photo-1533090161767-e6ffed986c88',
                ),
                'gallery' => array(
                    'photo-1487700160040-b2e5f2b9c0a0',
                    'photo-1524758631624-e2822e304c36',
                    'photo-1560448204-e02f11c3d0e2',
                ),
            ),
            'Abbigliamento' => array(
                'main' => array(
                    'photo-1491553895911-0055eca6402d',
                    'photo-1523381210434-271e8be1f52b',
                    'photo-1542291026-7eec264c27ff',
                    'photo-1551028719-00167b16eac5',
                    'photo-1512436991641-6745b0cfb1b1',
                ),
                'gallery' => array(
                    'photo-1556905055-8f358a7a47b2',
                    'photo-1549298916-b41d501d3772',
                    'photo-1517404215738-1526349b4db0',
                ),
            ),
            'Bellezza' => array(
                'main' => array(
                    'photo-1596462502278-27bfdc403348',
                    'photo-1522335789203-aabd1fc54bc8',
                    'photo-1570172619644-dfd03ed5d881',
                    'photo-1567721913486-6585f069b332',
                ),
                'gallery' => array(
                    'photo-1556228578-0d85b1a4d571',
                    'photo-1596755389378-c31d21fd1273',
                    'photo-1608248543803-ba4f8c70ae0b',
                ),
            ),
            'Sport' => array(
                'main' => array(
                    'photo-1571019613454-1cb2f99b2d8b',
                    'photo-1517836357463-d25dfeac3438',
                    'photo-1530541930197-ff16ac917b0e',
                    'photo-1518611012118-696072aa579a',
                    'photo-1556817411-31ae72fa3ea0',
                ),
                'gallery' => array(
                    'photo-1534438327276-14e5300c3a48',
                    'photo-1562183241-b937e95585b6',
                    'photo-1571902943202-507ec2618e8f',
                ),
            ),
        );

        $products = array();
        $total    = 1000; // per language

        foreach ( $lang_data as $lang_code => $lang ) {
            $cat_keys  = array_keys( $lang['categories'] );
            $adj_count = count( $lang['adjectives'] );
            $ing_count = count( $lang['ingredients'] );
            $all_count = count( $lang['allergens'] );

            for ( $i = 1; $i <= $total; $i++ ) {
                $category  = $cat_keys[ array_rand( $cat_keys ) ];
                $base_name = $lang['categories'][ $category ][ array_rand( $lang['categories'][ $category ] ) ];
                $adj       = $lang['adjectives'][ array_rand( $lang['adjectives'] ) ];
                $title     = "$base_name $adj";

                $price      = rand( 15, 800 ) + ( rand( 0, 99 ) / 100 );
                $sale_price = ( rand( 1, 10 ) > 7 ) ? ( $price * 0.85 ) : '';

                // Image pool lookup
                $image_cat  = isset( $cat_image_map[ $category ] ) ? $cat_image_map[ $category ] : $category;
                $cat_imgs   = $category_images[ $image_cat ] ?? $category_images['Elettronica'];
                $main_img   = $img_base . $cat_imgs['main'][ $i % count( $cat_imgs['main'] ) ] . $img_params;
                $gallery1   = $img_base . $cat_imgs['gallery'][ $i % count( $cat_imgs['gallery'] ) ] . $img_params;
                $gallery2   = $img_base . $cat_imgs['gallery'][ ( $i + 1 ) % count( $cat_imgs['gallery'] ) ] . $img_params;

                $desc  = "<h3>{$lang['desc_title']}</h3>";
                $desc .= "<p>{$lang['desc_intro']}</p>";
                $desc .= "<h4>{$lang['desc_why']}</h4>";
                $desc .= "<ul>";
                $desc .= "<li><strong>{$lang['benefit_1']}:</strong> {$lang['benefit_1_txt']}</li>";
                $desc .= "<li><strong>{$lang['benefit_2']}:</strong> {$lang['benefit_2_txt']}</li>";
                $desc .= "<li><strong>{$lang['benefit_3']}:</strong> {$lang['benefit_3_txt']}</li>";
                $desc .= "</ul>";

                $excerpt = sprintf( $lang['excerpt'], $category, $title );

                $ing = '';
                if ( ( 'Bellezza' === $category || 'Beauty' === $category ) || rand( 1, 10 ) > 8 ) {
                    $ing = $lang['ingredients'][ array_rand( $lang['ingredients'] ) ] . ', ' . $lang['ingredients'][ array_rand( $lang['ingredients'] ) ];
                }
                $all = '';
                if ( ( 'Bellezza' === $category || 'Beauty' === $category ) && rand( 1, 10 ) > 7 ) {
                    $all = $lang['allergens'][ array_rand( $lang['allergens'] ) ];
                }
                $stick = ( rand( 1, 10 ) > 8 ) ? 'NEW' : ( ( rand( 1, 10 ) > 8 ) ? 'BEST SELLER' : '' );

                $products[] = array(
                    'post_title'      => $title,
                    'post_content'    => $desc,
                    'post_excerpt'    => $excerpt,
                    'sifp_categories' => $category,
                    'sifp_tag'        => strtolower( $category ) . ', ' . $adj . ', ' . implode( ', ', $lang['tags'] ),
                    'sifp_img'        => $main_img,
                    'sifp_gallery'    => "$gallery1,$gallery2",
                    'regular_price'   => number_format( $price, 2, '.', '' ),
                    'sale_price'      => $sale_price ? number_format( $sale_price, 2, '.', '' ) : '',
                    'sku'             => 'SIFP-' . strtoupper( substr( $image_cat, 0, 3 ) ) . '-' . str_pad( $i, 4, '0', STR_PAD_LEFT ),
                    'stock_status'    => 'instock',
                    'stock_qty'       => rand( 5, 150 ),
                    'weight'          => ( rand( 5, 100 ) / 10 ),
                    'length'          => rand( 5, 80 ),
                    'width'           => rand( 5, 80 ),
                    'height'          => rand( 2, 40 ),
                    'is_virtual'      => 'no',
                    'is_downloadable' => 'no',
                    'sifp_ingredient' => $ing,
                    'sifp_allerg'     => $all,
                    'sifp_sticker'    => $stick,
                    'sifp_temp'       => ( rand( 1, 10 ) > 9 ) ? ( 'it' === $lang_code ? 'Conservare in luogo fresco' : 'Store in a cool place' ) : '',
                    'attributes'      => array(
                        array( 'name' => 'Colore', 'value' => 'Nero | Bianco | Grigio | Blu', 'visible' => 1, 'variation' => 0 ),
                        array( 'name' => 'Materiale', 'value' => 'Premium | Eco-friendly', 'visible' => 1, 'variation' => 0 ),
                    ),
                    'custom_taxonomy' => array(
                        'brand'     => 'FlashBrand',
                        'materiale' => 'Materiale ' . $adj,
                    ),
                );
            }
        }

        file_put_contents( $json_path, wp_json_encode( $products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
        
        // Sync to database table
        $db = \SIFlashProducts\Core\Database::instance();
        $db->sync_json_to_db( $json_path );
        
        if ( isset( $_GET['sifp_regenerate_db'] ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=flash_products_settings&tab=database&message=db_regenerated' ) );
            exit;
        }
    }
}

/**
 * Handle sync DB manually
 */
if ( ! function_exists( 'sifp_handle_sync_db' ) ) {
    function sifp_handle_sync_db() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['sifp_sync_db'] ) && check_admin_referer( 'sifp_sync_db' ) ) {
            $upload_dir = wp_upload_dir();
            $json_path  = $upload_dir['basedir'] . '/si-flash-products/local_products.json';
            
            if ( file_exists( $json_path ) ) {
                $db = \SIFlashProducts\Core\Database::instance();
                $db->sync_json_to_db( $json_path );
                wp_safe_redirect( admin_url( 'admin.php?page=flash_products_settings&tab=database&message=db_synced' ) );
                exit;
            } else {
                wp_safe_redirect( admin_url( 'admin.php?page=flash_products_settings&tab=database&message=db_file_not_found' ) );
                exit;
            }
        }
    }
    add_action( 'admin_init', 'sifp_handle_sync_db' );
}

if ( ! function_exists( 'sifp_ensure_local_db_hook' ) ) {
    function sifp_ensure_local_db_hook() {
        sifp_ensure_local_db();
    }
    add_action('admin_init', 'sifp_ensure_local_db_hook');
}

/**
 * Log message for debugging and UI display
 */
if ( ! function_exists( 'sifp_log' ) ) {
    function sifp_log( $message, $context = 'general', $level = 'info' ) {
        $timestamp = date( 'Y-m-d H:i:s' );
        $log_entry = array(
            'timestamp' => $timestamp,
            'context'   => $context,
            'level'     => $level,
            'message'   => ( is_array( $message ) || is_object( $message ) ? json_encode( $message ) : $message )
        );

        // Save to option for UI display (keep last 50)
        $logs = get_option( 'sifp_error_logs', array() );
        array_unshift( $logs, $log_entry );
        $logs = array_slice( $logs, 0, 50 );
        update_option( 'sifp_error_logs', $logs );

        // Fallback to file logging if WP_DEBUG is on (use content dir, not plugin dir)
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $log_dir = WP_CONTENT_DIR . '/uploads/si-flash-products';
            if ( ! file_exists( $log_dir ) ) {
                wp_mkdir_p( $log_dir );
            }
            $log_file = $log_dir . '/debug.log';
            $formatted_message = "[{$timestamp}] [{$level}] [{$context}] " . $log_entry['message'] . PHP_EOL;
            error_log( $formatted_message, 3, $log_file );
        }
    }
}
