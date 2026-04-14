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
                <strong class="sifp-text-settings" style="flex-basis:100%"><?php echo esc_html( $title ); ?></strong>
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
                    case 'sifp_gemini_api_key':
                    case 'sifp_ai_tone':
                    case 'sifp_sku_prefix':
                        $sanitized_value = sanitize_text_field( $value );
                        break;
                    case 'sifp_remote_db_links':
                        // Sanitize each URL in the textarea
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
                    case 'sifp_ai_model':
                        $sanitized_value = sanitize_text_field( $value );
                        break;
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
            $redirect_url = remove_query_arg( array( 'message', 'sifp_sync_db', 'sifp_regenerate_db', '_wpnonce' ), $_SERVER['REQUEST_URI'] );
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
            
            $kw = $img_keywords[$category] ?? 'product';
            $main_img = "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80&sig=$i";
            $gallery1 = "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80&sig=" . ($i + 2000);
            $gallery2 = "https://images.unsplash.com/photo-1526170315830-ef18a283ac13?auto=format&fit=crop&w=800&q=80&sig=" . ($i + 4000);

            $desc = "<h3>" . sprintf( esc_html__( 'Want to transform how you experience %s?', 'si-flash-products' ), esc_html( $category ) ) . "</h3>";
            $desc .= "<p>" . sprintf( esc_html__( 'Stop settling for mediocre solutions. The new %s is not just a product, it is the ultimate answer you were looking for.', 'si-flash-products' ), "<strong>" . esc_html( $title ) . "</strong>" ) . "</p>";
            $desc .= "<h4>" . sprintf( esc_html__( 'Why choose %s?', 'si-flash-products' ), esc_html( $title ) ) . "</h4>";
            $desc .= "<ul>";
            $desc .= "<li><strong>" . esc_html__( 'Unmatched Performance:', 'si-flash-products' ) . "</strong> " . sprintf( esc_html__( 'Designed with %s technology to exceed every expectation.', 'si-flash-products' ), esc_html( $adj ) ) . "</li>";
            $desc .= "<li><strong>" . esc_html__( 'Certified Quality:', 'si-flash-products' ) . "</strong> " . esc_html__( 'Every component has been tested to ensure unprecedented durability.', 'si-flash-products' ) . "</li>";
            $desc .= "<li><strong>" . esc_html__( 'Exclusive Design:', 'si-flash-products' ) . "</strong> " . esc_html__( 'An aesthetic that fits perfectly with your modern lifestyle.', 'si-flash-products' ) . "</li>";
            $desc .= "</ul>";

            $ing = ( $category === 'Bellezza' || rand( 1, 10 ) > 8 ) ? $ingredients_list[ array_rand( $ingredients_list ) ] . ", " . $ingredients_list[ array_rand( $ingredients_list ) ] : '';
            $all = ( $category === 'Bellezza' && rand( 1, 10 ) > 7 ) ? $allergens_list[ array_rand( $allergens_list ) ] : '';
            $stick = ( rand( 1, 10 ) > 8 ) ? "NOVITÀ" : ( ( rand( 1, 10 ) > 8 ) ? "BEST SELLER" : '' );

            $products[] = [
                'post_title'      => $title,
                'post_content'    => $desc,
                'post_excerpt'    => sprintf( esc_html__( 'The secret to getting the most out of %s. Find out why %s is the number one choice of experts.', 'si-flash-products' ), esc_html( $category ), esc_html( $title ) ),
                'sifp_categories' => $category,
                'sifp_tag'        => strtolower( $category ) . ", $adj, offerta, esclusivo",
                'sifp_img'        => $main_img,
                'sifp_gallery'    => "$gallery1,$gallery2",
                'regular_price'   => number_format( $price, 2, '.', '' ),
                'sale_price'      => $sale_price ? number_format( $sale_price, 2, '.', '' ) : '',
                'sku'             => "SIFP-" . strtoupper( substr( $category, 0, 3 ) ) . "-" . str_pad( $i, 4, '0', STR_PAD_LEFT ),
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
                'sifp_temp'       => ( rand( 1, 10 ) > 9 ) ? "Conservare in luogo fresco" : '',
                'attributes'      => [
                    [ 'name' => 'Colore', 'value' => 'Nero | Bianco | Grigio | Blu', 'visible' => 1, 'variation' => 0 ],
                    [ 'name' => 'Materiale', 'value' => 'Premium | Eco-friendly', 'visible' => 1, 'variation' => 0 ]
                ],
                'custom_taxonomy' => [
                    'brand'     => 'FlashBrand',
                    'materiale' => 'Materiale ' . $adj
                ]
            ];
        }

        file_put_contents( $json_path, wp_json_encode( $products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
        
        // Sync to database table
        $db = \SIFlashProducts\Core\Database::instance();
        $db->sync_json_to_db( $json_path );
        
        if ( isset( $_GET['sifp_regenerate_db'] ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=flash_products_settings&message=db_regenerated' ) );
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
                wp_safe_redirect( admin_url( 'admin.php?page=flash_products_settings&message=db_synced' ) );
                exit;
            } else {
                wp_safe_redirect( admin_url( 'admin.php?page=flash_products_settings&message=db_file_not_found' ) );
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

        // Fallback to file logging if WP_DEBUG is on
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $log_file = SIFProd_PATH . 'debug.log';
            $formatted_message = "[{$timestamp}] [{$level}] [{$context}] " . $log_entry['message'] . PHP_EOL;
            error_log( $formatted_message, 3, $log_file );
        }
    }
}
