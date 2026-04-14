<?php
/**
 * Global Helper Functions (Legacy)
 * 
 * Note: Most logic has been moved to classes in the includes/ directory.
 * This file is kept for backward compatibility and as per user request.
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
                        $sanitized_value = sanitize_textarea_field( $value );
                        break;
                    case 'sifp_default_stock':
                    case 'sifp_menu_order':
                        $sanitized_value = intval( $value );
                        break;
                    case 'sifp_ai_creativity':
                        $sanitized_value = floatval( $value );
                        break;
                    case 'sifp_ai_model':
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
 * Generator for local DB
 */
if ( ! function_exists( 'sifp_ensure_local_db' ) ) {
    function sifp_ensure_local_db() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/si-flash-products';
        
        if ( ! file_exists( $plugin_upload_dir ) ) {
            wp_mkdir_p( $plugin_upload_dir );
        }

        $json_path = $plugin_upload_dir . '/local_products.json';
        
        if ( isset( $_GET['sifp_regenerate_db'] ) ) {
            check_admin_referer( 'sifp_regenerate_db' );
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
        } else {
            return;
        }

        // Logic here should match includes/Helpers/Functions.php or includes/generate_local_db.php
        if ( file_exists( SIFProd_PATH . 'includes/generate_local_db.php' ) ) {
            include_once SIFProd_PATH . 'includes/generate_local_db.php';
        }
    }
}

/**
 * Log message for debugging (Wrapper for sifp_log)
 */
if ( ! function_exists( 'sifp_log_event' ) ) {
    function sifp_log_event( $message, $context = 'general' ) {
        if ( function_exists( 'sifp_log' ) ) {
            sifp_log( $message, $context );
        }
    }
}

/**
 * Create WooCommerce product from remote data (Wrapper for Importer class)
 */
if ( ! function_exists( 'sifp_create_woo_product' ) ) {
    function sifp_create_woo_product( $data ) {
        $importer = new \SIFlashProducts\Core\Importer();
        return $importer->create_woo_product( $data );
    }
}
